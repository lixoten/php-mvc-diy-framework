<?php

declare(strict_types=1);

use App\Helpers\DebugRt;
use App\Repository\AlbumRepository;
use App\Repository\AlbumRepositoryInterface;
use Core\Database\ConnectionInterface;
use App\Repository\PostRepositoryInterface;
use App\Repository\TestyRepositoryInterface;
use App\Repository\RepositoryRegistry;
use App\Repository\RepositoryRegistryInterface;
use App\Repository\StoreRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Services\FlashMessageService;
use App\Services\GenericDataService;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\Interfaces\GenericDataServiceInterface;
use Core\Constants\Consts;
use Core\Context\CurrentContext;
use Core\FrontController;
use Core\Router;
use Core\Services\ConfigService;
use Psr\Container\ContainerInterface;
use Core\Form\CSRF\CSRFToken;
use Core\Form\FormFieldRegistryInterface;
use Core\Form\FormBuilderInterface;
use Core\Middleware\CSRFMiddleware;
use Core\Form\FormFactoryInterface;
use Core\Form\FormHandlerInterface;
use Core\Form\Validation\ValidatorRegistry;
use Core\I18n\LabelProvider;
use Core\List\ListFactoryInterface;
use Core\Middleware\RoutingMiddleware;
use Core\RouterInterface;

