<?php

declare(strict_types=1);

use App\Features\Testy\TestyRepository;
use App\Features\Testy\TestyRepositoryInterface;
use App\Features\Image\ImageRepository;
use App\Features\Image\ImageRepositoryInterface;
use App\Features\Gallery\GalleryRepository;
use App\Features\Gallery\GalleryRepositoryInterface;
use App\Features\Image\ImageService;
use App\Features\User\UserRepository;
use App\Features\User\UserRepositoryInterface;
use App\Helpers\DebugRt;
use App\Repository\AlbumRepository;
use App\Repository\AlbumRepositoryInterface;
use Core\Database\ConnectionInterface;
use App\Repository\PostRepositoryInterface;
use App\Repository\RepositoryRegistry;
use App\Repository\RepositoryRegistryInterface;
use App\Features\Store\StoreRepositoryInterface;
use App\Features\Store\StoreRepository;
use App\Features\User\UserService;
// use App\Repository\UserRepositoryInterface;
use App\Services\FeatureMetadataService;
use App\Services\FlashMessageService;
use App\Services\GenericDataService;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\Interfaces\GenericDataServiceInterface;
use Core\Console\Commands\CleanupTempUploadsCommand;
use Core\Console\Generators\ConfigViewGenerator;
use Core\Console\Generators\GeneratorOutputService;
use Core\Console\Generators\MigrationGenerator;
use Core\Console\Generators\SeederGenerator;
use Core\Constants\Consts;
use Core\Context\CurrentContext;
use Core\Database\Seeders\SeederRunnerService;
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
use Core\Form\Schema\FieldSchema;
use Core\Form\Upload\FileUploadServiceInterface;
use Core\Form\Upload\TemporaryFileUploadService;
use Core\Form\Validation\ValidatorRegistry;
use Core\I18n\I18nTranslator;
use Core\Interfaces\ConfigInterface;
use Core\List\ListFactoryInterface;
use Core\Middleware\RoutingMiddleware;
use Core\RouterInterface;
use Core\Services\DataNormalizerService;
use Core\Services\FormatterService;
use Core\Services\ImageStorageServiceInterface;
use Core\Services\SchemaLoaderService;
use Core\Services\ThemeServiceInterface;
use Core\Storage\LocalStorageService;
use Psr\Log\LoggerInterface;

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



    // Register GeoLocationService
    'App\Services\GeoLocationService' => \DI\autowire(),




    'Core\View' => \DI\get('view'),

    //-----------------------------------------------------------------


    // Storage provider (local disk)
    // Core\Storage\StorageProviderInterface::class => \DI\autowire(\Core\Storage\LocalStorageService::class)
    //     ->constructorParameter('basePath', __DIR__ . '/../src/public_html/uploads')
    //     ->constructorParameter('baseUrl', '/uploads'),
    // ✅ Storage Provider (Local Filesystem)
    Core\Storage\StorageProviderInterface::class => DI\factory(function (ContainerInterface $container) {
        // Load config from environment or ConfigService
        $config = $container->get('config');

        $basePath = $config->get('storage.local.base_path', __DIR__ . '/../public_html/uploads');
        $baseUrl = $config->get('storage.local.base_url', '/uploads');

        return new LocalStorageService($basePath, $baseUrl);
    }),


    // ✅ Image Processing Service (handles resize, crop, compress)
    \Core\Services\ImageProcessingService::class => \DI\autowire()
        ->constructorParameter('logger', \DI\get(LoggerInterface::class)),


    // // High-level file upload service (validates and delegates to StorageProvider)
    // // ✅ File Upload Service
    // Core\Form\Upload\FileUploadServiceInterface::class => \DI\autowire(\Core\Form\Upload\FileUploadService::class)
    //     ->constructorParameter('storage', \DI\get(Core\Storage\StorageProviderInterface::class))
    //     ->constructorParameter('logger', \DI\get(LoggerInterface::class))
    //     ->constructorParameter('defaultMaxSize', \DI\factory(function (ContainerInterface $container) {
    //         $config = $container->get('config');
    //         return $config->get('storage.upload.default_max_size', 5242880); // Default: 5MB
    //     })),

    // Temporary File Upload Service (for generic file handling by FormHandler)
    FileUploadServiceInterface::class => \DI\autowire(TemporaryFileUploadService::class)
        ->constructorParameter('logger', \DI\get(LoggerInterface::class))
        ->constructorParameter('tempUploadDir', \DI\string(dirname(__DIR__) . '/storage/temp_uploads')), // ✅ Configure your temporary upload directory here


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


    // -----------------------------------------------------------------
    // Filesystem Configuration (Ensure these paths are correct for your setup)
    // -----------------------------------------------------------------
    // -----------------------------------------------------------------
    // NEW: ImageStorageService (interface and concrete)
    // -----------------------------------------------------------------
    \Core\Services\ImageStorageServiceInterface::class => \DI\autowire(\Core\Services\ImageStorageService::class)
        ->constructorParameter('configService', \DI\get(\Core\Interfaces\ConfigInterface::class))
        ->constructorParameter('logger', \DI\get(\Psr\Log\LoggerInterface::class))
        ->constructorParameter('imageProcessingService', \DI\get(\Core\Services\ImageProcessingService::class))
        ->constructorParameter('publicHtmlRoot', \DI\get('app.public_path'))
        ->constructorParameter('storageRoot', \DI\get('app.storage_path')),




    // ✅ Return URL Manager Service
    \Core\Services\ReturnUrlManagerServiceInterface::class => \DI\autowire(\Core\Services\ReturnUrlManagerService::class)
        ->constructorParameter('sessionManager', \DI\get(\Core\Session\SessionManagerInterface::class)),

    // Convenience alias
    'returnUrlManager' => \DI\get(\Core\Services\ReturnUrlManagerServiceInterface::class),

    // ✅ URL Generator Service
    \Core\Services\UrlGeneratorServiceInterface::class => \DI\autowire(\Core\Services\UrlGeneratorService::class)
        ->constructorParameter('currentContext', \DI\get(\Core\Context\CurrentContext::class)),

    // Convenience alias
    'urlGenerator' => \DI\get(\Core\Services\UrlGeneratorServiceInterface::class),




    // //------------------------------------------------------------------------
    // // EXAMPLE - Not using Aliases / Shortcuts
    // // This defines the concrete implementation
    // FlashMessageService::class => \DI\autowire()
    //     ->constructorParameter('sessionManager', \DI\get('sessionManager')),

    // // This binds the interface to the concrete class
    // FlashMessageServiceInterface::class => \DI\get(FlashMessageService::class),
    // //------------------------------------------------------------------------


    // Register GeoLocationMiddleware with dependencies injected
    'Core\Middleware\GeoLocationMiddleware' => \DI\autowire()
        ->constructorParameter('geoLocationService', \DI\get('App\Services\GeoLocationService'))
        ->constructorParameter('sessionManager', \DI\get('Core\Session\SessionManagerInterface')),






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
        ->constructorParameter('storeRepository', \DI\get(App\Features\Store\StoreRepositoryInterface::class))
        ->constructorParameter('context', \DI\get('Core\Context\CurrentContext')),



    // Register BootstrapNavigationRendererService for navigation rendering
    'Core\Navigation\Bootstrap\BootstrapNavigationRendererService' => \DI\autowire()
        ->constructorParameter('themeService', \DI\get('Core\Services\ThemeServiceInterface'))
        ->constructorParameter('translator', \DI\get('Core\I18n\I18nTranslator')),

    // Optionally, bind the interface to the Bootstrap implementation as the default
    'Core\Navigation\NavigationRendererInterface' =>
                                                \DI\get('Core\Navigation\Bootstrap\BootstrapNavigationRendererService'),
    //-----------------------------------------------------------------


    /// Dynamic-me 2
    // --- Content Type Management ---
    'App\Services\Interfaces\ContentTypeRegistryInterface' => \DI\autowire('App\Services\ContentTypeRegistry'),
    'App\Services\ContentTypeRegistry' => \DI\autowire(), // Autowiring should inject ConfigInterface

    /// Dynamic-me 2
    // --- Generic CRUD Components ---
    'App\Features\GenericCrud\GenericCrudController' => \DI\autowire(),
    'App\Features\Generic\Form\GenericFormType' => \DI\autowire(),
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

    // featureFoo //dangerdanger
    // 'App\Repository\UserRepositoryInterface' => \DI\autowire(App\Repository\UserRepository::class),
    //UserRepositoryInterface::class => \DI\autowire(UserRepository::class),

    'App\Repository\RememberTokenRepositoryInterface' => DI\autowire(App\Repository\RememberTokenRepository::class)
        ->constructorParameter('connection', DI\get('Core\Database\ConnectionInterface')),

    // 'Core\Auth\AuthenticationServiceInterface' => function (ContainerInterface $c) {
    //     return $c->get('Core\Auth\SessionAuthenticationService');
    // },

    'Core\Auth\SessionAuthenticationService' => DI\autowire()
        // featureFoo //dangerdanger
        //->constructorParameter('userRepository', DI\get(App\Repository\UserRepositoryInterface::class))
        ->constructorParameter('userRepository', DI\get(UserRepositoryInterface::class))
        ->constructorParameter('session', DI\get(Core\Session\SessionManagerInterface::class))
        ->constructorParameter(
            'rememberTokenRepository',
            DI\get(App\Repository\RememberTokenRepositoryInterface::class)
        )
        ->constructorParameter('config', DI\get('config'))
        ->constructorParameter(
            'storeRepository',
            DI\get(App\Features\Store\StoreRepositoryInterface::class) // Corrected namespace
        ),
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

    // 'Core\Auth\SessionAuthenticationService' => DI\autowire()
    //     ->constructorParameter('userRepository', DI\get(UserRepositoryInterface::class))
    //     ->constructorParameter('session', DI\get(Core\Session\SessionManagerInterface::class))
    //     ->constructorParameter(
    //         'rememberTokenRepository',
    //         DI\get(App\Repository\RememberTokenRepositoryInterface::class)
    //     )
    //     ->constructorParameter('config', DI\get('config'))
    //     ->constructorParameter(
    //         'storeRepository',
    //         DI\get(App\Repository\StoreRepositoryInterface::class)
    //     ),
        // foofee
        // ->constructorParameter('bruteForceProtection', DI\get('Core\Security\BruteForceProtectionService')),

    // Add this after the RememberTokenRepository registration
    // 'App\Repository\LoginAttemptsRepositoryInterface' => DI\autowire(App\Repository\LoginAttemptsRepository::class)
    //     ->constructorParameter('connection', DI\get('Core\Database\ConnectionInterface')),

    //-----------------------------------------------------------------
    // SECTION: UrlService (Ensure these are present as per previous steps)
    //-----------------------------------------------------------------

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


    //-----------------------------------------------------------------
    // SECTION: CodeLookupService - Centralized Code-to-Label Mappings
    //-----------------------------------------------------------------

    /**
     * CodeLookupService - Single generic service for all code lookups
     * (gender, payment_type, status, etc.)
     *
     * Dependencies:
     * - ConfigInterface: Loads src/Config/app_lookups.php
     * - TranslatorInterface: Translates label keys to localized strings
     *
     * @see src/Config/app_lookups.php for centralized code definitions
     * @see src/Core/Services/CodeLookupService.php for implementation
     */
    \Core\Interfaces\CodeLookupServiceInterface::class => \DI\autowire(\Core\Services\CodeLookupService::class)
        ->constructorParameter('configService', \DI\get(\Core\Interfaces\ConfigInterface::class)),
        // ->constructorParameter('translator', \DI\get(\Core\I18n\TranslatorInterface::class)),

    // Convenience alias for easier access
    'codeLookup' => \DI\get(\Core\Interfaces\CodeLookupServiceInterface::class),

    //-----------------------------------------------------------------
    // END SECTION: CodeLookupService
    //-----------------------------------------------------------------



    'Core\I18n\TranslationLoaderService' => \DI\autowire()
        ->constructorParameter('languageDir', __DIR__ . '/../lang')
        ->constructorParameter('currentLocale', $_ENV['APP_LOCALE'] ?? 'en'),

    'Core\I18n\I18nTranslator' => function (ContainerInterface $c) {
        $loader = $c->get('Core\I18n\TranslationLoaderService');
        return new \Core\I18n\I18nTranslator(
            $loader->loadTranslations(),
            '_'
        );
    },

    // ✅ NEW: Bind the interface to the concrete implementation
    \Core\I18n\TranslatorInterface::class => \DI\get('Core\I18n\I18nTranslator'),

    // 'Core\I18n\I18nTranslator' => function (ContainerInterface $c) {
    //     // ✅ Load translations from files
    //     $labels = [];
    //     $langDir = __DIR__ . '/../lang/en/';

    //     foreach (glob($langDir . '*.php') as $file) {
    //         $namespace = basename($file, '_lang.php');
    //         $labels[$namespace] = require $file;
    //     }

    //     return new \Core\I18n\I18nTranslator($labels, '_'); // ✅ Pass delimiter
    // },
    // 'Core\I18n\I18nTranslator' => function (ContainerInterface $c) {
    //     $logger = $c->get(LoggerInterface::class);

    //     $langPath = dirname(__DIR__) . '/lang/en/';


    //     $testyPath = $langPath . 'testy_lang.php';
    //     $testy = file_exists($testyPath) ? include $testyPath : [];
    //     if (empty($testy) && !file_exists($testyPath)) {
    //         $logger->warning("Language file not found: {$testyPath}. Using empty array.");
    //     }

    //     $galleryPath = $langPath . 'gallery_lang.php';
    //     $gallery = file_exists($galleryPath) ? include $galleryPath : [];
    //     if (empty($gallery) && !file_exists($galleryPath)) {
    //         $logger->warning("Language file not found: {$galleryPath}. Using empty array.");
    //     }

    //     $userPath = $langPath . 'user_lang.php';
    //     $user = file_exists($userPath) ? include $userPath : [];
    //     if (empty($user) && !file_exists($userPath)) {
    //         $logger->warning("Language file not found: {$userPath}. Using empty array.");
    //     }

    //     $commonPath = $langPath . 'common_lang.php';
    //     $common = file_exists($commonPath) ? include $commonPath : [];
    //     if (empty($common) && !file_exists($commonPath)) {
    //         $logger->warning("Language file not found: {$commonPath}. Using empty array.");
    //     }

    //     // Collect all loaded language arrays into a single structure for the I18nTranslator
    //     $labels = [
    //         'testy' => $testy,
    //         'gallery' => $gallery,
    //         'user' => $user,
    //         'common' => $common,
    //     ];
    //     return new \Core\I18n\I18nTranslator($labels);
    // },




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




    \Core\Services\DataTransformerService::class => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get(\Core\Services\FieldRegistryService::class)),


    // Base Feature Service (provides common utilities like data transformation)
    \Core\Services\BaseFeatureService::class => \DI\autowire()
        ->constructorParameter('dataTransformerService', \DI\get(\Core\Services\DataTransformerService::class)),



    // UserService
    UserService::class => \DI\autowire()
        ->constructorParameter('userRepository', \DI\get(UserRepositoryInterface::class))
        ->constructorParameter('dataTransformer', \DI\get(\Core\Services\DataTransformerService::class)),

    // UserValidationService
    'App\Services\UserValidationService' => \DI\autowire(),

    // ImageService
    ImageService::class => \DI\autowire()
        ->constructorParameter('imageRepository', \DI\get(ImageRepositoryInterface::class))
        ->constructorParameter('imageStorageService', \DI\get(ImageStorageServiceInterface::class)) // ✅ NEW
        ->constructorParameter('logger', \DI\get(LoggerInterface::class)), // ✅ NEW




    // Add the StoreContext registration:
    // StoreContext service
    'App\Services\StoreContext' => \DI\autowire()
        ->constructorParameter('session', \DI\get(Core\Session\SessionManagerInterface::class))
        ->constructorParameter('storeRepository', \DI\get(App\Features\Store\StoreRepositoryInterface::class))
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
            ->constructorParameter('userService', \DI\get(UserService::class))
            ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService')),

    // fffffffffffffffffffffffffffffffffffffffffffffffffffffffff
    // fffffffffffffffffffffffffffffffffffffffffffffffffffffffff
    // fffffffffffffffffffffffffffffffffffffffffffffffffffffffff
    // fffffffffffffffffffffffffffffffffffffffffffffffffffffffff

    // Add this to your existing container definitions


    // ThemeService registrations
    'Core\Services\ThemeServiceInterface' => \DI\autowire('Core\Services\BootstrapThemeService'),
    'Core\Services\BootstrapThemeService' => \DI\autowire(),
    'Core\Services\MaterialThemeService' => \DI\autowire(),
    'Core\Services\VanillaThemeService' => \DI\autowire(),


    // Theme Asset Service
    'Core\Services\ThemeAssetService' => \DI\autowire()
        ->constructorParameter('config', \DI\get(\Core\Interfaces\ConfigInterface::class))
        ->constructorParameter('themeManager', \DI\get(\Core\Services\ThemeConfigurationManagerService::class)),


    // Theme Configuration Manager
    'Core\Services\ThemeConfigurationManagerService' => \DI\factory(function (ContainerInterface $c) {
        $manager = new \Core\Services\ThemeConfigurationManagerService(
            $c->get('Core\Interfaces\ConfigInterface'),
            $c->get(\Core\Session\SessionManagerInterface::class),
            $_ENV['DEFAULT_THEME'] ?? 'bootstrap'
        );

        // Register all theme services
        $manager->registerThemeService('bootstrap', $c->get(\Core\Services\BootstrapThemeService::class))
            ->registerThemeService('material', $c->get(\Core\Services\MaterialThemeService::class))
            ->registerThemeService('vanilla', $c->get(\Core\Services\VanillaThemeService::class));

        // Load configuration from config files
        $manager->loadThemeConfiguration();
        // $manager->setActiveVariant('christmas');

        return $manager;
    }),

    // Theme Preview Service
    'Core\Services\ThemePreviewService' => \DI\autowire()
        ->constructorParameter('sessionManager', \DI\get(\Core\Session\SessionManagerInterface::class))
        ->constructorParameter('themeManager', \DI\get(\Core\Services\ThemeConfigurationManagerService::class)),

    // Convenience alias
    'theme.manager' => \DI\get('Core\Services\ThemeConfigurationManagerService'),






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


    // // Login field registry with inheritance
    // 'App\Features\Auth\Field\LoginFieldRegistry' => \DI\autowire()
    //     ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface')),
    //     // ->constructorParameter('config', \DI\get('config'))
    //     // ->constructorParameter('baseRegistry', \DI\get('Core\Registry\BaseFieldRegistry')),

    // //-----------------------------------------------------------------

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
        ->constructorParameter('userRepository', \DI\get(UserRepositoryInterface::class))
        ->constructorParameter('loginAttemptsRepository', \DI\get('App\Repository\LoginAttemptsRepositoryInterface'))
        ->constructorParameter('rememberTokenRepository', \DI\get('App\Repository\RememberTokenRepositoryInterface'))
        ->constructorParameter('session', \DI\get('Core\Session\SessionManagerInterface')),


    // 'App\Features\Auth\LoginController' => \DI\autowire()
    'App\Features\Login\LoginController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('scrap', \DI\get(CurrentContext::class))
        ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        ->constructorParameter('formType', \DI\get('Core\Form\ZzzzFormType'))
        ->constructorParameter('authService', \DI\get('Core\Auth\AuthenticationServiceInterface'))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),




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
        ->constructorParameter('config', \DI\get('config'))
        ->constructorParameter('themeManager', \DI\get('Core\Services\ThemeConfigurationManagerService')),

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
        ->constructorParameter('userRepository', \DI\get(UserRepositoryInterface::class))
        ->constructorParameter('emailNotificationService', \DI\get('App\Services\Email\EmailNotificationService'))
        ->constructorParameter('logger', \DI\get('logger')),



    'Core\Errors\ErrorsController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
         ->constructorParameter('flash', \DI\get('flash'))
         ->constructorParameter('view', \DI\get('view'))
         ->constructorParameter('httpFactory', \DI\get('httpFactory')),


    'App\Features\About\AboutController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),



    // Dynamic-me 3
    // Update GenericController definition to use the GenericDataService
    'App\Features\Admin\Generic\GenericController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))

        ->constructorParameter('listFactory', \DI\get('Core\List\ListFactoryInterface'))
        ->constructorParameter('genericListType', \DI\get('App\Features\Admin\Generic\List\GenericListType'))
        // Inject the GenericDataService instead of the specific repository
        ->constructorParameter('dataService', \DI\get(GenericDataServiceInterface::class))
        // ->constructorParameter('config', \DI\get('Core\Interfaces\ConfigInterface')),
        // ->constructorParameter('postRepository', \DI\get('App\Repository\PostRepositoryInterface')),
        ->constructorParameter('columnRegistry', \DI\get('App\Features\Admin\Generic\List\GenericColumnRegistry'))
        ->constructorParameter('postRepository', \DI\get('App\Repository\PostRepositoryInterface')),




    'App\Features\Store\Dashboard\DashboardController' => \DI\autowire()
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
    'App\Features\Store\StoresController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('formFactory', \DI\get(FormFactoryInterface::class))
        ->constructorParameter('formHandler', \DI\get(FormHandlerInterface::class))
        ->constructorParameter('storeRepository', \DI\get(App\Features\Store\StoreRepositoryInterface::class))
        ->constructorParameter('storesFormType', \DI\get('App\Features\Store\Form\StoresFormType')),




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
        ->constructorParameter('formType', \DI\get('App\Features\Post\Form\PostFormType'))
        ->constructorParameter('listFactory', \DI\get('Core\List\ListFactory'))
        ->constructorParameter('listType', \DI\get('App\Features\Post\List\PostListType')),



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
            'testy' => TestyRepositoryInterface::class, // Map 'testy' string to the repo service ID/interface
            'image' => ImageRepositoryInterface::class, // Map 'image' string to the repo service ID/interface
            'gallery' => GalleryRepositoryInterface::class, // Map 'gallery' string to the repo service ID/interface
            'post' => PostRepositoryInterface::class, // Map 'post' string to the repo service ID/interface
            'store' => StoreRepositoryInterface::class, // Ex: Map 'user' string to the User repo service ID/interface
            'user' => UserRepositoryInterface::class, // Ex: Map 'user' string to the User repo service ID/interface
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
        //..->constructorParameter('baseRegistry', null),
        // Or inject BaseFieldRegistry if needed: \DI\get('Core\Form\BaseFieldRegistry')

    // You might want an interface alias if you plan to swap implementations later
    // 'App\Features\Admin\Generic\Form\GenericFieldRegistryInterface'
    //                                           => \DI\get('App\Features\Admin\Generic\Form\GenericFieldRegistry'),









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

    // Database connection ccc
    'Core\Database\ConnectionInterface' => function (ContainerInterface $c) {
        $config = $c->get('config.database');
        $connectionConfig = $config['connections'][$config['default']];
        $configService = $c->get(\Core\Interfaces\ConfigInterface::class);
        $logger = null;

        if ($config['logging']['enabled'] ?? false) {
            $logger = $c->get('Psr\Log\LoggerInterface');
        }

        return new \Core\Database\Connection($connectionConfig, $configService, $logger);
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

    // Core\Form\Schema\FieldSchema::class => DI\create()
    //     ->constructor(function (Core\Interfaces\ConfigInterface $config) {
    //         return $config->get('forms/schema'); // Loads all 26 field types
    //     }),


    'forms.schema' => \DI\factory(function (ContainerInterface $c) {
        // This is the ideal way to load your schema configuration
        $config = $c->get('config')->get('forms/schema');

        // ✅ DEBUG: Check how many field types are in FieldSchema
        // $rr = count($config);
        // DebugRt::j('1', 'Count: ',  $rr);

        return new FieldSchema($config);
        //"Class "Core\Form\Schema\FieldSchema" not found"
    }),

    FieldSchema::class => \DI\get('forms.schema'),



    // Field Types
    'field.type.text' => \DI\autowire(\Core\Form\Field\Type\TextType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.password' => \DI\autowire(\Core\Form\Field\Type\PasswordType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.email' => \DI\autowire(\Core\Form\Field\Type\EmailType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.url' => \DI\autowire(\Core\Form\Field\Type\UrlType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.tel' => \DI\autowire(\Core\Form\Field\Type\TelType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.search' => \DI\autowire(\Core\Form\Field\Type\SearchType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.date' => \DI\autowire(\Core\Form\Field\Type\DateType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.datetime' => \DI\autowire(\Core\Form\Field\Type\DatetimeType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.month' => \DI\autowire(\Core\Form\Field\Type\MonthType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.week' => \DI\autowire(\Core\Form\Field\Type\WeekType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.time' => \DI\autowire(\Core\Form\Field\Type\TimeType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.number' => \DI\autowire(\Core\Form\Field\Type\NumberType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.decimal' => \DI\autowire(\Core\Form\Field\Type\DecimalType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.range' => \DI\autowire(\Core\Form\Field\Type\RangeType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),



    'field.type.display' => \DI\autowire(\Core\Form\Field\Type\DisplayType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.color' => \DI\autowire(\Core\Form\Field\Type\ColorType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.file' => \DI\autowire(\Core\Form\Field\Type\FileType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.select' => \DI\autowire(\Core\Form\Field\Type\SelectType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.hidden' => \DI\autowire(\Core\Form\Field\Type\HiddenType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema'))
        ->constructorParameter('logger', \DI\get('logger')),

    'field.type.checkbox_group' => \DI\autowire(\Core\Form\Field\Type\CheckboxGroupType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.checkbox' => \DI\autowire(\Core\Form\Field\Type\CheckboxType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.radio_group' => \DI\autowire(\Core\Form\Field\Type\RadioGroupType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.radio' => \DI\autowire(\Core\Form\Field\Type\RadioType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),

    'field.type.textarea' => \DI\autowire(\Core\Form\Field\Type\TextareaType::class)
        ->constructorParameter('fieldSchema', \DI\get('forms.schema')),


    // 'field.type.checkbox' => function () {
    //     return new \Core\Form\Field\Type\CheckboxType();
    // },

    // Field Type Registry
    \Core\Form\Field\Type\FieldTypeRegistry::class => \DI\factory(function (ContainerInterface $c) {
        $registry = new \Core\Form\Field\Type\FieldTypeRegistry([
            $c->get('field.type.hidden'),
            $c->get('field.type.text'),
            $c->get('field.type.password'),
            $c->get('field.type.email'),
            $c->get('field.type.tel'),
            $c->get('field.type.url'),
            $c->get('field.type.search'),

            $c->get('field.type.date'),
            $c->get('field.type.datetime'),
            $c->get('field.type.month'),
            $c->get('field.type.week'),
            $c->get('field.type.time'),

            $c->get('field.type.number'),
            $c->get('field.type.decimal'),
            $c->get('field.type.range'),

            $c->get('field.type.file'),
            $c->get('field.type.color'),

            $c->get('field.type.textarea'),
            $c->get('field.type.select'),
            $c->get('field.type.display'),
            $c->get('field.type.checkbox_group'),
            $c->get('field.type.checkbox'),
            $c->get('field.type.radio_group'),
            $c->get('field.type.radio'),
            $c->get('field.type.captcha')
        ]);
        return $registry;
    }),




    // Static Single-Field Validators
    'validator.required' => function () {
        return new \Core\Form\Validation\Rules\RequiredValidator();
    },

    'validator.textarea' => \DI\autowire(\Core\Form\Validation\Rules\TextAreaValidator::class),
    'validator.text'     => \DI\autowire(\Core\Form\Validation\Rules\TextValidator::class),
    'validator.password' => \DI\autowire(\Core\Form\Validation\Rules\PasswordValidator::class),
    'validator.email'    => \DI\autowire(\Core\Form\Validation\Rules\EmailValidator::class),
    'validator.url'      => \DI\autowire(\Core\Form\Validation\Rules\UrlValidator::class),
    'validator.tel'    => \DI\autowire(\Core\Form\Validation\Rules\TelValidator::class),
    'validator.search'   => \DI\autowire(\Core\Form\Validation\Rules\SearchValidator::class),

    'validator.date' => \DI\autowire(\Core\Form\Validation\Rules\DateValidator::class),
    'validator.datetime' => \DI\autowire(\Core\Form\Validation\Rules\DateTimeValidator::class),
    'validator.month' => \DI\autowire(\Core\Form\Validation\Rules\MonthValidator::class),
    'validator.week' => \DI\autowire(\Core\Form\Validation\Rules\WeekValidator::class),
    'validator.time' => \DI\autowire(\Core\Form\Validation\Rules\TimeValidator::class),

    'validator.number' => \DI\autowire(\Core\Form\Validation\Rules\NumberValidator::class),
    'validator.decimal' => \DI\autowire(\Core\Form\Validation\Rules\DecimalValidator::class),
    'validator.currency' => \DI\autowire(\Core\Form\Validation\Rules\CurrencyValidator::class),
    'validator.range' => \DI\autowire(\Core\Form\Validation\Rules\RangeValidator::class),
    'validator.forbidden_words' => \DI\autowire(\Core\Form\Validation\Rules\ForbiddenWordsValidator::class),


    'validator.color' => \DI\autowire(\Core\Form\Validation\Rules\ColorValidator::class),

    'validator.checkbox_group' => \DI\autowire(\Core\Form\Validation\Rules\CheckboxGroupValidator::class),
    'validator.checkbox'       => \DI\autowire(\Core\Form\Validation\Rules\CheckboxValidator::class),

    'validator.radio_group' => \DI\autowire(\Core\Form\Validation\Rules\RadioGroupValidator::class),
    'validator.radio' => \DI\autowire(\Core\Form\Validation\Rules\RadioValidator::class),
    'validator.file' => \DI\autowire(\Core\Form\Validation\Rules\FileValidator::class),

    'validator.extratest' => \DI\autowire(\Core\Form\Validation\Rules\ExtraTestValidator::class),
    'validator.extratest2' => \DI\autowire(\Core\Form\Validation\Rules\ExtraTest2Validator::class),
    'validator.select'     => \DI\autowire(\Core\Form\Validation\Rules\SelectValidator::class),

    'validator.regex' => function () {
        return new \Core\Form\Validation\Rules\RegexValidator();
    },

    // custom Single-Field Validators too, but with external content
    'validator.unique_username' => function (ContainerInterface $c) {
        return new \Core\Form\Validation\Rules\UniqueEntityValidator(
            $c->get(UserRepositoryInterface::class),
            'username',
            'This username is already taken.'
        );
    },
    'validator.unique_email' => function (ContainerInterface $c) {
        return new \Core\Form\Validation\Rules\UniqueEntityValidator(
            $c->get(UserRepositoryInterface::class),
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

            $c->get('validator.textarea'),
            $c->get('validator.text'),
            $c->get('validator.password'),
            $c->get('validator.email'),
            $c->get('validator.url'),
            $c->get('validator.tel'),
            $c->get('validator.search'),

            $c->get('validator.date'),
            $c->get('validator.datetime'),
            $c->get('validator.month'),
            $c->get('validator.week'),
            $c->get('validator.time'),

            $c->get('validator.number'),
            $c->get('validator.decimal'),
            $c->get('validator.currency'),
            $c->get('validator.range'),

            $c->get('validator.color'),

            $c->get('validator.checkbox_group'),
            $c->get('validator.checkbox'),
            $c->get('validator.radio_group'),
            $c->get('validator.radio'),
            $c->get('validator.file'),

            $c->get('validator.extratest'),
            $c->get('validator.extratest2'),
            $c->get('validator.select'),

            $c->get('validator.forbidden_words'),
            $c->get('validator.regex'),
            $c->get('validator.unique_username'),
            $c->get('validator.unique_email'),
            $c->get('validator.captcha'),
        ]);
        return $registry;
    }),

    // Main Validator
    \Core\Form\Validation\Validator::class => \DI\autowire()
        // Explicitly inject the configured ValidatorRegistry instance into the constructor.
        // Replace 'registry' with the actual parameter name used in the Validator's constructor if different.
        ->constructorParameter('registry', \DI\get(\Core\Form\Validation\ValidatorRegistry::class))
        ->constructorParameter('fieldSchema', \DI\get('forms.schema'))
        ->constructorParameter('logger', \DI\get('logger')),



    //-------------------------------------------------------------------------
    // FORMATTER STRATEGY PATTERN - PURE AUTOWIRING
    //-------------------------------------------------------------------------




    // Core Components - Pure autowiring
    'formatterz.text'      => \DI\autowire(\Core\Formatters\TextFormatter::class)
        ->constructorParameter('translator', \DI\get(I18nTranslator::class)),

    'formatterz.tel'       => \DI\autowire(\Core\Formatters\PhoneNumberFormatter::class),
    'formatterz.email'     => \DI\autowire(\Core\Formatters\EmailFormatter::class),
    // 'formatterz.image'     => \DI\autowire(\Core\Formatters\ImageFormatter::class),
    'formatterz.decimal'   => \DI\autowire(\Core\Formatters\DecimalFormatter::class),
    'formatterz.currency'  => \DI\autowire(\Core\Formatters\CurrencyFormatter::class),
    'formatterz.foo'       => \DI\autowire(\Core\Formatters\FooFormatter::class),
    'formatterz.truncate5' => \DI\autowire(\Core\Formatters\Truncate5Formatter::class),
    'formatterz.badge'     => \DI\autowire(\Core\Formatters\BadgeFormatter::class)
        ->constructorParameter('themeService', \DI\get('Core\Services\ThemeServiceInterface')),
    'formatterz.image_link'     => \DI\autowire(\Core\Formatters\ImageLinkFormatter::class)
        ->constructorParameter('themeService', \DI\get('Core\Services\ThemeServiceInterface'))
        ->constructorParameter('imageStorageService', \DI\get(\Core\Services\ImageStorageServiceInterface::class)) // ✅ NEW
        ->constructorParameter('currentContext', \DI\get(\Core\Context\CurrentContext::class)), // ✅ NEW

    // ✅ NEW: BadgeCollectionFormatter for handling arrays of values
    'formatterz.badge_collection' => \DI\autowire(\Core\Formatters\BadgeCollectionFormatter::class)
        ->constructorParameter('themeService', \DI\get('Core\Services\ThemeServiceInterface'))
        ->constructorParameter('translator', \DI\get('Core\I18n\I18nTranslator'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)) // For service-based options_providers
        ->constructorParameter('logger', \DI\get(\Psr\Log\LoggerInterface::class)),

    'formatterz.array'     => \DI\autowire(\Core\Formatters\ArrayFormatter::class),
    'formatterz.boolean' => \DI\autowire(\Core\Formatters\BooleanFormatter::class),



    'Core\Formatters\FormatterInterface'    => \DI\get('Core\Formatters\TextFormatter'),

    \Core\Formatters\FormatterRegistry::class => \DI\factory(function (ContainerInterface $c) {
        $registry = new \Core\Formatters\FormatterRegistry([
            $c->get('formatterz.text'),
            $c->get('formatterz.tel'),
            $c->get('formatterz.email'),
            // $c->get('formatterz.image'), // todo remove old imageFormatter
            $c->get('formatterz.decimal'),
            $c->get('formatterz.currency'),
            $c->get('formatterz.foo'),
            $c->get('formatterz.truncate5'),
            $c->get('formatterz.image_link'),
            $c->get('formatterz.badge'),
            $c->get('formatterz.badge_collection'),
            $c->get('formatterz.array'),
            $c->get('formatterz.boolean'),
            // ...other formatters
        ]);
        return $registry;
    }),
    // // ✅ ADD: Register in FormatterRegistry
    // \Core\Formatters\FormatterRegistry::class => \DI\autowire()
    //     ->constructor([
    //         \DI\get(\Core\Formatters\TextFormatter::class),
    //         \DI\get(\Core\Formatters\BadgeFormatter::class),
    //         \DI\get(\Core\Formatters\PhoneNumberFormatter::class),
    //         \DI\get(\Core\Formatters\BooleanFormatter::class),
    //         \DI\get(\Core\Formatters\EmailFormatter::class),
    //         \DI\get(\Core\Formatters\EnumFormatter::class),
    //         \DI\get(\Core\Formatters\ArrayFormatter::class),
    //         \DI\get(\Core\Formatters\BadgeCollectionFormatter::class),
    //         \DI\get(\Core\Formatters\ImageLinkFormatter::class), // ✅ NEW
    //     ]),



    'Core\Services\ClosureFormatterService' => \DI\autowire(),

    // 2. DEFINE THE SERVICE (SRP: Service uses the list)
    // The FormatterService is defined ONCE, and it receives the fully configured Registry.
    // Main formatter
    'Core\Services\FormatterService' => \DI\autowire()
        // Explicitly inject the configured Registry instance into the constructor.
        // (This also ensures the logger is handled by autowiring the rest)
        ->constructorParameter('registry', \DI\get('Core\Formatters\FormatterRegistry'))
        ->constructorParameter('logger', \DI\get('Psr\Log\LoggerInterface'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class)),

        // Convenience alias
    'formatterxx' => \DI\get('Core\Services\FormatterService'),





    'Core\Services\RegionContextService' => \DI\autowire(),

    'Core\Services\DataNormalizerService' => \DI\autowire(),
    'dataNormalizerService' => \DI\get('Core\Services\DataNormalizerService'),





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



    'projectRoot' => function (ContainerInterface $container) {
        // Assuming the project root is two directories up from src/dependencies.php
        return dirname(__DIR__, 1);
    },


    \Core\Services\PathResolverService::class => \DI\autowire(\Core\Services\PathResolverService::class)
        ->constructorParameter('projectRoot', \DI\get('projectRoot')),


    Core\Database\Migrations\MigrationRunner::class => \DI\autowire()
        ->constructorParameter('db', \DI\get(ConnectionInterface::class))
        ->constructorParameter('repository', \DI\get(Core\Database\Migrations\MigrationRepository::class))
        ->constructorParameter('path', \DI\factory(function (\Core\Services\PathResolverService $pathResolverService) {
            return $pathResolverService->getDatabaseMigrationsPath();
        }))
        // ->constructorParameter('pathResolverService', \DI\get(\Core\Services\PathResolverService::class)),
        ->constructorParameter('namespace', 'Database\\Migrations')
        ->constructorParameter('logger', \DI\get(LoggerInterface::class)),


    // Core Console Services
    SeederRunnerService::class => \DI\autowire(SeederRunnerService::class)
        ->constructorParameter('db', \DI\get(ConnectionInterface::class))
        ->constructorParameter('pathResolverService', \DI\get(\Core\Services\PathResolverService::class)),



    // Console Commands
    \Core\Console\Commands\HelloCommand::class => \DI\autowire(),

    \Core\Console\Commands\MigrateCommand::class => \DI\autowire(),

    \Core\Console\Commands\MigrateOneCommand::class => \DI\autowire(),

    \Core\Console\Commands\RollbackCommand::class => \DI\autowire(),


    \Core\Console\Commands\SeedCommand::class => \DI\autowire(),


    \Core\Console\Commands\MakeMigrationCommand::class => \DI\autowire()
        ->constructorParameter('migrationGenerator', \DI\get(Core\Console\Generators\MigrationGenerator::class))
        ->constructorParameter('schemaLoaderService', \DI\get(Core\Services\SchemaLoaderService::class)),

    \Core\Console\Commands\MakeSeederCommand::class => \DI\autowire()
        ->constructorParameter('seederGenerator', \DI\get(Core\Console\Generators\SeederGenerator::class))
        ->constructorParameter('schemaLoaderService', \DI\get(\Core\Services\SchemaLoaderService::class)),

    \Core\Console\Commands\MakeEntityCommand::class => \DI\autowire()
        ->constructorParameter('entityGenerator', \DI\get(\Core\Console\Generators\EntityGenerator::class))
        ->constructorParameter('schemaLoaderService', \DI\get(SchemaLoaderService::class)),

    \Core\Console\Commands\MakeRepositoryCommand::class => \DI\autowire()
        ->constructorParameter('repositoryGenerator', \DI\get(\Core\Console\Generators\RepositoryGenerator::class))
        ->constructorParameter('schemaLoaderService', \DI\get(SchemaLoaderService::class)),

    \Core\Console\Commands\MakeLangFileCommand::class => \DI\autowire()
        ->constructorParameter('langFileGenerator', \DI\get(\Core\Console\Generators\LangFileGenerator::class))
        ->constructorParameter('schemaLoaderService', \DI\get(SchemaLoaderService::class)),

    \Core\Console\Commands\MakeConfigFieldsCommand::class => \DI\autowire()
        ->constructorParameter('configFieldsGenerator', \DI\get(\Core\Console\Generators\ConfigFieldsGenerator::class))
        ->constructorParameter('schemaLoaderService', \DI\get(SchemaLoaderService::class)),

        \Core\Console\Commands\MakeConfigViewCommand::class => \DI\autowire()
        ->constructorParameter('configViewGenerator', \DI\get(\Core\Console\Generators\ConfigViewGenerator::class))
        ->constructorParameter('schemaLoaderService', \DI\get(SchemaLoaderService::class)),

    \Core\Console\Commands\FeatureMoveCommand::class => \DI\autowire()
        ->constructorParameter('pathResolverService', \DI\get(Core\Services\PathResolverService::class)),



    // Register GeneratorOutputService
    \Core\Console\Generators\GeneratorOutputService::class => \DI\autowire()
        ->constructorParameter('config', \DI\get(\Core\Interfaces\ConfigInterface::class))
        ->constructorParameter('pathResolverService', \DI\get(\Core\Services\PathResolverService::class)),


    // Register SchemaLoaderService
    \Core\Services\SchemaLoaderService::class => \DI\autowire()
        ->constructorParameter('config', \DI\get(\Core\Interfaces\ConfigInterface::class)),

    // Register MigrationGenerator
    \Core\Console\Generators\MigrationGenerator::class => \DI\autowire()
        ->constructorParameter(
            'generatorOutputService',
            \DI\get(\Core\Console\Generators\GeneratorOutputService::class)
        ),

    // Register SeederGenerator
    \Core\Console\Generators\SeederGenerator::class => \DI\autowire()
        ->constructorParameter(
            'generatorOutputService',
            \DI\get(\Core\Console\Generators\GeneratorOutputService::class)
        )
        ->constructorParameter('pathResolverService', \DI\get(\Core\Services\PathResolverService::class))
        ->constructorParameter('fakeDataGenerator', \DI\get(\Core\Console\Generators\FakeDataGenerator::class)),

    // FakeDataGenerator definition
    \Core\Console\Generators\FakeDataGenerator::class => \DI\autowire(),

    // Register RepositoryGenerator
    \Core\Console\Generators\RepositoryGenerator::class => \DI\autowire()
        ->constructorParameter(
            'generatorOutputService',
            \DI\get(\Core\Console\Generators\GeneratorOutputService::class)
        ),

    // Register EntityGenerator
    \Core\Console\Generators\EntityGenerator::class => \DI\autowire()
        ->constructorParameter(
            'generatorOutputService',
            \DI\get(\Core\Console\Generators\GeneratorOutputService::class)
        ),

    // Register LangFileGenerator
    \Core\Console\Generators\LangFileGenerator::class
                                            => \DI\autowire(\Core\Console\Generators\LangFileGenerator::class)
        ->constructorParameter(
            'generatorOutputService',
            \DI\get(\Core\Console\Generators\GeneratorOutputService::class)
        ),

    // Register ConfigFieldsGenerator
    \Core\Console\Generators\ConfigFieldsGenerator::class
                                            => \DI\autowire(\Core\Console\Generators\ConfigFieldsGenerator::class)
        ->constructorParameter(
            'generatorOutputService',
            \DI\get(\Core\Console\Generators\GeneratorOutputService::class)
        ),

    // Register ConfigViewGenerator
    ConfigViewGenerator::class => \DI\autowire(\Core\Console\Generators\ConfigViewGenerator::class)
        ->constructorParameter('generatorOutputService', \DI\get(GeneratorOutputService::class)),


    // Register FeatureGenerator
    \Core\Console\Generators\FeatureGenerator::class => \DI\autowire()
        ->constructorParameter('entityGenerator', \DI\get(\Core\Console\Generators\EntityGenerator::class))
        ->constructorParameter('repositoryGenerator', \DI\get(\Core\Console\Generators\RepositoryGenerator::class))
        ->constructorParameter('migrationGenerator', \DI\get(\Core\Console\Generators\MigrationGenerator::class))
        ->constructorParameter('seederGenerator', \DI\get(\Core\Console\Generators\SeederGenerator::class)),


    CleanupTempUploadsCommand::class => \DI\autowire()
        ->constructorParameter('logger', \DI\get(LoggerInterface::class))
        ->constructorParameter('tempUploadDir', \DI\string(dirname(__DIR__) . '/storage/temp_uploads')) // Should match the path in TemporaryFileUploadService
        ->constructorParameter('retentionHours', 24), // Default to 24 hours retention




    //-------------------------------------------------------------------------

    // // Line 192 - This will throw "Entry cannot be resolved"
    // $schemaLoader = $container->get(SchemaLoaderService::class);

    // featureFoo //dangerdanger
    // 'App\Repository\UserRepositoryInterface' => \DI\autowire(App\Repository\UserRepository::class),
    UserRepositoryInterface::class => \DI\autowire(UserRepository::class),

    // Testy the repository interface
    TestyRepositoryInterface::class =>  DI\autowire(TestyRepository::class),
    ImageRepositoryInterface::class =>  DI\autowire(ImageRepository::class),

    // Testy the concrete repository implementation
    TestyRepository::class => \DI\autowire()
        ->constructorParameter('connection', \DI\get('db')),
    ImageRepository::class => \DI\autowire()
        ->constructorParameter('connection', \DI\get('db')),

    // GalleryRepositoryInterface::class =>  DI\autowire(GalleryRepository::class),

    // GalleryRepository::class => \DI\autowire()
    //     ->constructorParameter('connection', \DI\get('db')),


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
    App\Features\Store\StoreRepositoryInterface::class => \DI\autowire(App\Features\Store\StoreRepository::class),

    // Store the concrete repository implementation
    App\Features\Store\StoreRepository::class => \DI\autowire()
        ->constructorParameter('connection', \DI\get('db')),

    //-------------------------------------------------------------------------





    // Section - Form types
    // 'App\Features\Testy\Form\ZzzzFormType' => \DI\autowire()
    'Core\Form\ZzzzFormType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('formConfigService', \DI\get(\Core\Services\FormConfigurationService::class))
        ->constructorParameter('logger', \DI\get(\Psr\Log\LoggerInterface::class))
        ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),

    // 'App\Features\Testy\Form\TestyFormType' => \DI\autowire()
    //     ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
    //     ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
    //     ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),

    // 'App\Features\Post\Form\PostFormType' => \DI\autowire()
    //     // ->constructorParameter('viewFocus2', \DI\get('viewFocus2'))
    //     ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
    //     ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
    //     ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface')),


    // 'App\Features\Auth\Form\LoginFormType' => \DI\autowire()
    //     ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
    //     ->constructorParameter('captchaService', \DI\get('Core\Security\Captcha\CaptchaServiceInterface'))
    //     ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface')),

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



    //-----------------------------------------------------------------

    //-----------------------------------------------------------------
    // SECTION: List Configuration Service
    //-----------------------------------------------------------------

    \Core\Services\ListConfigurationService::class => \DI\autowire()
        ->constructorParameter('configService', \DI\get(\Core\Interfaces\ConfigInterface::class))
        ->constructorParameter('logger', \DI\get(\Psr\Log\LoggerInterface::class)),

    //-----------------------------------------------------------------

    // Section - List types

    // 'App\Features\Testy\List\ZzzzListType' => \DI\autowire()
    'Core\List\ZzzzListType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('listConfigService', \DI\get(\Core\Services\ListConfigurationService::class))
        ->constructorParameter('logger', \DI\get(\Psr\Log\LoggerInterface::class)),

    // 'App\Features\Testy\List\TestyListType' => \DI\autowire()
    //     ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
    //     ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface')),
    //     // ->constructorParameter('pathResolverService', \DI\get(\Core\Services\PathResolverService::class)),

    'App\Features\Post\List\PostListType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface')),
        // ->constructorParameter('pathResolverService', \DI\get(\Core\Services\PathResolverService::class)),

    // Generic List Type (Depends on GenericColumnRegistry and ConfigInterface)
    'App\Features\Admin\Generic\List\GenericListType' => \DI\autowire()
        ->constructorParameter('columnRegistry', \DI\get('App\Features\Admin\Generic\List\GenericColumnRegistry'))
        ->constructorParameter('config', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('currentContext', \DI\get(CurrentContext::class)),
        // ->constructorParameter('pathResolverService', \DI\get(\Core\Services\PathResolverService::class)),


    // Section - Registry

    // Base field registry
    //..'Core\Registry\BaseFieldRegistry' => \DI\autowire(),

    // Post ListView dependencies
    'App\Features\Post\Field\PostFieldRegistry' => \DI\autowire()
        ->constructorParameter('configService', DI\get('Core\Interfaces\ConfigInterface')),
        //..->constructorParameter('baseRegistry', \DI\get('Core\Registry\BaseFieldRegistry')),

    // Albums ListView dependencies
    'App\Features\Albums\List\AlbumsColumnRegistry' => \DI\autowire(),
    'App\Features\Albums\List\AlbumsListType' => \DI\autowire(),


    //-----------------------------------------------------------------

    // TypeResolverService (lazy-loading via config and factory - instantiates only when needed)
    'Core\Services\TypeResolverService' => \DI\autowire()
        ->constructorParameter('config', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('factory', \DI\factory(function (ContainerInterface $container) {
            return function (string $className) use ($container) {
                // Instantiate with dependencies only when resolveFormType() is called (lazy)
                return $container->get($className);
            };
        })),






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



    // ✅ View Factory //viewfeat
    \Core\View\ViewFactoryInterface::class => \DI\autowire(\Core\View\ViewFactory::class),

    // ✅ View Renderer (Bootstrap)
    'view.renderer.bootstrap' => \DI\factory(function (ContainerInterface $c) {
        return new \Core\View\Renderer\BootstrapViewRenderer(
            $c->get(\Core\Services\ThemeServiceInterface::class),
            $c->get(\Core\I18n\I18nTranslator::class),
            $c->get(\Core\Services\FormatterService::class),
            $c->get(\Psr\Log\LoggerInterface::class)
        );
    }),


    // ✅ View Renderer (Material)
    'view.renderer.material' => \DI\factory(function (ContainerInterface $c) {
        return new \Core\View\Renderer\MaterialViewRenderer(
            $c->get(\Core\Services\ThemeServiceInterface::class),
            $c->get(\Core\I18n\I18nTranslator::class),
            $c->get(\Core\Services\FormatterService::class),
            $c->get(\Psr\Log\LoggerInterface::class)
        );
    }),

    // ✅ View Renderer (Vanilla)
    'view.renderer.vanilla' => \DI\factory(function (ContainerInterface $c) {
        return new \Core\View\Renderer\VanillaViewRenderer(
            $c->get(\Core\Services\ThemeServiceInterface::class),
            $c->get(\Core\I18n\I18nTranslator::class),
            $c->get(\Core\Services\FormatterService::class),
            $c->get(\Psr\Log\LoggerInterface::class)
        );
    }),






    // 1.Bind the Interface to the Implementation
    ListFactoryInterface::class => \DI\autowire(\Core\List\ListFactory::class)
        ->constructorParameter('csrfToken', \DI\get('Core\Form\CSRF\CSRFToken'))
        ->constructorParameter('fieldTypeRegistry', \DI\get('Core\Form\Field\Type\FieldTypeRegistry')),
        // ->constructorParameter('listRendererRegistry', \DI\get('Core\List\Renderer\ListRendererRegistry')),
    // //----------------------------------------------------------------------
    // // Notes-: Aliases and shortcuts are optional.
    // // 2. Alias the Concrete Class to the Interface
    // 'Core\List\ListFactory' => \DI\get('Core\List\ListFactoryInterface'),
    // // 3. Provide a Shortcut for Convenience
    // 'listFactory' => \DI\get('Core\List\ListFactoryInterface'),
    // //----------------------------------------------------------------------

    //foofee
    // Important!!! - Lesson: This uses DI\autowire to automatically inject dependencies into FormFactory.
    // Important!!! - Constructor parameters are overridden explicitly where needed.
    FormFactoryInterface::class => \DI\autowire(\Core\Form\FormFactory::class)
        ->constructorParameter('csrf', \DI\get('Core\Form\CSRF\CSRFToken'))
        ->constructorParameter('fieldTypeRegistry', \DI\get('Core\Form\Field\Type\FieldTypeRegistry'))
        // ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
        // ->constructorParameter('formRendererRegistry', \DI\get('Core\Form\Renderer\FormRendererRegistry'))
        ->constructorParameter('validator', \DI\get('Core\Form\Validation\Validator')),



    ##########################################################################




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





    // List renderer with theme service
    'Core\List\Renderer\ListRendererInterface' => function (
        \Psr\Container\ContainerInterface $container,
    ) {
        // Get the ListRendererRegistry and ask it for the currently active renderer
        return $container->get(\Core\List\Renderer\ListRendererRegistry::class)->getRenderer();
    },


    // Form renderer with theme service
    'Core\Form\Renderer\FormRendererInterface' => function (
        \Psr\Container\ContainerInterface $container
    ) {
        return $container->get(\Core\Form\Renderer\FormRendererRegistry::class)->getRenderer();
        // return new \Core\Form\Renderer\BootstrapFormRenderer(
        //     $container->get('Core\Services\ThemeServiceInterface'),
        //     $container->get(ThemeServiceInterface::class),
        //     $container->get(FormatterService::class),
        //     $container->get(LoggerInterface::class)
        // );
    },



    // // List Renderers - Keep this as it's used by the ListRendererRegistry
    // 'list.renderer.bootstrap' => \DI\factory(function (ContainerInterface $c) {
    //     return new \Core\List\Renderer\BootstrapListRenderer(
    //         $c->get('Core\Services\ThemeServiceInterface'),
    //         $c->get('Core\I18n\I18nTranslator'),
    //         $c->get('Core\Services\FormatterService'),
    //         $c->get(LoggerInterface::class)
    //     );
    // }),

    // ✅ CLEAN: Full autowiring (no manual parameter injection needed)
    'list.renderer.bootstrap' => \DI\autowire(\Core\List\Renderer\BootstrapListRenderer::class),
    'list.renderer.material'  => \DI\autowire(\Core\List\Renderer\MaterialListRenderer::class),
    'list.renderer.vanilla'   => \DI\autowire(\Core\List\Renderer\VanillaListRenderer::class),


    // // Material Design renderer
    // 'list.renderer.material' => \DI\factory(function (ContainerInterface $c) {
    //     return new \Core\List\Renderer\MaterialListRenderer(
    //         $c->get(\Core\Services\ThemeServiceInterface::class),
    //         // $c->get('Core\I18n\I18nTranslator'), // fixme we need to test with label-provider
    //         $c->get(\Core\Services\FormatterService::class),
    //         $c->get(\Psr\Log\LoggerInterface::class)
    //     );
    // }),

    // // Vanilla List Renderer
    // 'list.renderer.vanilla' => \DI\factory(function (ContainerInterface $c) {
    //     return new \Core\List\Renderer\VanillaListRenderer(
    //         $c->get(\Core\Services\ThemeServiceInterface::class),
    //         // $c->get('Core\I18n\I18nTranslator'), // fixme we need to test with label-provider
    //         $c->get(\Core\Services\FormatterService::class),
    //         $c->get(\Psr\Log\LoggerInterface::class)
    //     );
    // }),



    // Form Renderers
    'form.renderer.bootstrap' => \DI\factory(function (ContainerInterface $c) {
        return new \Core\Form\Renderer\BootstrapFormRenderer(
            $c->get('Core\Services\ThemeServiceInterface'),
            $c->get('Core\I18n\I18nTranslator'),
            $c->get('Core\Services\FormatterService'),
            $c->get(LoggerInterface::class)
        );
    }),


    // ✅ Default View Renderer Interface
    \Core\View\Renderer\ViewRendererInterface::class => \DI\factory(function (ContainerInterface $c) {
        return $c->get(\Core\View\Renderer\ViewRendererRegistry::class)->getRenderer();
    }),

    // // ✅ Generic ViewType (Zzzzz pattern)
    // 'Core\View\ZzzzViewType' => \DI\autowire(\Core\View\AbstractViewType::class) // Abstract, so use concrete impl
    //     ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
    //     ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface')),

     // SECTION: View Configuration Service
    \Core\Services\ViewConfigurationService::class => \DI\autowire()
        ->constructorParameter('configService', \DI\get(\Core\Interfaces\ConfigInterface::class))
        ->constructorParameter('logger', \DI\get(\Psr\Log\LoggerInterface::class)),
    // END SECTION: View Configuration Service

    // Section - View types
    'Core\View\ZzzzViewType' => \DI\autowire()
        ->constructorParameter('fieldRegistryService', \DI\get('Core\Services\FieldRegistryService'))
        ->constructorParameter('configService', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('viewConfigService', \DI\get(\Core\Services\ViewConfigurationService::class))
        ->constructorParameter('logger', \DI\get(\Psr\Log\LoggerInterface::class)),



    // Testy View Type
    // \Core\View\TestyViewType::class => \DI\autowire()
    //     ->constructorParameter('fieldRegistryService', \DI\get(\Core\Services\FieldRegistryService::class))
    //     ->constructorParameter('configService', \DI\get(\Core\Interfaces\ConfigInterface::class)),

    // ✅ Update the View renderer registry
    \Core\View\Renderer\ViewRendererRegistry::class => \DI\factory(function (ContainerInterface $c) {
        $registry = new \Core\View\Renderer\ViewRendererRegistry();
        $registry->register('bootstrap', $c->get('view.renderer.bootstrap'));
        $registry->register('material', $c->get('view.renderer.material'));
        $registry->register('vanilla', $c->get('view.renderer.vanilla'));

        // Get default renderer from environment or config
        $defaultRenderer = $_ENV['VIEW_CSS_FRAMEWORK'] ?? 'bootstrap';
        $registry->setDefaultRenderer($defaultRenderer);
        return $registry;
    }),




    // Notes-: This is called 'factory closure'
    // Update the renderer registry
    \Core\List\Renderer\ListRendererRegistry::class => \DI\factory(function (ContainerInterface $c) {
        $registry = new \Core\List\Renderer\ListRendererRegistry();
        $registry->register('bootstrap', $c->get('list.renderer.bootstrap'));
        $registry->register('material', $c->get('list.renderer.material'));
        $registry->register('vanilla', $c->get('list.renderer.vanilla'));

        // Get default renderer from environment or config
        $defaultRenderer = $_ENV['LIST_CSS_FRAMEWORK'] ?? 'bootstrap';
        $registry->setDefaultRenderer($defaultRenderer);
        return $registry;
    }),

    \Core\Form\Renderer\FormRendererRegistry::class => \DI\factory(function (ContainerInterface $c) {
        $registry = new \Core\Form\Renderer\FormRendererRegistry();
        $registry->register('bootstrap', $c->get('form.renderer.bootstrap'));
        // $registry->register('material', $c->get('form.renderer.material')); // todo - missing
        // $registry->register('vanilla', $c->get('form.renderer.vanilla')); // todo - missing

        // Get default renderer from environment or config
        $defaultRenderer = $_ENV['FORM_CSS_FRAMEWORK'] ?? 'bootstrap';
        $registry->setDefaultRenderer($defaultRenderer);
        return $registry;
    }),

    // More services...


    'App\Features\Theme\ThemeController' => \DI\autowire()
        ->constructorParameter('route_params', \DI\get('route_params'))
        ->constructorParameter('flash', \DI\get('flash'))
        ->constructorParameter('view', \DI\get('view'))
        ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        ->constructorParameter('container', \DI\get(ContainerInterface::class))
        ->constructorParameter('scrap', \DI\get(CurrentContext::class))
        ->constructorParameter('themeManager', \DI\get('Core\Services\ThemeConfigurationManagerService'))
        ->constructorParameter('themePreview', \DI\get('Core\Services\ThemePreviewService'))
        ->constructorParameter('themeAsset', \DI\get('Core\Services\ThemeAssetService')),

    // Register the factory service (autowire dependencies)
    'App\Services\FeatureMetadataFactoryService' => \DI\autowire()
        ->constructorParameter('config', \DI\get('Core\Interfaces\ConfigInterface'))
        ->constructorParameter('currentContext', \DI\get(Core\Context\CurrentContext::class)),

    'App\Features\Home\HomeController' => \DI\autowire(),
        // ->constructorParameter('route_params', \DI\get('route_params'))
        // ->constructorParameter('flash', \DI\get('flash'))
        // ->constructorParameter('view', \DI\get('view'))
        // ->constructorParameter('httpFactory', \DI\get('httpFactory'))
        // ->constructorParameter('container', \DI\get(ContainerInterface::class)),




    'App\Features\User\UserController' => \DI\autowire()
        ->constructorParameter(
            'featureMetadataService',
            \DI\factory(function (ContainerInterface $c) {
                // Use the factory to create the correct metadata for this feature/view
                return $c->get('App\Services\FeatureMetadataFactoryService')
                    ->createFor('user');
            })
        )
        ->constructorParameter('formType', \DI\get('Core\Form\ZzzzFormType'))
        ->constructorParameter('listType', \DI\get('Core\List\ZzzzListType'))
        ->constructorParameter('viewType', \DI\get('Core\View\ZzzzViewType'))
        ->constructorParameter('repository', \DI\get('App\Features\User\UserRepositoryInterface'))
        ->constructorParameter('listRenderer', \DI\get('Core\List\Renderer\ListRendererInterface'))
        ->constructorParameter('userService', \DI\get(UserService::class)),

    'App\Features\Testy\TestyController' => \DI\autowire()
        ->constructorParameter(
            'featureMetadataService',
            \DI\factory(function (ContainerInterface $c) {
                // Use the factory to create the correct metadata for this feature/view
                return $c->get('App\Services\FeatureMetadataFactoryService')
                    ->createFor('testy');
            })
        )
        ->constructorParameter('formType', \DI\get('Core\Form\ZzzzFormType'))
        ->constructorParameter('listType', \DI\get('Core\List\ZzzzListType'))
        ->constructorParameter('viewType', \DI\get('Core\View\ZzzzViewType')),
        // ->constructorParameter('viewType', \DI\get('Core\List\ZzzzViewType')),

    'App\Features\Image\ImageController' => \DI\autowire()
        ->constructorParameter(
            'featureMetadataService',
            \DI\factory(function (ContainerInterface $c) {
                // Use the factory to create the correct metadata for this feature/view
                return $c->get('App\Services\FeatureMetadataFactoryService')
                    ->createFor('image');
            })
        )
        ->constructorParameter('formType', \DI\get('Core\Form\ZzzzFormType'))
        ->constructorParameter('listType', \DI\get('Core\List\ZzzzListType'))
        ->constructorParameter('viewType', \DI\get('Core\View\ZzzzViewType')),
        // ->constructorParameter('imageService', \DI\get(ImageService::class)),
        // ->constructorParameter('viewType', \DI\get('Core\List\ZzzzViewType')),




    'App\Features\Gallery\GalleryController' => \DI\autowire()
        ->constructorParameter(
            'featureMetadataService',
            \DI\factory(function (ContainerInterface $c) {
                // Use the factory to create the correct metadata for this feature/view
                return $c->get('App\Services\FeatureMetadataFactoryService')
                    ->createFor('gallery');
            })
        )
        ->constructorParameter('formType', \DI\get('Core\Form\ZzzzFormType'))
        ->constructorParameter('listType', \DI\get('Core\List\ZzzzListType')),

    // dynamic-fix
    // Autowiring with a Factory Override. a hybrid approach
    'App\Features\Post\PostController' => \DI\autowire()
        ->constructorParameter(
            'featureMetadataService',
            \DI\factory(function (ContainerInterface $c) {
                // Use the factory to create the correct metadata for this feature/view
                return $c->get('App\Services\FeatureMetadataFactoryService')
                    ->createFor('post');
            })
        )
        ->constructorParameter('formType', \DI\get('Core\Form\ZzzzFormType'))
        ->constructorParameter('listType', \DI\get('Core\List\ZzzzListType'))
        ->constructorParameter('viewType', \DI\get('Core\View\ZzzzViewType'))
        ->constructorParameter('repository', \DI\get('App\Repository\PostRepositoryInterface')),

        // dynamic-fix
    // Autowiring with a Factory Override. a hybrid approach
    'App\Features\Gen\GenController' => \DI\autowire()
        ->constructorParameter(
            'featureMetadataService',
            \DI\factory(function (ContainerInterface $c) {
                // Use the factory to create the correct metadata for this feature/view
                return $c->get('App\Services\FeatureMetadataFactoryService')
                    ->createFor('post');
            })
        )
        ->constructorParameter('formType', \DI\get('Core\Form\ZzzzFormType'))
        ->constructorParameter('listType', \DI\get('Core\List\ZzzzListType'))
        ->constructorParameter('viewType', \DI\get('Core\View\ZzzzViewType'))
        ->constructorParameter('repository', \DI\get('App\Repository\PostRepositoryInterface')),

];
// 1435 1395 2119
