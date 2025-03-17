<?php

declare(strict_types=1);

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

///////////////////////////////////////////////////////
// Create PHP-DI container
$containerBuilder = new \DI\ContainerBuilder();
$containerBuilder->useAutowiring(true);

if ($environment === 'production') {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}


// Define services
$definitions = [
    'environment' => $environment,

    'httpFactory' => \DI\autowire(\Core\Http\HttpFactory::class),

    'responseEmitter' => \DI\autowire(\Core\Http\ResponseEmitter::class),

    'config' => \DI\autowire(ConfigService::class)
        ->constructorParameter('basePath', __DIR__ . '/../../src')
        ->constructorParameter('environment', \DI\get('environment')),

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

    // 'errorHandler' => \DI\autowire(\Core\ErrorHandler::class)
    //     ->constructorParameter('displayErrors', \DI\get('environment') === 'development')
    //     ->constructorParameter('logger', \DI\get('logger'))
    //     ->constructorParameter('container', \DI\get(\Psr\Container\ContainerInterface::class))
    //     ->constructorParameter('httpFactory', \DI\get('httpFactory')),

    'errorHandler' => \DI\autowire(\Core\ErrorHandler::class)
        // Replace 'displayErrors' with the actual parameter name from your constructor
        // ->constructorParameter('showErrors', \DI\get('environment') === 'development')
        ->constructorParameter('developmentMode', \DI\get('environment') === 'development')
        ->constructorParameter('logger', \DI\get('logger'))
        ->constructorParameter('container', \DI\get(\Psr\Container\ContainerInterface::class))
        ->constructorParameter('httpFactory', \DI\get('httpFactory')),

    'router' => \DI\autowire(Router::class)
        ->constructorParameter('container', \DI\get(\Psr\Container\ContainerInterface::class))
        ->constructorParameter('httpFactory', \DI\get('httpFactory')),
    Core\Router::class => \DI\get('router'),
    Core\RouterInterface::class => \DI\get('router'),


    Core\Middleware\MiddlewarePipeline::class => function ($container) {
        return Core\Middleware\MiddlewareFactory::createPipeline($container);
    },


    Core\Middleware\TimingMiddleware::class => \DI\autowire(),

    Core\Middleware\ErrorHandlerMiddleware::class => \DI\autowire()
    ->constructorParameter('errorHandler', \DI\get('errorHandler')),

    Core\Middleware\SessionMiddleware::class => \DI\autowire()
    ->constructorParameter('sessionManager', \DI\get('sessionManager')),

    'frontController' => \DI\autowire(FrontController::class)
        ->constructorParameter('router', \DI\get('router'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory')),
    FrontController::class => \DI\get('frontController'),


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
                $c->get('view'),
                $c->get('httpFactory')
            );
        }

        throw new \Exception("Controller class $controller_class not found");
    }),



    'flash' => \DI\autowire(\App\Services\FlashMessageService::class)
        ->constructorParameter('sessionManager', \DI\get('sessionManager')),
    'flashMessageService' => \DI\get('flash'),

    'Core\Errors\ErrorsController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
         ->constructorParameter('flash', \DI\get('flash'))
         ->constructorParameter('view', \DI\get('view'))
         ->constructorParameter('httpFactory', \DI\get('httpFactory')),

    'App\Features\Home\HomeController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
         ->constructorParameter('flash', \DI\get('flash'))
         ->constructorParameter('httpFactory', \DI\get('httpFactory')),

    'App\Features\About\AboutController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory')),

    'App\Features\Testy\TestyController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('config', \DI\get('config'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory')),

    'App\Features\Admin\Dashboard\DashboardController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory')),

    // More services...
];

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

# 244 233
