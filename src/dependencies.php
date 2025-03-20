<?php

declare(strict_types=1);

use App\Features\Testy\Form\ContactFieldRegistry;
use App\Features\Testy\Form\ContactFormType;
use Core\Database\Connection;
use Core\FrontController;
use Core\Router;
use Core\Services\ConfigService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Helpers\DebugRt as Debug;
use Core\Form\CSRF\CSRFToken;
// use Core\Form\Validation\Validator;
use Core\Middleware\CSRFMiddleware;
// use Core\Form\FormBuilder;
use Core\Form\FormBuilder;
use Core\Form\FormFactory;
use Core\Form\FormHandler;
use Core\Form\FormBuilderInterface;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Form\FieldRegistryInterface;
use App\Features\Testy\Form\UserFieldRegistry;
use App\Features\Testy\Form\UserEditFormType;

// Define services
return [
    // 'environment' => $environment,
    'environment' => 'development',

    'httpFactory' => \DI\autowire(\Core\Http\HttpFactory::class),

    'responseEmitter' => \DI\autowire(\Core\Http\ResponseEmitter::class),

    'config' => \DI\autowire(ConfigService::class)
        // ->constructorParameter('basePath', __DIR__)
        ->constructorParameter('configPath', __DIR__ . '\\Config')
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
        //Debug::p($logger);

        if ($config['rotation'] ?? true) {
            $logger->cleanupOldLogs($config['retention_days'] ?? 30);
        }

        return $logger;
    },
    'Psr\Log\LoggerInterface' => \DI\get('logger'),

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


    'sessionManager' => function (ContainerInterface $c) {
        $environment = $c->get('environment');
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




    // Field registries - Create separate registry for contact forms
    ContactFieldRegistry::class => DI\create(App\Features\Testy\Form\ContactFieldRegistry::class),


    ContactFormType::class => function (ContainerInterface $c) {
        // Get the specific registry for contact forms
        $registry = $c->get(ContactFieldRegistry::class);

        // Return the form type with an empty config - config will be passed directly
        return new App\Features\Testy\Form\ContactFormType($registry, []);
    },

    //ContactFormType::class => function (ContainerInterface $c) {
   //     return new ContactFormType($c->get(ContactFieldRegistry::class), []);
    //},

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
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('config', \DI\get('config'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        ->constructorParameter('logger', \DI\get('logger'))
        ->constructorParameter('contactFieldRegistry', \DI\get(ContactFieldRegistry::class))
        ->constructorParameter('contactFormType', \DI\get(ContactFormType::class)),

    'App\Features\Admin\Dashboard\DashboardController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory')),


    // ...existing definitions

    // Database configuration
    'config.database' => function (ContainerInterface $c) {
        try {
            $config = $c->get('config')->get('database');

            // Validate the config
            if (!is_array($config) || !isset($config['connections']) || !isset($config['default'])) {
                throw new \Exception('Invalid or missing database configuration');
            }

            return $config;
        } catch (\Throwable $e) {
            // Log the error
            $c->get('logger')->error('Database configuration error: ' . $e->getMessage());

            // For development only, throw a more helpful exception
            if ($c->get('environment') === 'development') {
                throw new \Exception(
                    'Database configuration error. Please ensure database.php exists and is properly configured.',
                    0,
                    $e
                );
            }

            // For production, throw a generic error without details
            throw new \Exception('Application configuration error. Please contact support.');
        }
    },

    // Database connection
    'Core\Database\ConnectionInterface' => function (ContainerInterface $c) {
        $config = $c->get('config.database');
        $connectionConfig = $config['connections'][$config['default']];
        $logger = null;

        if ($config['logging']['enabled'] ?? false) {
            $logger = $c->get('Psr\Log\LoggerInterface');
        }

        return new \Core\Database\Connection($connectionConfig, $logger);
    },

    // Add alias for convenience
    'db' => function (ContainerInterface $c) {
        return $c->get('Core\Database\ConnectionInterface');
    },




    // CSRF Token
    CSRFToken::class => \DI\factory(function ($c) {
        return new CSRFToken(
            $c->get('sessionManager')  // Changed from 'session' to 'sessionManager'
        );
    }),

    // CSRF Middleware
    CSRFMiddleware::class => \DI\factory(function ($c) {
        return new CSRFMiddleware(
            $c->get(CSRFToken::class),
            $c->get('httpFactory'),
            ['/api'] // Exclude API paths from CSRF validation if needed
        );
    }),

    // Register the CSRF token as a service
    'csrf' => \DI\get(CSRFToken::class),


    // Form system
    FormBuilderInterface::class => DI\factory(function ($c) {
        return new FormBuilder($c->get(CSRFToken::class), 'form');
    }),
    FormFactoryInterface::class => DI\create(FormFactory::class)
        ->constructor(DI\get(FormBuilderInterface::class)),
    FormHandlerInterface::class => DI\factory(function ($c) {
        return new Core\Form\FormHandler(
            $c->get(CSRFToken::class)
        );
    }),

    // Shortcuts
    'formBuilder' => DI\get(FormBuilderInterface::class),
    'formFactory' => DI\get(FormFactoryInterface::class),
    'formHandler' => DI\factory(function ($c) {
        return new Core\Form\FormHandler(
            $c->get(CSRFToken::class)
        );
    }),

    // Field registries
    FieldRegistryInterface::class => DI\get(UserFieldRegistry::class),
    UserFieldRegistry::class => DI\create(UserFieldRegistry::class),

    // Form types
    UserEditFormType::class => DI\factory(function ($c, $params = []) {
        return new UserEditFormType(
            $c->get(UserFieldRegistry::class),
            $params['config'] ?? []
        );
    }),


    // More services...
];
