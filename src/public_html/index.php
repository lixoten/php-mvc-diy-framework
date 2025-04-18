<?php

declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Los_Angeles');

/**
 * Application entry point
 */

// Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Helpers\DebugRt as Debug;
use Core\ErrorHandler;
use Core\Router;
use Core\FrontController;
use Core\Services\ConfigService;
use Psr\Container\ContainerInterface;

// Define base URL constant for views
// Fucking hack by copilot // TODO
// define('BASE_PATH', dirname(__DIR__)); // Points to project root
// define('SRC_PATH', BASE_PATH . '/src');
// define('CONFIG_PATH', SRC_PATH . '/Config');
// define('LOG_PATH', BASE_PATH . '/logs');
// define('PUBLIC_PATH', SRC_PATH . '/public_html');

if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
}

// Load .env file if it exists
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();
}


// Validate required environment variables
$dotenv->required([
    'APP_ENV',
    'MAIL_API_DEFAULT',
    'SMTP_USERNAME',
    'SMTP_PASSWORD',
    'MAILGUN_API_KEY',
    'MAILGUN_DOMAIN'
])->notEmpty(); // TODO add all $ENV valiables to here

// Validate specific values
$dotenv->required('MAIL_API_DEFAULT')->allowedValues(['smtp', 'mailgun']);



// Initialize error handling
$environment = $_SERVER['APP_ENV'] ?? 'development';

///////////////////////////////////////////////////////
// Create PHP-DI container
$containerBuilder = new \DI\ContainerBuilder();
$containerBuilder->useAutowiring(true);

if ($environment === 'production') {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}



$definitions = require __DIR__ . '/../dependencies.php';

$containerBuilder->addDefinitions($definitions);
$container = $containerBuilder->build();

// Get ErrorHandler from container
$errorHandler = $container->get('errorHandler');


// Define a common exception handler function
// $handleException = function ($exception) use ($errorHandler, $container, $environment) {
//     // For ParseError in development, show PHP's error
//     if ($environment === 'development' && $exception instanceof \ParseError) {
//         throw $exception;
//     }

//     // For all other exceptions
//     $response = $errorHandler->handleException($exception);
//     $container->get('responseEmitter')->emit($response);
// };

// Simple fallback for critical exceptions outside the HTTP flow
set_exception_handler(function ($exception) use ($container) {
    error_log('CRITICAL: ' . $exception->getMessage());

    if (!headers_sent()) {
        http_response_code(500);
        echo "Server Error";
        //Debug::p($exception);
    }

    exit(1);
});


// Different error display settings based on environment
if ($environment === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    ini_set('display_errors', 0);
    // Convert PHP errors to exceptions in production
    set_error_handler(function ($level, $message, $file, $line) {
        throw new \ErrorException($message, 0, $level, $file, $line);
    });
}

// Set common exception handler
//set_exception_handler($handleException);

// Force logger initialization to see debug output
$container->get('logger');

/** @var \Core\FrontController $frontController */
$frontController = $container->get('frontController');


// PSR-7 approach
$httpFactory = $container->get('httpFactory');

// Create and process the request
$request = $httpFactory->createServerRequestFromGlobals();

// Get middleware pipeline
$pipeline = $container->get(Core\Middleware\MiddlewarePipeline::class);

// Process request through middleware pipeline (instead of directly through frontController)
$response = $pipeline->handle($request);

// Output the response to the browser
$container->get('responseEmitter')->emit($response);

# 244 233 108
