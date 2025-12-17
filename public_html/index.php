<?php

// xdebug_start_trace('d:\xampp\tmp\traces\manual_trace');

declare(strict_types=1);

$uri = $_SERVER['REQUEST_URI'] ?? '';
if (preg_match('#\.(css|js|png|jpg|jpeg|gif|svg|woff2?|ttf|eot|ico)$#i', $uri)) {
    // Optionally serve the file or just exit
    http_response_code(404);
    exit;
}
// dangerdanger  # .htaccess or httpd.conf
// RewriteEngine On
// RewriteCond %{REQUEST_FILENAME} -f [OR]
// RewriteCond %{REQUEST_FILENAME} -d
// RewriteRule ^ - [L]
// RewriteRule ^ index.php [QSA,L]
// dangerdanger  Example: Laravel's .htaccess
// <IfModule mod_rewrite.c>
//     RewriteEngine On
//     RewriteCond %{REQUEST_FILENAME} !-f
//     RewriteCond %{REQUEST_FILENAME} !-d
//     RewriteRule ^ index.php [L]
// </IfModule>
// dangerdanger

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Los_Angeles');

/**
 * Application entry point
 */

// Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';


// // ✅ NEW: TEMPORARY AUTOLOADING DEBUGGING BLOCK
// // Check if the CodeLookupServiceInterface can be loaded directly.
// $interfaceFqcn = 'Core\\Interfaces\\CodeLookupServiceInterface';
// if (class_exists($interfaceFqcn)) {
//     error_log("DEBUG: {$interfaceFqcn} - EXISTS via autoloader.");
// } else {
//     // Attempt to manually require it if autoloader fails, to see if file path is the issue.
//     $manualPath = __DIR__ . '/../src/Core/Interfaces/CodeLookupServiceInterface.php';
//     if (file_exists($manualPath)) {
//         require_once $manualPath;
//         if (class_exists($interfaceFqcn)) {
//             error_log("DEBUG: {$interfaceFqcn} - EXISTS after manual require. Autoloader issue suspected.");
//         } else {
//             error_log("DEBUG: {$interfaceFqcn} - File found, but class_exists still FALSE after manual require. Namespace mismatch or fatal error in file?");
//         }
//     } else {
//         error_log("DEBUG: {$interfaceFqcn} - File does NOT exist at expected path: {$manualPath}");
//     }
// }
// // ❌ END TEMPORARY AUTOLOADING DEBUGGING BLOCK



use App\Helpers\DebugRt;
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

// // Load .env file if it exists
// if (file_exists(__DIR__ . '/../../.env')) {
//     $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
//     $dotenv->load();
// }


// ✅ FIX: Implement robust .env loading with proper error handling.
// This will clearly tell you if the file is missing, or if required variables are absent.
try {
    // Use the correct path (project root from public_html/index.php)
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    // Validate required environment variables.
    // ✅ Suggestion: Expand this list with all *critical* environment variables
    // that your application absolutely needs to start (e.g., DB credentials, image paths).
    $dotenv->required([
        'APP_NAME', // Added from your .env file
        'APP_ENV',
        'APP_DEBUG', // Added from your .env file
        'APP_URL', // Added from your .env file
        'APP_KEY', // Added from your .env file
        'APP_TIMEZONE', // Added from your .env file
        'MAIL_API_DEFAULT',
        'SMTP_USERNAME',
        'SMTP_PASSWORD',
        'MAILGUN_API_KEY',
        'MAILGUN_DOMAIN',
        // Example additions (you need to fill these in based on your actual needs):
        // 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
        // 'IMAGE_PUBLIC_ROOT', 'IMAGE_STORAGE_ROOT', 'IMAGE_BASE_URL',
        // 'RECAPTCHA_SITE_KEY', 'RECAPTCHA_SECRET_KEY',
        // 'STORAGE_PROVIDER', 'STORAGE_BASE_PATH', 'STORAGE_BASE_URL',
    ])->notEmpty();

    // Validate specific values
    $dotenv->required('MAIL_API_DEFAULT')->allowedValues(['smtp', 'mailgun']);

} catch (\Dotenv\Exception\InvalidPathException $e) {
    // This catches if the .env file itself cannot be found at the specified path.
    error_log('FATAL: Environment file (.env) not found or path is invalid: ' . $e->getMessage());
    http_response_code(500);
    die('Application startup error: Environment configuration file is missing. Please ensure .env exists in the project root.');
} catch (\Dotenv\Exception\ValidationException $e) {
    // This catches if any of the $dotenv->required() variables are missing or empty.
    error_log('FATAL: Missing or invalid required environment variables: ' . $e->getMessage());
    http_response_code(500);
    die('Application startup error: Critical environment variables are not set. Please check your .env file and ensure all required variables are present and not empty. Details: ' . $e->getMessage());
} catch (\Throwable $e) {
    // Catch any other unexpected errors during the .env loading process.
    error_log('FATAL: An unexpected error occurred during environment variable loading: ' . $e->getMessage());
    http_response_code(500);
    die('Application startup error: An unexpected error occurred during configuration loading. Please check server logs for more details.');
}







// Validate required environment variables
$dotenv->required([
    'APP_ENV',
    'MAIL_API_DEFAULT',
    'SMTP_USERNAME',
    'SMTP_PASSWORD',
    'MAILGUN_API_KEY',
    'MAILGUN_DOMAIN'
])->notEmpty(); // TODO add all $ENV variables to here

// Validate specific values
$dotenv->required('MAIL_API_DEFAULT')->allowedValues(['smtp', 'mailgun']);



// Initialize error handling
$environment = $_SERVER['APP_ENV'] ?? 'development';







/////////////////////////////////////////////////////////////////
// // Create PHP-DI container
// $containerBuilder = new \DI\ContainerBuilder();
// $containerBuilder->useAutowiring(true);

// if ($environment === 'production`') {
//     $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
// }

// $definitions = require __DIR__ . '/../src/dependencies.php';

// $containerBuilder->addDefinitions($definitions);
// $container = $containerBuilder->build();

$app = new \Core\Application(dirname(__DIR__));
$container = $app->bootstrap();
///////////////////////////////////////////////////////////////









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
        echo "Server Error...";
        // DebugRt::p($exception);
        $exceptionData = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            // The 'trace' is intentionally omitted here for security/verbosity.
        ];
        DebugRt::j('0', 'exception', $exceptionData);
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

$container->get('urlServiceInitializer');

/** @var \Core\FrontController $frontController */
$frontController = $container->get('frontController');
// DebugRt::j('0', '$frontController', $frontController);

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

// dangerdanger
$rrr = 4;
$sc = $_SERVER['SCRIPT_NAME'];
if ($_SERVER['SCRIPT_NAME'] === '/index.php') {
    file_put_contents('exit.log', 'EXIT index  HIT: ' . date('c') . ' ' . $sc . ' '. ($_SERVER['REQUEST_URI'] ?? '') . PHP_EOL, FILE_APPEND);
}
file_put_contents('exit.log', 'EXIT HIT: ' . date('c') . ' ' . $sc . ' '. ($_SERVER['REQUEST_URI'] ?? '') . PHP_EOL, FILE_APPEND);
exit();
# 244 233 108