// Define services
return [
    'environment' => $_ENV['APP_ENV'] ?? 'development',



    'httpFactory' => \DI\autowire(\Core\Http\HttpFactory::class),

    'responseEmitter' => \DI\autowire(\Core\Http\ResponseEmitter::class),

    // Important!!! Lesson: Define the 'config' service
    'config' => \DI\autowire(ConfigService::class)
        // ->constructorParameter('basePath', __DIR__)
        ->constructorParameter('configPath', __DIR__ . '\\Config')
        ->constructorParameter('environment', \DI\get('environment')),


   // ConfigServiceInterface::class => \DI\autowire(ConfigService::class),


    // Important!!! Lesson: Alias the ConfigInterface to 'config'
    'Core\Interfaces\ConfigInterface' => \DI\get('config'),

    //-----------------------------------------------------------------

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

    //-----------------------------------------------------------------

    'route_params' => \DI\factory(function () {
        return [];
    }),

    //-----------------------------------------------------------------

    'Core\Database\ConnectionInterface' => \DI\autowire(\Core\Database\Connection::class),

    //-----------------------------------------------------------------

    // Token service
    'Core\Security\TokenServiceInterface' => \DI\autowire(\Core\Security\TokenService::class),
    // Also register the concrete class for direct use if needed
    'Core\Security\TokenService' => \DI\autowire(),

    //-----------------------------------------------------------------


    'Core\View' => \DI\get('view'),

    //-----------------------------------------------------------------

    //-----------------------------------------------------------------


    // Define the service with a short, convenient name.
    'sessionManager' => function (ContainerInterface $c) {
        $environment = $c->get('environment');
        return new \Core\Session\SessionManager([
            'name' => 'mvc3_session',
            'secure' => $environment === 'production',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    },

    // Bind the interface to the short name. This is the best practice.
    'Core\Session\SessionManagerInterface' => DI\get('sessionManager'),


    //-----------------------------------------------------------------



    //------------------------------------------------------------------------
    // EXAMPLE - Using Aliases / Shortcuts
    // Define the service with a short, convenient name.
    'flash' => \DI\autowire(\App\Services\FlashMessageService::class)
        ->constructorParameter('sessionManager', \DI\get('sessionManager')),

    // Bind the interface to the short name. This is the best practice.
    'App\Services\Interfaces\FlashMessageServiceInterface' => \DI\get('flash'),


    // //------------------------------------------------------------------------
    // // EXAMPLE - Not using Aliases / Shortcuts
    // // This defines the concrete implementation
    // FlashMessageService::class => \DI\autowire()
    //     ->constructorParameter('sessionManager', \DI\get('sessionManager')),

    // // This binds the interface to the concrete class
    // FlashMessageServiceInterface::class => \DI\get(FlashMessageService::class),
    // //------------------------------------------------------------------------



    //-----------------------------------------------------------------



    'errorHandler' => \DI\autowire(\Core\ErrorHandler::class)
        ->constructorParameter('developmentMode', function (ContainerInterface $c) {
            return $c->get('environment') === 'development';
        })
        ->constructorParameter('logger', \DI\get('logger'))
        ->constructorParameter('container', \DI\get(\Psr\Container\ContainerInterface::class))
        ->constructorParameter('httpFactory', \DI\get('httpFactory')),

    //-----------------------------------------------------------------


    // Router definition
    'router' => \DI\autowire(Router::class)
        ->constructorParameter('container', \DI\get(\Psr\Container\ContainerInterface::class))
        ->constructorParameter('httpFactory', \DI\get('httpFactory')),
        Core\Router::class => \DI\get('router'),
        Core\RouterInterface::class => \DI\get('router'),

    //-------------------------------------------------------------------------

    'Core\Auth\AuthenticationServiceInterface' => function (ContainerInterface $c) {
        return $c->get('Core\Auth\SessionAuthenticationService');
    },



    //-------------------------------------------------------------------------


    // Middleware Pipeline definition
    Core\Middleware\MiddlewarePipeline::class => function ($container) {
        return Core\Middleware\MiddlewareFactory::createPipeline($container);
    },

    // Register RoutingMiddleware
    RoutingMiddleware::class => \DI\autowire()
        ->constructorParameter('router', \DI\get(RouterInterface::class)),

    Core\Middleware\TimingMiddleware::class => \DI\autowire(),

    Core\Middleware\ErrorHandlerMiddleware::class => \DI\autowire()
    ->constructorParameter('errorHandler', \DI\get('errorHandler')),

    Core\Middleware\SessionMiddleware::class => \DI\autowire()
    ->constructorParameter('sessionManager', \DI\get('sessionManager')),

    Core\Middleware\ContextPopulationMiddleware::class => \DI\autowire()
        ->constructorParameter('authService', \DI\get(Core\Auth\AuthenticationServiceInterface::class))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('currentContext', \DI\get(CurrentContext::class)),
        //->constructorParameter('container', \DI\get(ContainerInterface::class)),

    //-----------------------------------------------------------------

    CurrentContext::class => \DI\autowire(),

    //-----------------------------------------------------------------

    'Core\Services\FieldRegistryService' => \DI\autowire()
        ->constructorParameter('configService', \DI\get('config')),

    //-----------------------------------------------------------------

    // Dynamic-me
    // Page Registry Service
    'App\Services\Interfaces\PageRegistryInterface' => \DI\autowire('App\Services\PageRegistry'),
    'App\Services\PageRegistry' => \DI\autowire(),

    //-----------------------------------------------------------------

    // FrontController definition
    'frontController' => \DI\autowire(FrontController::class)
        ->constructorParameter('router', \DI\get('router'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        // Dynamic-me
        ->constructorParameter('pageRegistry', \DI\get('App\Services\Interfaces\PageRegistryInterface'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),

        FrontController::class => \DI\get('frontController'),

    //-----------------------------------------------------------------


    // Navigation Service
    'App\Services\NavigationService' => \DI\autowire()
        ->constructorParameter('authService', \DI\get('Core\Auth\AuthenticationServiceInterface'))
        ->constructorParameter('storeRepository', \DI\get('App\Repository\StoreRepositoryInterface'))
        ->constructorParameter('context', \DI\get('Core\Context\CurrentContext')),


    //-----------------------------------------------------------------


    /// Dynamic-me 2
    // --- Content Type Management ---
    'App\Services\Interfaces\ContentTypeRegistryInterface' => \DI\autowire('App\Services\ContentTypeRegistry'),
    'App\Services\ContentTypeRegistry' => \DI\autowire(), // Autowiring should inject ConfigInterface

    /// Dynamic-me 2
    // --- Generic CRUD Components ---
    'App\Features\GenericCrud\GenericCrudController' => \DI\autowire(), // Autowires dependencies including ContentTypeRegistry
    'App\Features\Generic\Form\GenericFormType' => \DI\autowire(), // Autowires dependencies like CaptchaService
    // 'App\Features\Generic\List\DynamicListType' => \DI\autowire(), // Register when created


    //-----------------------------------------------------------------



    //-----------------------------------------------------------------


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

    // 'Core\Auth\AuthenticationServiceInterface' => function (ContainerInterface $c) {
    //     return $c->get('Core\Auth\SessionAuthenticationService');
    // },

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



    // Store context middleware
    'Core\Middleware\StoreContextMiddleware' => \DI\autowire()
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('storeContext', \DI\get('App\Services\StoreContext'))
        ->constructorParameter('responseFactory', \DI\get('Core\Http\ResponseFactory'))
        ->constructorParameter('currentContext', \DI\get('Core\Context\CurrentContext')),




    // Rate limiting service
    // Repository binding (keep only one)
    'App\Repository\RateLimitRepositoryInterface' => DI\autowire(App\Repository\RateLimitRepository::class)
        ->constructorParameter('connection', DI\get('Core\Database\ConnectionInterface')),

    // Rate limiting service - fixed parameters to match constructor
    'Core\Security\RateLimitServiceInterface' => DI\autowire(Core\Security\RateLimitService::class)
        ->constructorParameter('repository', DI\get('App\Repository\RateLimitRepositoryInterface'))
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface')),
        // ->constructorParameter('customConfig', DI\get('config.rate_limits')),

    // // Configuration values
    // 'config.rate_limits' => [
    //     'contact_direct' => ['limit' => 5, 'window' => 300],          // 5 attempts per 5 minutes
    //     'login' => ['limit' => 5, 'window' => 300],          // 5 attempts per 5 minutes
    //     'registration' => ['limit' => 3, 'window' => 1800],   // 3 attempts per 30 minutes
    //     'password_reset' => ['limit' => 3, 'window' => 900],  // 3 attempts per 15 minutes
    //     'email_verification' => ['limit' => 5, 'window' => 900], // 5 attempts per 15 minutes
    //     'activation_resend' => ['limit' => 3, 'window' => 1800], // 3 attempts per 30 minutes
    // ],

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
        )
        ->constructorParameter(
            'storeRepository',
            DI\get(App\Repository\StoreRepositoryInterface::class)
        ),
        // foofee
        // ->constructorParameter('bruteForceProtection', DI\get('Core\Security\BruteForceProtectionService')),

    // Add this after the RememberTokenRepository registration
    // 'App\Repository\LoginAttemptsRepositoryInterface' => DI\autowire(App\Repository\LoginAttemptsRepository::class)
    //     ->constructorParameter('connection', DI\get('Core\Database\ConnectionInterface')),


    'Core\Services\UrlServiceInterface' => \DI\autowire('Core\Services\UrlService'),
        // ->lazy(), // FUTURE - in needed

    // Register URL Service implementation
    'Core\Services\UrlService' => \DI\autowire(),

    // URL Service Provider
    'Core\Providers\UrlServiceProvider' => \DI\autowire(),

    // Initialize URL Service - runs the provider to set up URLs
    'urlServiceInitializer' => \DI\factory(function (
        Core\Providers\UrlServiceProvider $provider,
        Core\Services\UrlServiceInterface $urlService,
        Core\Interfaces\ConfigInterface $configService
    ) {
        // $provider->register($urlService, $configService);
        $provider->register($urlService, $configService);
        return true; // Just to return something
    }),

    // Shortcut for convenience
    'url' => \DI\get('Core\Services\UrlServiceInterface'),



    // Rate limiting middleware
    'Core\Middleware\RateLimitMiddleware' => \DI\autowire()
        // foofee
        // ->constructorParameter('protectionService', \DI\get('Core\Security\BruteForceProtectionService'))
        ->constructorParameter('rateLimitService', \DI\get('Core\Security\RateLimitServiceInterface'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface')),
        // ->constructorParameter('configPathMapp', [
        //     'path_mappings' => [
        //         '/registration' => 'registration',
        //         '/login' => 'login',
        //         '/forgot-password' => 'password_reset',
        //         '/verify-email/resend' => 'activation_resend',
        //         '/verify-email/verify' => 'email_verification'
        //     ]
        // ]),

    'Core\Security\Captcha\CaptchaServiceInterface' => \DI\factory(function (ContainerInterface $c) {
        $config = $c->get('config');
        $captchaConfig = $config->get('security.captcha', []);
        // DebugRt::j('1', '', $captchaConfig);
        $bruteForceService = null; // $c->get('Core\Security\BruteForceProtectionService'); // foofee

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







    'Core\I18n\LabelProvider' => function () {
        $posts = include dirname(__DIR__) . '/lang/en/posts.php';
        $albums = include dirname(__DIR__) . '/lang/en/albums.php';
        $common = include dirname(__DIR__) . '/lang/en/common.php';
        // Merge posts and common for the provider
        $labels = [
            'posts' => $posts,
            'albums' => $albums,
            'common' => $common,
        ];
        return new \Core\I18n\LabelProvider($labels);
    },




    //-------------------------------------------------------------------------



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


    //-------------------------------------------------------------------------







    // UserService
    'App\Services\UserService' => \DI\autowire()
        ->constructorParameter('userRepository', \DI\get('App\Repository\UserRepositoryInterface'))
        ->constructorParameter('tokenService', \DI\get('Core\Security\TokenServiceInterface')),

    // UserValidationService
    'App\Services\UserValidationService' => \DI\autowire(),





    // Add the StoreContext registration:
    // StoreContext service
    'App\Services\StoreContext' => \DI\autowire()
        ->constructorParameter('session', \DI\get(Core\Session\SessionManagerInterface::class))
        ->constructorParameter('storeRepository', \DI\get(App\Repository\StoreRepositoryInterface::class))
        ->constructorParameter('authService', \DI\get(Core\Auth\AuthenticationServiceInterface::class)),

    // Also register as a string alias for easier access
    'store.context' => \DI\get(App\Services\StoreContext::class),





    // RegistrationService
    // 'App\Services\RegistrationService' => \DI\autowire()
        // ->constructorParameter('userService', \DI\get('App\Services\UserService'))
        // ->constructorParameter('validationService', \DI\get('App\Services\UserValidationService'))
        // ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService')),

    // RegistrationService
        'App\Services\RegistrationService' => \DI\autowire()
            ->constructorParameter('userService', \DI\get('App\Services\UserService'))
            ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService')),

// fffffffffffffffffffffffffffffffffffffffffffffffffffffffff
// fffffffffffffffffffffffffffffffffffffffffffffffffffffffff
// fffffffffffffffffffffffffffffffffffffffffffffffffffffffff
// fffffffffffffffffffffffffffffffffffffffffffffffffffffffff

    // Add this to your existing container definitions

    // List system
    // $container->set(\Core\List\Renderer\ListRendererInterface::class, \DI\create(\Core\List\Renderer\BootstrapListRenderer::class));
    // $container->set(\Core\List\ListFactory::class, \DI\create(\Core\List\ListFactory::class)
    //     ->constructor(
    //         \DI\get(ContainerInterface::class),
    //         \DI\get(\Core\List\Renderer\ListRendererInterface::class)
    //     )
    // );

    // Add these lines at the end of your file before the closing bracket

    // ListView system dependencies
    'Core\List\Renderer\ListRendererInterface' => \DI\autowire(\Core\List\Renderer\BootstrapListRenderer::class),


    //-----------------------------------------------------------------

    'App\Features\Albums\Form\AlbumsFieldRegistry' => \DI\autowire()
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface')),
        // ->constructorParameter('baseRegistry', \DI\get('Core\Form\BaseFieldRegistry')),

    'App\Features\Albums\Form\AlbumsFormType' => \DI\autowire()
        ->constructorParameter('fieldRegistry', \DI\get('App\Features\Albums\Form\AlbumsFieldRegistry'))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),

    'App\Features\Albums\AlbumsController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        ->constructorParameter('albumRepository', \DI\get('App\Repository\AlbumRepositoryInterface'))
        ->constructorParameter('albumsFormType', \DI\get('App\Features\Albums\Form\AlbumsFormType')),

    //-----------------------------------------------------------------


    // Login field registry with inheritance
    'App\Features\Auth\Field\LoginFieldRegistry' => \DI\autowire()
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface')),
        // ->constructorParameter('config', \DI\get('config'))
        // ->constructorParameter('baseRegistry', \DI\get('Core\Registry\BaseFieldRegistry')),

    //-----------------------------------------------------------------

    'App\Features\Auth\Form\RegistrationFormFieldRegistry' => \DI\autowire()
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface')),
        // ->constructorParameter('baseRegistry', \DI\get('Core\Registry\BaseFieldRegistry')),

    //-----------------------------------------------------------------

    'App\Features\Contact\Form\ContactFieldRegistry' => \DI\autowire()
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface')),
        // ->constructorParameter('baseRegistry', \DI\get('Core\Form\BaseFieldRegistry')),

    //-----------------------------------------------------------------

    'App\Features\Contact\Form\ContactDirectFieldRegistry' => \DI\autowire()
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface')),
        // ->constructorParameter('baseRegistry', \DI\get('Core\Form\BaseFieldRegistry')),

    //-----------------------------------------------------------------


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
        ->constructorParameter('loginFormType', \DI\get('App\Features\Auth\Form\LoginFormType')),
        // ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),


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



    'App\Features\Contact\ContactController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        ->constructorParameter('logger', \DI\get('logger'))
        ->constructorParameter('contactFormType', \DI\get('App\Features\Contact\Form\ContactFormType'))
        ->constructorParameter('contactDirectFormType', \DI\get('App\Features\Contact\Form\ContactDirectFormType')),


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

    //-----------------------------------------------------------------

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
//







    'App\Features\Auth\EmailVerificationController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))  // Use 'flash', not the interface
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('userRepository', \DI\get('App\Repository\UserRepositoryInterface'))
        ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService'))
        ->constructorParameter('logger', \DI\get('logger')),



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



    'App\Features\Posts\PostsController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        // ->constructorParameter('currentContext', \DI\get(CurrentContext::class))
        ->constructorParameter('scrap', \DI\get(CurrentContext::class))
        ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        ->constructorParameter('repository', \DI\get('App\Repository\PostRepositoryInterface'))
        ->constructorParameter('formType', \DI\get('App\Features\Posts\Form\PostsFormType'))
        ->constructorParameter('listFactory', \DI\get('Core\List\ListFactory'))
        ->constructorParameter('listType', \DI\get('App\Features\Posts\List\PostsListType')),


    // 'App\Features\Admin\Generic\GenericController' => \DI\autowire()
    //     ->constructorParameter('route_params', \DI\get('route_params'))
    //     ->constructorParameter('flash', \DI\get('flash'))
    //     ->constructorParameter('view', \DI\get('view'))
    //     ->constructorParameter('httpFactory', \DI\get('httpFactory'))
    //     ->constructorParameter('container', \DI\get(ContainerInterface::class))
    //     // ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
    //     // ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
    //     ->constructorParameter('postRepository', \DI\get('App\Repository\PostRepositoryInterface')),
    //     // ->constructorParameter('postsFormType', \DI\get('App\Features\Stores\Posts\Form\PostsFormType')),

    // Dynamic-me 3
    // Update GenericController definition to use the GenericDataService
    'App\Features\Admin\Generic\GenericController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('listFactory', \DI\get('Core\List\ListFactoryInterface')) // Ensure ListFactory is injected
        ->constructorParameter('genericListType', \DI\get('App\Features\Admin\Generic\List\GenericListType')) // Ensure GenericListType is injected
        // Inject the GenericDataService instead of the specific repository
        ->constructorParameter('dataService', \DI\get(GenericDataServiceInterface::class))
        // ->constructorParameter('config', \DI\get('Core\Interfaces\ConfigInterface')), // Inject Config if needed for fields
    //     ->constructorParameter('postRepository', \DI\get('App\Repository\PostRepositoryInterface')),
        ->constructorParameter('columnRegistry', \DI\get('App\Features\Admin\Generic\List\GenericColumnRegistry'))
        ->constructorParameter('postRepository', \DI\get('App\Repository\PostRepositoryInterface')),

        // Inject the configuration parameters (example for 'posts')
        // These values would typically be defined per-route or in a factory for the controller
        // ->constructorParameter('entityType', 'posts') // Example value
        // ->constructorParameter('listFields', ['id', 'title', 'username', 'status', 'created_at']) // Example value
        // ->constructorParameter('repositoryInterfaceName', PostRepositoryInterface::class) // Less needed if using DataService
        //->constructorParameter('paginationUrl', '/admin/posts/page/{page:\d+}') // Example value - Use Url::STORE_POSTS->paginationUrl() if possible
        //->constructorParameter('viewTemplate', 'admin/generic/index') // Example value
        //->constructorParameter('pageTitle', 'Manage Posts'), // Example value






    'App\Features\Stores\Dashboard\DashboardController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),




    'App\Features\Account\Dashboard\DashboardController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),

    'App\Features\Account\Profile\ProfileController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),

    'App\Features\Account\MyNotes\MyNotesController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),

        // Post
    'App\Features\Stores\StoresController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        ->constructorParameter('storeRepository', \DI\get('App\Repository\StoreRepositoryInterface'))
        ->constructorParameter('storesFormType', \DI\get('App\Features\Stores\Form\StoresFormType')),




    'App\Features\Notes\NotesController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('config', \DI\get('config'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
            /////////////
        ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        ->constructorParameter('repository', \DI\get('App\Repository\PostRepositoryInterface'))
        ->constructorParameter('formType', \DI\get('App\Features\Posts\Form\PostsFormType'))
        ->constructorParameter('listFactory', \DI\get('Core\List\ListFactory'))
        ->constructorParameter('listType', \DI\get('App\Features\Posts\List\PostsListType')),



        // ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        // ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        // ->constructorParameter('logger', \DI\get('logger'))
        // ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService')),


    'App\Features\Admin\Dashboard\DashboardController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),


    // Dynamic-me 3
    // --- Generic Data Service Dependencies ---

    // 1. Define the Repository Registry and its mapping
    RepositoryRegistryInterface::class => \DI\autowire(RepositoryRegistry::class)
        ->constructorParameter('container', \DI\get(ContainerInterface::class)) // Pass the container itself
        ->constructorParameter('repositoryMap', [
            'testys' => TestyRepositoryInterface::class, // Map 'testys' string to the Post repo service ID/interface
            'posts' => PostRepositoryInterface::class, // Map 'posts' string to the Post repo service ID/interface
            'stores' => StoreRepositoryInterface::class, // Example: Map 'users' string to the User repo service ID/interface
            'users' => UserRepositoryInterface::class, // Example: Map 'users' string to the User repo service ID/interface
            // *** Add mappings for all entity types you want the GenericDataService to handle ***
            // 'products' => ProductRepositoryInterface::class,
        ]),

    // 2. Define the GenericDataService, injecting the registry
    GenericDataServiceInterface::class => \DI\autowire(GenericDataService::class)
        ->constructor(\DI\get(RepositoryRegistryInterface::class)), // Inject the registry

    // --- End Generic Data Service Dependencies ---
    // Dynamic-me 3









    // --- Generic List Components --- ADD THIS SECTION ---

    // Generic List Column Registry (Depends on ConfigInterface)
    'App\Features\Admin\Generic\List\GenericColumnRegistry' => \DI\autowire()
        ->constructorParameter('config', \DI\get('Core\Interfaces\ConfigInterface')),



    // --- End Generic List Components ---






    // --- Generic Form Components ---
    'App\Features\Admin\Generic\Form\GenericFieldRegistry' => \DI\autowire()
        ->constructorParameter('config', \DI\get('Core\Interfaces\ConfigInterface')),
        //..->constructorParameter('baseRegistry', null), // Or inject BaseFieldRegistry if needed: \DI\get('Core\Form\BaseFieldRegistry')

    // You might want an interface alias if you plan to swap implementations later
    // 'App\Features\Admin\Generic\Form\GenericFieldRegistryInterface' => \DI\get('App\Features\Admin\Generic\Form\GenericFieldRegistry'),









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



    'paginationService' => function (ContainerInterface $container) {
        return new \App\Services\PaginationService();
    },

    ////////////////////////////
    ////////////////////////////
    ////////////////////////////


    // CSRF Token
    CSRFToken::class => \DI\factory(function ($c) {
        return new CSRFToken(
            $c->get('sessionManager')  // Changed from 'session' to 'sessionManager'
        );
    }),
    // CSRFToken::class => \DI\autowire()
        // ->constructorParameter('sessionManager', \DI\get('sessionManager')),

    // Shortcut - Register the CSRF token as a service
    'csrf' => \DI\get(CSRFToken::class),


    // // CSRF Middleware
    // CSRFMiddleware::class => \DI\factory(function ($c) {
    //     return new CSRFMiddleware(
    //         $c->get(CSRFToken::class),
    //         $c->get('httpFactory'),
    //         ['/api'] // Exclude API paths from CSRF validation if needed
    //     );
    // }),
    // CSRF Middleware
    CSRFMiddleware::class => \DI\autowire()
        // ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        // ->constructorParameter('csrfToken', \DI\get('Core\Form\CSRF\CSRFToken'))
        ->constructorParameter('excludedPaths', ['/api']),


    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------

    //-------------------------------------------------------------------------


    // Testy the repository interface
    'App\Repository\TestyRepositoryInterface' => \DI\get('App\Repository\TestyRepository'),

    // Testy the concrete repository implementation
    'App\Repository\TestyRepository' => \DI\autowire()
        ->constructorParameter('connection', \DI\get('db')),

    // Post the repository interface
    'App\Repository\PostRepositoryInterface' => \DI\get('App\Repository\PostRepository'),

    // Post the concrete repository implementation
    'App\Repository\PostRepository' => \DI\autowire()
        ->constructorParameter('connection', \DI\get('db')),

    // Album the repository interface
    'App\Repository\AlbumRepositoryInterface' => \DI\get('App\Repository\AlbumRepository'),

    // Album the concrete repository implementation
    'App\Repository\AlbumRepository' => \DI\autowire()
        ->constructorParameter('connection', \DI\get('db')),

    // Store the repository interface
    'App\Repository\StoreRepositoryInterface' => \DI\get('App\Repository\StoreRepository'),

    // Store the concrete repository implementation
    'App\Repository\StoreRepository' => \DI\autowire()
        ->constructorParameter('connection', \DI\get('db')),

    //-------------------------------------------------------------------------




    // Section - Form types
    'App\Features\Testys\Form\TestysFormType' => \DI\autowire(),
        // ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        // ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
        // ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),

    'App\Features\Auth\Form\LoginFormType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface')),

    'App\Features\Auth\Form\RegistrationFormType' => \DI\autowire()
        ->constructorParameter('fieldRegistry', \DI\get('App\Features\Auth\Form\RegistrationFormFieldRegistry'))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),

    // 'App\Features\Contact\Form\ContactFormType' => \DI\autowire()
        // ->constructor(\DI\get('App\Features\Contact\Form\ContactFieldRegistry')),

    'App\Features\Contact\Form\ContactFormType' => \DI\autowire()
        // ->constructor(\DI\get('App\Features\Contact\Form\ContactFieldRegistry'))
        ->constructorParameter('fieldRegistry', \DI\get('App\Features\Contact\Form\ContactFieldRegistry'))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),

    'App\Features\Contact\Form\ContactDirectFormType' => \DI\autowire()
        // ->constructor(\DI\get('App\Features\Contact\Form\ContactDirectFieldRegistry')),
        ->constructorParameter('fieldRegistry', \DI\get('App\Features\Contact\Form\ContactDirectFieldRegistry'))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),


    'App\Features\Posts\Form\PostsFormType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),



    //-----------------------------------------------------------------

    // Section - List types

    'App\Features\Testys\List\TestysListType' => \DI\autowire(),
        // ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        // ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface')),

    'App\Features\Posts\List\PostsListType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface')),

    // Generic List Type (Depends on GenericColumnRegistry and ConfigInterface)
    'App\Features\Admin\Generic\List\GenericListType' => \DI\autowire()
        ->constructorParameter('columnRegistry', \DI\get('App\Features\Admin\Generic\List\GenericColumnRegistry'))
        ->constructorParameter('config', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('currentContext', \DI\get(CurrentContext::class)),


    // Section - Registry

    // Base field registry
    //..'Core\Registry\BaseFieldRegistry' => \DI\autowire(),

    // Posts ListView dependencies
    'App\Features\Posts\Field\PostsFieldRegistry' => \DI\autowire()
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface')),
        //..->constructorParameter('baseRegistry', \DI\get('Core\Registry\BaseFieldRegistry')),

    // Albums ListView dependencies
    'App\Features\Albums\List\AlbumsColumnRegistry' => \DI\autowire(),
    'App\Features\Albums\List\AlbumsListType' => \DI\autowire(),


    //-----------------------------------------------------------------




    //-------------------------------------------------------------------------

    'Core\Form\FormHandlerInterface' => DI\autowire(Core\Form\FormHandler::class),
        // ->constructorParameter('csrf', DI\get('Core\Form\CSRF\CSRFToken'))
        // ->constructorParameter('validatorRegistry', DI\get('Core\Form\Validation\ValidatorRegistry'))
        // ->constructorParameter('captchaService', DI\get('Core\Security\Captcha\CaptchaServiceInterface'))
        // ->constructorParameter('eventDispatcher', DI\get('Psr\EventDispatcher\EventDispatcherInterface')),

    // Shortcuts
    //'formHandler' => DI\get(FormHandlerInterface::class),

    // DeleteMe ---just do it
    // 'formHandler' => \DI\factory(function ($c) {
    //     return new Core\Form\FormHandler(
    //         $c->get(CSRFToken::class),
    //         $c->get(Psr\EventDispatcher\EventDispatcherInterface::class)
    //     );
    // }),



    // Notes-: — you do not need to create DI container entries in dependencies.php
    // for ListBuilder or ListView in this case. Since we instantiate them directly
    // in ListFactory using new ListBuilder(...) and new ListView(...),
    // and they do not require external configuration or dependencies from the container,
    // DI registration is unnecessary.
    // - FormBuilder
    // - Form
    // - ListBuilder
    // - ListView





    // 1.Bind the Interface to the Implementation
    ListFactoryInterface::class => \DI\autowire(\Core\List\ListFactory::class)
        ->constructorParameter('csrfToken', \DI\get('Core\Form\CSRF\CSRFToken'))
        ->constructorParameter('fieldTypeRegistry', \DI\get('Core\Form\Field\Type\FieldTypeRegistry'))
        ->constructorParameter('listRendererRegistry', \DI\get('Core\List\Renderer\ListRendererRegistry')),
    // //----------------------------------------------------------------------
    // // Notes-: Aliases and shortcuts are optional.
    // // 2. Alias the Concrete Class to the Interface
    // 'Core\List\ListFactory' => \DI\get('Core\List\ListFactoryInterface'),
    // // 3. Provide a Shortcut for Convenience
    // 'listFactory' => \DI\get('Core\List\ListFactoryInterface'),
    // //----------------------------------------------------------------------

    ##########################################################################

    //foofee
    // Important!!! - Lesson: This uses DI\autowire to automatically inject dependencies into FormFactory.
    // Important!!! - Constructor parameters are overridden explicitly where needed.
    FormFactoryInterface::class => \DI\autowire(\Core\Form\FormFactory::class)
        ->constructorParameter('csrf', \DI\get('Core\Form\CSRF\CSRFToken'))
        ->constructorParameter('fieldTypeRegistry', \DI\get('Core\Form\Field\Type\FieldTypeRegistry'))
        // ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('formRendererRegistry', \DI\get('Core\Form\Renderer\FormRendererRegistry'))
        ->constructorParameter('validator', \DI\get('Core\Form\Validation\Validator')),


    // // Notes-: Aliases and shortcuts are optional.
    // 'formFactory' => DI\get(FormFactoryInterface::class),



    // // Field registries
    // FieldRegistryInterface::class => DI\get(UserFieldRegistry::class),
    // UserFieldRegistry::class => DI\create(UserFieldRegistry::class),

    // // Form types
    // UserEditFormType::class => DI\factory(function ($c, $params = []) {
    //     return new UserEditFormType(
    //         $c->get(UserFieldRegistry::class),
    //         $params['config'] ?? []
    //     );
    // }),


    // Form Renderers
    'form.renderer.bootstrap' => \DI\factory(function () {
        return new \Core\Form\Renderer\BootstrapFormRenderer();
    }),
    'list.renderer.bootstrap' => \DI\factory(function () {
        return new \Core\List\Renderer\BootstrapListRenderer();
    }),

    // Notes-: This is called 'factory closure'
    // Renderer Registry
    // \Core\Form\Renderer\FormRendererXRegistry::class => \DI\factory(function (ContainerInterface $c) {
    //     $registry = new \Core\Form\Renderer\FormRendererXRegistry();
    //     $registry->register('bootstrap', $c->get('form.renderer.bootstrap'));

    //     // Set default renderer based on environment setting
    //     $defaultRenderer = $_ENV['FORM_CSS_FRAMEWORK'] ?? 'bootstrap';
    //     $registry->setDefaultRenderer($defaultRenderer);

    //     return $registry;
    // }),

    // Notes-: This is called 'factory closure'
    \Core\Form\Renderer\FormRendererRegistry::class => \DI\factory(function (ContainerInterface $c) {
        $registry = new \Core\Form\Renderer\FormRendererRegistry();
        $registry->register('bootstrap', $c->get('form.renderer.bootstrap'));
        $defaultRenderer = $_ENV['FORM_CSS_FRAMEWORK'] ?? 'bootstrap';
        $registry->setDefaultRenderer($defaultRenderer);
        return $registry;
    }),

    \Core\List\Renderer\ListRendererRegistry::class => \DI\factory(function (ContainerInterface $c) {
        $registry = new \Core\List\Renderer\ListRendererRegistry();
        $registry->register('bootstrap', $c->get('list.renderer.bootstrap'));
        $defaultRenderer = $_ENV['LIST_CSS_FRAMEWORK'] ?? 'bootstrap';
        $registry->setDefaultRenderer($defaultRenderer);
        return $registry;
    }),
    // More services...






    'App\Features\Testys\TestysController' => \DI\autowire()
        // ->constructorParameter('route_params', \DI\get('route_params'))
        // ->constructorParameter('flash22', \DI\get('flash'))
        // ->constructorParameter('view', \DI\get('view'))
        // ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        // ->constructorParameter('container', \DI\get(ContainerInterface::class))
        //     /////////////
        // ->constructorParameter('config', \DI\get('config'))
        // ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        // ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        // ->constructorParameter('repository', \DI\get('App\Repository\TestyRepositoryInterface'))
        // ->constructorParameter('formType', \DI\get('App\Features\Testys\Form\TestysFormType'))
        // ->constructorParameter('listFactory', \DI\get(ListFactoryInterface::class))
        // ->constructorParameter('listType', \DI\get('App\Features\Testys\List\TestysListType'))
        //     /////////////
        // ->constructorParameter('logger', \DI\get('logger'))
        // ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService')),





];
// 1435 1395
