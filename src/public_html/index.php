<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Initialize error handling
$environment = $_SERVER['APP_ENV'] ?? 'development';
// Set up configuration with environment awareness
//require_once __DIR__ . '/../../src/Core/Services/ConfigService.php';
//Config::init(__DIR__ . '/src', $environment);

// $errorHandler = new ErrorHandler(
//     $environment === 'development',  // developmentMode
//     null,                           // logger (uses default).
//     null                            // temporarily null container
// );


///////////////////////////////////////////////////////
// Create PHP-DI container
$containerBuilder = new \DI\ContainerBuilder();
if ($environment === 'production') {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}





// Define services
$definitions = [
    'environment' => $environment,

    'config' => function (\Psr\Container\ContainerInterface $c) use ($environment) {
        // Create Config service with base path and environment
        return new ConfigService(
            __DIR__ . '/../../src',  // Path to src directory
            $environment             // Current environment
        );
    },


    // Add this to your $definitions array
    'route_params' => \DI\factory(function () {
        return [];
    }),

    'logger' => function (\Psr\Container\ContainerInterface $c) {
        // Get environment and configs
        $config = $c->get('config')->get('logger');

        // Create logger with appropriate settings
        $logger = new \Core\Logger(
            $config['directory'] ?? __DIR__ . '/../../logs',
            $config['min_level'] ?? \Core\Logger::INFO,
            $config['debug_mode'] ?? false,
            $config['sampling_rate'] ?? 0.1
        );


        if ($config['rotation'] ?? true) {
            $logger->cleanupOldLogs($config['retention_days'] ?? 30);
        }

        return $logger;
    },



    'errorHandler' => \DI\autowire(ErrorHandler::class)
        ->constructorParameter('developmentMode', $environment === 'development')
        ->constructorParameter('logger', \DI\get('logger'))
        ->constructorParameter('container', \DI\get(\Psr\Container\ContainerInterface::class)),

    'router' => \DI\autowire(Router::class)
        ->constructorParameter('container', \DI\get(\Psr\Container\ContainerInterface::class)),
    'frontController' => \DI\autowire(FrontController::class)
        ->constructorParameter('router', \DI\get('router')),


    'sessionManager' => function () use ($environment) {
        return new \Core\Session\SessionManager([
            'name' => 'mvc3_session',
            'secure' => $environment === 'production',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    },



    'view' => \DI\autowire(Core\View::class),
    'controller' => \DI\factory(function ($c, $route_params, $controller_class) {
        if (class_exists($controller_class)) {
            return new $controller_class(
                $route_params,
                $c->get('flashMessageService'),
                $c->get('view')
            );
        }

        throw new \Exception("Controller class $controller_class not found");
    }),



    'flash' => \DI\autowire(\App\Services\FlashMessageService::class)
        ->constructorParameter('sessionManager', \DI\get('sessionManager')),
    'flashMessageService' => \DI\get('flash'),

    'App\Features\Errors\ErrorsController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
         ->constructorParameter('flash', \DI\get('flash')),
    'App\Features\Home\HomeController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
         ->constructorParameter('flash', \DI\get('flash')),
    'App\Features\About\AboutController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash')),
    'App\Features\Testy\TestyController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('config', \DI\get('config')),//feey
    'App\Features\Admin\Dashboard\DashboardController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash')),
    // More services...
];

$containerBuilder->addDefinitions($definitions);
$container = $containerBuilder->build();

// Get ErrorHandler from container
$errorHandler = $container->get('errorHandler');


// Different error handling based on environment
if ($environment === 'development') {
    // DEVELOPMENT: Show raw PHP errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Don't convert warnings to exceptions in development
    // This makes undefined variables show as warnings, not trigger error pages

    // Only handle uncaught exceptions with the error handler
    set_exception_handler(function ($exception) use ($errorHandler) {
        // Check if the exception is a ParseError
            //throw $exception;
        //Debug::p($exception);
        if ($exception instanceof \ParseError) {
            // Let PHP display the ParseError directly
            throw $exception;
        } else {
            // Handle other exceptions with the error handler
            $errorHandler->handleException($exception);
        }
    });
} else {
    // PRODUCTION: Use custom error pages
    ini_set('display_errors', 0);
    error_reporting(E_ALL);

    // Convert PHP errors to exceptions
    set_error_handler(function ($level, $message, $file, $line) {
        throw new \ErrorException($message, 0, $level, $file, $line);
    });

    // Handle exceptions with custom error pages
    set_exception_handler(function ($exception) use ($errorHandler) {
        $errorHandler->handleException($exception);
    });
}









// Force logger initialization to see debug output
$container->get('logger');

/** @var \Core\FrontController $frontController */
$frontController = $container->get('frontController');
$url = $_SERVER['QUERY_STRING'] ?? '';
$frontController->run($url);
