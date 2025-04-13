<?php

declare(strict_types=1);

use App\Features\Testy\Form\ContactFieldRegistry;
use App\Features\Testy\Form\ContactFormType;
use App\Helpers\DebugRt;
use Core\Constants\Consts;
use Core\FrontController;
use Core\Router;
use Core\Services\ConfigService;
use Psr\Container\ContainerInterface;
use Core\Form\CSRF\CSRFToken;
use Core\Form\FieldRegistryInterface;
use Core\Form\FormBuilderInterface;
use Core\Middleware\CSRFMiddleware;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Form\Validation\ValidatorRegistry;

// use Core\Form\FormFactory;

// use Core\Form\FormBuilderInterface;
// use Core\Form\FormHandlerInterface;
// use Core\Form\FieldRegistryInterface;
// use App\Features\Testy\Form\UserFieldRegistry;
// use App\Features\Testy\Form\UserEditFormType;

// use Core\Database\Connection;
// use Psr\Log\LoggerInterface;
// use App\Helpers\DebugRt as Debug;
// use Core\Form\FormBuilder;
// use Core\Form\FormHandler;

// Define services
return [
    'environment' => $_ENV['APP_ENV'] ?? 'development',

    'httpFactory' => \DI\autowire(\Core\Http\HttpFactory::class),

    'responseEmitter' => \DI\autowire(\Core\Http\ResponseEmitter::class),

    'config' => \DI\autowire(ConfigService::class)
        // ->constructorParameter('basePath', __DIR__)
        ->constructorParameter('configPath', __DIR__ . '\\Config')
        ->constructorParameter('environment', \DI\get('environment')),

    'Core\Interfaces\ConfigInterface' => \DI\get('config'),

    'route_params' => \DI\factory(function () {
        return [];
    }),


    // // Token service
    // 'Core\Security\TokenServiceInterface' => \DI\factory(function () {
    //     return new \Core\Security\TokenService();
    // }),
    // // Also register the concrete class for direct use if needed
    // 'Core\Security\TokenService' => \DI\factory(function () {
    //     return new \Core\Security\TokenService();
    // }),

    // Add this line near the top of the file
    'Core\Database\ConnectionInterface' => \DI\autowire(\Core\Database\Connection::class),

    // Token service
    'Core\Security\TokenServiceInterface' => \DI\autowire(\Core\Security\TokenService::class),
    // Also register the concrete class for direct use if needed
    'Core\Security\TokenService' => \DI\autowire(),

    'Core\View' => \DI\get('view'),


    'App\Services\Email\MailgunEmailService' => \DI\autowire()
        ->constructorParameter('config', \DI\get('config'))
        ->constructorParameter('logger', \DI\get('logger'))
        ->constructorParameter('view', \DI\get('view')),

    'App\Services\Email\SMTPEmailService' => \DI\autowire()
        ->constructorParameter('config', \DI\get('config'))
        ->constructorParameter('logger', \DI\get('logger'))
        ->constructorParameter('view', \DI\get('view')),

    // Dynamic email service provider selection:
    'App\Services\Interfaces\EmailServiceInterface' => function (ContainerInterface $container) {
        $config = $container->get('config');
        // $env = $config->get('app.env');
        // echo "Env: $env";

        // Get email config for the current environment
        $emailConfig = $config->get('email');

        // DebugRt::p($emailConfig);
        // echo "<br />EmailConfig: ";
        // echo "<pre>";
        // print_r($emailConfig);
        // echo "</pre>";

        if (!isset($emailConfig)) {
            //DebugRt::p(111); // TODO
            // If no config found, default to Mailgun
            return $container->get('App\Services\Email\MailgunEmailService');
        }

        // Get provider from the environment-specific config
        $provider = $emailConfig['providers']['default']; // No Fallback needed: 'mailgun';

        return match ($provider) {
            'smtp' => $container->get('App\Services\Email\SMTPEmailService'),
            'mailgun' => $container->get('App\Services\Email\MailgunEmailService')
        };
    },

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

    // Add this interface binding here
    'Core\Session\SessionManagerInterface' => DI\get('sessionManager'),

    // Auth components - add these here
    'Core\Http\ResponseFactory' => function (ContainerInterface $c) {
        return new \Core\Http\ResponseFactory(
            $c->get('httpFactory')
        );
    },


    // 'App\Repository\UserRepositoryInterface' => function (ContainerInterface $c) {
    //     return new \App\Repository\UserRepository(
    //         $c->get('Core\Database\ConnectionInterface') // Ensure this is registered
    //     );
    // },

    'App\Repository\UserRepositoryInterface' => \DI\autowire(App\Repository\UserRepository::class),

    'App\Repository\RememberTokenRepositoryInterface' => DI\autowire(App\Repository\RememberTokenRepository::class)
        ->constructorParameter('connection', DI\get('Core\Database\ConnectionInterface')),

    'Core\Auth\AuthenticationServiceInterface' => function (ContainerInterface $c) {
        return $c->get('Core\Auth\SessionAuthenticationService');
    },

    // 'Core\Auth\SessionAuthenticationService' => DI\autowire()
    //     ->constructorParameter('userRepository', DI\get(App\Repository\UserRepositoryInterface::class))
    //     ->constructorParameter('session', DI\get(Core\Session\SessionManagerInterface::class))
    //     ->constructorParameter(
    //         'rememberTokenRepository',
    //         DI\get(App\Repository\RememberTokenRepositoryInterface::class)
    //     )
    //     ->constructorParameter(
    //         'loginAttemptsRepository',
    //         DI\get(App\Repository\LoginAttemptsRepositoryInterface::class)
    //     )
    //     ->constructorParameter('config', DI\get('auth.config')),

    // Rate limiting service
    // Repository binding (keep only one)
    'App\Repository\RateLimitRepositoryInterface' => DI\autowire(App\Repository\RateLimitRepository::class)
        ->constructorParameter('connection', DI\get('Core\Database\ConnectionInterface')),

    // Rate limiting service - fixed parameters to match constructor
    'Core\Security\RateLimitServiceInterface' => DI\autowire(Core\Security\RateLimitService::class)
        ->constructorParameter('repository', DI\get('App\Repository\RateLimitRepositoryInterface'))
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('customConfig', DI\get('config.rate_limits')),

    // Configuration values
    'config.rate_limits' => [
        'login' => ['limit' => 5, 'window' => 300],          // 5 attempts per 5 minutes
        'registration' => ['limit' => 3, 'window' => 1800],   // 3 attempts per 30 minutes
        'password_reset' => ['limit' => 3, 'window' => 900],  // 3 attempts per 15 minutes
        'email_verification' => ['limit' => 5, 'window' => 900], // 5 attempts per 15 minutes
        'activation_resend' => ['limit' => 3, 'window' => 1800], // 3 attempts per 30 minutes
    ],

    'Core\Security\BruteForceProtectionService' => DI\autowire()
        ->constructorParameter('repository', DI\get('App\Repository\RateLimitRepositoryInterface'))
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('customConfig', [
            // Override email verification to be more permissive for testing
            'email_verification' => [
                'max_attempts' => 4,      // Allow more attempts (default is 5)
                'ip_max_attempts' => 7,   // Allow more IP attempts (default is 15)
                'lockout_time' => 300      // Only 5 minutes lockout (default is 15 minutes/900s)
            ],
            // Make login much stricter
            'login' => [
                'max_attempts' => 16,       // More strict than default (5)
                'lockout_time' => 60     // Longer lockout (30 minutes)
            ]
        ]),

    'Core\Auth\SessionAuthenticationService' => DI\autowire()
        ->constructorParameter('userRepository', DI\get(App\Repository\UserRepositoryInterface::class))
        ->constructorParameter('session', DI\get(Core\Session\SessionManagerInterface::class))
        ->constructorParameter(
            'rememberTokenRepository',
            DI\get(App\Repository\RememberTokenRepositoryInterface::class)
        ),
        // foofee
        // ->constructorParameter('bruteForceProtection', DI\get('Core\Security\BruteForceProtectionService')),

    // Add this after the RememberTokenRepository registration
    // 'App\Repository\LoginAttemptsRepositoryInterface' => DI\autowire(App\Repository\LoginAttemptsRepository::class)
    //     ->constructorParameter('connection', DI\get('Core\Database\ConnectionInterface')),


    // Registration form components
    'App\Features\Auth\Form\RegistrationFieldRegistry' => \DI\factory(function () {
        return new \App\Features\Auth\Form\RegistrationFieldRegistry();
    }),

    'App\Features\Auth\Form\RegistrationFormType' => \DI\factory(function (ContainerInterface $c) {
        return new \App\Features\Auth\Form\RegistrationFormType(
            $c->get('App\Features\Auth\Form\RegistrationFieldRegistry')
        );
    }),


    // Rate limiting middleware
    'Core\Middleware\RateLimitMiddleware' => \DI\autowire()
        // foofee
        // ->constructorParameter('protectionService', \DI\get('Core\Security\BruteForceProtectionService'))
        ->constructorParameter('rateLimitService', \DI\get('Core\Security\RateLimitServiceInterface'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('configPath', [
            'path_mappings' => [
                '/registration' => 'registration',
                '/login' => 'login',
                '/forgot-password' => 'password_reset',
                '/verify-email/resend' => 'activation_resend',
                '/verify-email/verify' => 'email_verification'
            ]
        ]),

    'Core\Security\Captcha\CaptchaServiceInterface' => \DI\factory(function (ContainerInterface $c) {
        $config = $c->get('config');
        $captchaConfig = $config->get('security.captcha', []);
        $bruteForceService = $c->get('Core\Security\BruteForceProtectionService');

        $siteKey = $captchaConfig['site_key'] ?? $_ENV['RECAPTCHA_SITE_KEY'] ?? '';
        $secretKey = $captchaConfig['secret_key'] ?? $_ENV['RECAPTCHA_SECRET_KEY'] ?? '';

        return new \Core\Security\Captcha\GoogleReCaptchaService(
            $siteKey,
            $secretKey,
            $bruteForceService,
            $captchaConfig
        );
    }),

    // Add this to the Field Types section in dependencies.php
    'field.type.captcha' => function (ContainerInterface $c) {
        return new \Core\Form\Field\Type\CaptchaFieldType(
            $c->get('Core\Security\Captcha\CaptchaServiceInterface')
        );
    },







    // UserService
    'App\Services\UserService' => \DI\autowire()
        ->constructorParameter('userRepository', \DI\get('App\Repository\UserRepositoryInterface'))
        ->constructorParameter('tokenService', \DI\get('Core\Security\TokenServiceInterface')),

    // UserValidationService
    'App\Services\UserValidationService' => \DI\autowire(),

    // RegistrationService
    // 'App\Services\RegistrationService' => \DI\autowire()
        // ->constructorParameter('userService', \DI\get('App\Services\UserService'))
        // ->constructorParameter('validationService', \DI\get('App\Services\UserValidationService'))
        // ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService')),

    // RegistrationService
        'App\Services\RegistrationService' => \DI\autowire()
            ->constructorParameter('userService', \DI\get('App\Services\UserService'))
            ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService')),

    // Update RegistrationController to match current parameters
    'App\Features\Auth\RegistrationController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        ->constructorParameter('registrationFormType', \DI\get('App\Features\Auth\Form\RegistrationFormType'))
        ->constructorParameter('registrationService', \DI\get('App\Services\RegistrationService')),



    // // RegistrationController with all dependencies
    // 'App\Features\Auth\RegistrationController' => \DI\autowire()
    //     ->constructorParameter('route_params', \DI\get('route_params'))
    //     ->constructorParameter('flash', \DI\get('flash'))
    //     ->constructorParameter('view', \DI\get('view'))
    //     ->constructorParameter('httpFactory', \DI\get('httpFactory'))
    //     ->constructorParameter('container', \DI\get(ContainerInterface::class))
    //     ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
    //     ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
    //     ->constructorParameter('userRepository', \DI\get('App\Repository\UserRepositoryInterface'))
    //     ->constructorParameter('registrationFormType', \DI\get('App\Features\Auth\Form\RegistrationFormType'))
    //     ->constructorParameter('authService', \DI\get('Core\Auth\AuthenticationServiceInterface'))
    //     ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService')),

    // Auth Middleware
    'Core\Middleware\Auth\RequireAuthMiddleware' => function (ContainerInterface $c) {
        return new \Core\Middleware\Auth\RequireAuthMiddleware(
            $c->get('Core\Auth\AuthenticationServiceInterface'),
            $c->get('Core\Http\ResponseFactory'),
            '/login'
        );
    },

    'Core\Middleware\Auth\RequireRoleMiddleware' => function (ContainerInterface $c) {
        return new \Core\Middleware\Auth\RequireRoleMiddleware(
            $c->get('Core\Auth\AuthenticationServiceInterface'),
            $c->get('Core\Http\ResponseFactory'),
            'admin',
            '/unauthorized'
        );
    },

    'Core\Middleware\Auth\GuestOnlyMiddleware' => function (ContainerInterface $c) {
        return new \Core\Middleware\Auth\GuestOnlyMiddleware(
            $c->get('Core\Auth\AuthenticationServiceInterface'),
            $c->get('Core\Http\ResponseFactory'),
            '/'
        );
    },






    //'view' => \DI\autowire(Core\View::class)
   //     ->constructorParameter('config', \DI\get('config')),
    'view' => \DI\autowire(\Core\View::class)
        ->constructorParameter('config', \DI\get('config')),
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

    'App\Services\Interfaces\FlashMessageServiceInterface' => \DI\get('flash'),


    'App\Features\Auth\EmailVerificationController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))  // Use 'flash', not the interface
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('userRepository', \DI\get('App\Repository\UserRepositoryInterface'))
        ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService'))
        ->constructorParameter('logger', \DI\get('logger')),

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
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),

    'App\Features\About\AboutController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),

    'App\Features\Account\Mynotes\MynotesController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),

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
        ->constructorParameter('contactFormType', \DI\get(ContactFormType::class))
        ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService')),


    // LoginService
    'App\Services\LoginService' => \DI\autowire()
        ->constructorParameter('userRepository', \DI\get('App\Repository\UserRepositoryInterface'))
        ->constructorParameter('loginAttemptsRepository', \DI\get('App\Repository\LoginAttemptsRepositoryInterface'))
        ->constructorParameter('rememberTokenRepository', \DI\get('App\Repository\RememberTokenRepositoryInterface'))
        ->constructorParameter('session', \DI\get('Core\Session\SessionManagerInterface')),

    // Update LoginController to use LoginService
    'App\Features\Auth\LoginController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        //->constructorParameter('loginService', \DI\get('App\Services\LoginService'))
        ->constructorParameter('authService', \DI\get('Core\Auth\AuthenticationServiceInterface'))
        ->constructorParameter('loginFormType', \DI\get('App\Features\Auth\Form\LoginFormType'))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),

    'App\Features\Admin\Dashboard\DashboardController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),

    // Auth components
    'App\Features\Auth\Form\LoginFieldRegistry' => \DI\factory(function () {
        return new \App\Features\Auth\Form\LoginFieldRegistry();
    }),

    'App\Features\Auth\Form\LoginFormType' => \DI\factory(function (ContainerInterface $c) {
        return new \App\Features\Auth\Form\LoginFormType(
            $c->get('App\Features\Auth\Form\LoginFieldRegistry')
        );
    }),


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

    // Create the constants object
    'Core\Constants\Consts2' => \DI\create(\Core\Constants\Consts2::class),

    'const' => function (ContainerInterface $c) {
        return $c->get('Core\Constants\Consts');
    },
    'const2' => function (ContainerInterface $c) {
        return $c->get('Core\Constants\Consts2');
    },

    ////////////////////////////
    ////////////////////////////
    ////////////////////////////

    // Field Types
    'field.type.text' => function () {
        return new \Core\Form\Field\Type\TextType();
    },
    'field.type.email' => function () {
        return new \Core\Form\Field\Type\EmailType();
    },
    'field.type.textarea' => function () {
        return new \Core\Form\Field\Type\TextareaType();
    },

    'field.type.password' => function () {
        return new \Core\Form\Field\Type\PasswordType();
    },

    'field.type.checkbox' => function () {
        return new \Core\Form\Field\Type\CheckboxType();
    },

    // Field Type Registry
    \Core\Form\Field\Type\FieldTypeRegistry::class => \DI\factory(function (ContainerInterface $c) {
        $registry = new \Core\Form\Field\Type\FieldTypeRegistry([
            $c->get('field.type.text'),
            $c->get('field.type.email'),
            $c->get('field.type.textarea'),
            $c->get('field.type.password'),
            $c->get('field.type.checkbox'),
            $c->get('field.type.captcha')
        ]);
        return $registry;
    }),

    // Static Single-Field Validators
    'validator.required' => function () {
        return new \Core\Form\Validation\Rules\RequiredValidator();
    },
    'validator.email' => function () {
        return new \Core\Form\Validation\Rules\EmailValidator();
    },
    'validator.length' => function () {
        return new \Core\Form\Validation\Rules\LengthValidator();
    },
    'validator.regex' => function () {
        return new \Core\Form\Validation\Rules\RegexValidator();
    },

    // custom Single-Field Validators too, but with external content
    'validator.unique_username' => function (ContainerInterface $c) {
        return new \Core\Form\Validation\Rules\UniqueEntityValidator(
            $c->get('App\Repository\UserRepositoryInterface'),
            'username',
            'This username is already taken.'
        );
    },
    'validator.unique_email' => function (ContainerInterface $c) {
        return new \Core\Form\Validation\Rules\UniqueEntityValidator(
            $c->get('App\Repository\UserRepositoryInterface'),
            'email',
            'This email address is already registered.'
        );
    },

    // Add this with your other validators
    'validator.captcha' => function (ContainerInterface $c) {
        return new \Core\Form\Validation\Rules\CaptchaValidator(
            $c->get('Core\Security\Captcha\CaptchaServiceInterface')
        );
    },


    // Register the ValidatorRegistry
    \Core\Form\Validation\ValidatorRegistry::class => \DI\factory(function (ContainerInterface $c) {
        $registry = new \Core\Form\Validation\ValidatorRegistry([
            $c->get('validator.required'),
            $c->get('validator.email'),
            $c->get('validator.length'),
            $c->get('validator.regex'),
            $c->get('validator.unique_username'),
            $c->get('validator.unique_email'),
            $c->get('validator.captcha'),
        ]);
        return $registry;
    }),

    // Main Validator
    \Core\Form\Validation\Validator::class => \DI\factory(function (ContainerInterface $c) {
        return new \Core\Form\Validation\Validator(
            $c->get(\Core\Form\Validation\ValidatorRegistry::class)
        );
    }),

    // TODO...revisit for FormDataSanitizer
    // Event Dispatcher
    // Psr\EventDispatcher\EventDispatcherInterface::class => \DI\factory(function (ContainerInterface $c) {
    //     $dispatcher = new \Core\Event\EventDispatcher();
    //     $dispatcher->addSubscriber(new \Core\Form\Event\Subscriber\FormDataSanitizer());
    //     return $dispatcher;
    // }),
    // Event Dispatcher - Temporary version without FormDataSanitizer
    Psr\EventDispatcher\EventDispatcherInterface::class => \DI\factory(function (ContainerInterface $c) {
        $dispatcher = new \Core\Event\EventDispatcher();
        // Temporarily commented out until FormDataSanitizer is implemented
        // $dispatcher->addSubscriber(new \Core\Form\Event\Subscriber\FormDataSanitizer());
        return $dispatcher;
    }),



        ////////////////////////////
    ////////////////////////////
    ////////////////////////////


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
    // FormBuilderInterface::class => DI\factory(function ($c) {
    //     return new FormBuilder($c->get(CSRFToken::class), 'form');
    // }),
    FormBuilderInterface::class => \DI\factory(function (ContainerInterface $c) {
        // Note: We're not actually creating a FormBuilder directly here,
        // as it's created by the FormFactory during form creation.
        // This is just a placeholder for type hinting.
        return new \Core\Form\FormBuilder(
            new \Core\Form\Form('placeholder', $c->get(CSRFToken::class)),
            $c->get(\Core\Form\Field\Type\FieldTypeRegistry::class)
        );
    }),

    // FormFactoryInterface::class => DI\create(FormFactory::class)
    //     ->constructor(DI\get(FormBuilderInterface::class)),
    // FormHandlerInterface::class => DI\factory(function ($c) {
    //     return new Core\Form\FormHandler(
    //         $c->get(CSRFToken::class)
    //     );
    // }),
    FormFactoryInterface::class => \DI\factory(function (ContainerInterface $c) {
        return new \Core\Form\FormFactory(
            $c->get(\Core\Form\CSRF\CSRFToken::class),
            $c->get(\Core\Form\Field\Type\FieldTypeRegistry::class),
            $c->get(\Core\Form\Validation\Validator::class),
            $c->get(\Core\Form\Renderer\RendererRegistry::class)
        );
    }),
    FormHandlerInterface::class => \DI\factory(function (ContainerInterface $c) {
        return new \Core\Form\FormHandler(
            $c->get(CSRFToken::class),
            $c->get(ValidatorRegistry::class),
            $c->get(Psr\EventDispatcher\EventDispatcherInterface::class)
        );
    }),



    // Shortcuts
    'formBuilder' => DI\get(FormBuilderInterface::class),
    'formFactory' => DI\get(FormFactoryInterface::class),
    'formHandler' => \DI\factory(function ($c) {
        return new Core\Form\FormHandler(
            $c->get(CSRFToken::class),
            $c->get(Psr\EventDispatcher\EventDispatcherInterface::class)
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


    // Form Renderers
    'form.renderer.bootstrap' => \DI\factory(function () {
        return new \Core\Form\Renderer\BootstrapRenderer();
    }),

    // Renderer Registry
    \Core\Form\Renderer\RendererRegistry::class => \DI\factory(function (ContainerInterface $c) {
        $registry = new \Core\Form\Renderer\RendererRegistry();
        $registry->register('bootstrap', $c->get('form.renderer.bootstrap'));

        // Set default renderer based on environment setting
        $defaultRenderer = $_ENV['FORM_CSS_FRAMEWORK'] ?? 'bootstrap';
        $registry->setDefaultRenderer($defaultRenderer);

        return $registry;
    }),

    // More services...
];
