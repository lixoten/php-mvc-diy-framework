Total Files:
- PHP files: ~140 files
- Documentation files: ~20 files
- Configuration files: ~10 files
- CSS/HTML files: ~8 files
- Various system/project files (.gitignore, composer.json, etc.): ~12 files

MVCLIXO/ (project-root)
│
├── bin/
│   ├── a.txt
│   └── console.php                        # Command-line interface script
│
├── forms/
│   └── creation.md
│
├── logs/
│   └── (log files)
│
├── src/
│   ├── App/
│   │   ├── Entities/
│   │   │   └── User.php
│   │   │
│   │   ├── Enums/
│   │   │   └── FlashMessageType.php
│   │   │
│   │   ├── Features/
│   │   │   ├── About/
│   │   │   │   ├── AboutConst.php
│   │   │   │   ├── AboutController.php
│   │   │   │   └── Views/
│   │   │   │       └── index.php
│   │   │   │
│   │   │   ├── Admin/
│   │   │   │   └── Dashboard/
│   │   │   │       ├── DashboardConst.php
│   │   │   │       ├── DashboardController.php
│   │   │   │       └── Views/
│   │   │   │           └── index.php
│   │   │   │
│   │   │   ├── Auth/
│   │   │   │   ├── AuthConst.php
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── EmailVerificationController.php
│   │   │   │   └── Views/
│   │   │   │       └── login.php
│   │   │   │
│   │   │   ├── Home/
│   │   │   │   ├── HomeConst.php
│   │   │   │   ├── HomeController.php
│   │   │   │   └── Views/
│   │   │   │       ├── index.php
│   │   │   │       └── test.php
│   │   │   │
│   │   │   └── Testy/
│   │   │       ├── TestyConst.php
│   │   │       ├── TestyController.php
│   │   │       ├── Form/
│   │   │       │   └── ContactFormType.php
│   │   │       └── Views/
│   │   │           ├── index.php
│   │   │           └── testlogger.php
│   │   │
│   │   ├── Helpers/
│   │   │   ├── DebugRt.php
│   │   │   ├── HtmlHelper.php
│   │   │   └── UiHelper.php
│   │   │
│   │   ├── Repository/
│   │   │   ├── UserRepository.php
│   │   │   ├── UserRepositoryInterface.php
│   │   │   ├── RateLimitRepositoryInterface.php
│   │   │   ├── RememberTokenRepository.php
│   │   │   └── RememberTokenRepositoryInterface.php
│   │   │
│   │   ├── Services/
│   │   │   ├── Email/
│   │   │   │   ├── MailgunEmailService.php
│   │   │   │   └── SMTPEmailService.php
│   │   │   │
│   │   │   ├── FlashMessengerService.php
│   │   │   └── Interfaces/
│   │   │       └── FlashMessageServiceInterface.php
│   │   │
│   │   ├── ViewHelpers/
│   │   │   └── FlashMessageRendererView.php
│   │   │
│   │   └── Views/
│   │       ├── layouts/
│   │       │   ├── base5.html
│   │       │   ├── base5simple.html
│   │       │   ├── base8Error.html
│   │       │   ├── base8ErrorSimple.html
│   │       │   └── main.php
│   │       └── menu.php
│   │
│   ├── Config/
│   │   ├── app.php
│   │   ├── database.php                   # Database configuration
│   │   ├── logger.php                     # Logger configuration
│   │   ├── security.php                   # Security settings (CSRF, captcha)
│   │   └── view.php                       # View configuration
│   │
│   ├── Core/
│   │   ├── Auth/
│   │   │   └── AuthenticationServiceInterface.php
│   │   │
│   │   ├── Database/                      # Database component
│   │   │   ├── Connection.php             # Database connection class
│   │   │   ├── ConnectionInterface.php    # Connection interface
│   │   │   │
│   │   │   ├── Migrations/                # Migration system
│   │   │   │   ├── Migration.php          # Base Migration class
│   │   │   │   ├── MigrationRepository.php # Track migrations in database
│   │   │   │   └── MigrationRunner.php    # Execute migrations
│   │   │   │
│   │   │   ├── Query/                     # Query builder components
│   │   │   │   └── QueryBuilder.php       # (not implemented)
│   │   │   │
│   │   │   ├── Schema/                    # Schema definition
│   │   │   │   ├── Blueprint.php          # Table schema blueprint
│   │   │   │   ├── Column.php             # Column definition
│   │   │   │   ├── ForeignKey.php         # Foreign key definition
│   │   │   │   ├── Index.php              # Index definition
│   │   │   │   └── SchemaBuilder.php      # Create/alter tables
│   │   │   │
│   │   │   └── Seeder/                    # Database seeding
│   │   │       ├── Seeder.php             # Base seeder class
│   │   │       └── TableSeeder.php        # Table seeder
│   │   │
│   │   ├── Errors/
│   │   │   ├── ErrorsController.php
│   │   │   └── Views/
│   │   │       ├── 400.php
│   │   │       ├── 401.php
│   │   │       ├── 403.php
│   │   │       ├── 404.php
│   │   │       ├── 422.php
│   │   │       ├── 500.php
│   │   │       └── 503.php
│   │   │
│   │   ├── Exceptions/
│   │   │   ├── AuthenticationException.php
│   │   │   ├── BadRequestException.php
│   │   │   ├── ConnectionException.php     # Database connection exception
│   │   │   ├── DatabaseException.php       # General database exception
│   │   │   ├── ForbiddenException.php
│   │   │   ├── HttpException.php
│   │   │   ├── PageNotFoundException.php
│   │   │   ├── QueryException.php          # Query execution exception
│   │   │   ├── RecordNotFoundException.php
│   │   │   ├── ServerErrorException.php
│   │   │   ├── ServiceUnavailableException.php
│   │   │   ├── RecException.php
│   │   │   ├── UnauthenticatedException.php
│   │   │   └── ValidationException.php
│   │   │
│   │   ├── Form/                          # Form handling system
│   │   │   ├── CSRF/                      # CSRF protection
│   │   │   │   └── CSRFToken.php
│   │   │   │
│   │   │   ├── Field/                     # Form fields
│   │   │   │   ├── Type/
│   │   │   │   │   └── CaptchaFieldType.php
│   │   │   │   ├── Field.php
│   │   │   │   └── FieldInterface.php
│   │   │   │
│   │   │   ├── Renderer/
│   │   │   │   └── BootstrapFormRenderer.php
│   │   │   │
│   │   │   ├── Validation/                # Form validation
│   │   │   │   ├── Rules/
│   │   │   │   │   ├── CaptchaValidator.php
│   │   │   │   │   ├── EmailValidator.php
│   │   │   │   │   ├── MinLengthValidator.php
│   │   │   │   │   ├── MaxLengthValidator.php
│   │   │   │   │   ├── PatternValidator.php
│   │   │   │   │   ├── RequiredValidator.php
│   │   │   │   │   └── UrlValidator.php
│   │   │   │   ├── ValidatorInterface.php
│   │   │   │   ├── ValidatorRegistry.php
│   │   │   │   └── Validator.php
│   │   │   │
│   │   │   ├── View/
│   │   │   │   └── FormView.php
│   │   │   │
│   │   │   ├── Form.php
│   │   │   ├── FormBuilder.php            # Form generation
│   │   │   ├── FormBuilderInterface.php
│   │   │   ├── FormFactory.php
│   │   │   ├── FormFactoryInterface.php
│   │   │   ├── FormHandler.php
│   │   │   ├── FormValidator.php          # Validation rules
│   │   │   └── ValidationError.php        # Error representation
│   │   │
│   │   ├── Http/
│   │   │   ├── HttpFactory.php
│   │   │   ├── ResponseEmitter.php
│   │   │   └── ResponseFactory.php
│   │   │
│   │   ├── Interfaces/
│   │   │   ├── HttpFactory.php
│   │   │   └── ConfigInterface.php
│   │   │
│   │   ├── Middleware/                    # PSR-15 middleware components
│   │   │   ├── Auth/
│   │   │   │   ├── AuthMiddleware.php
│   │   │   │   ├── GuestOnlyMiddleware.php
│   │   │   │   ├── RequireAuthMiddleware.php
│   │   │   │   └── RequireRoleMiddleware.php
│   │   │   │
│   │   │   ├── CSRFMiddleware.php
│   │   │   ├── ErrorHandlerMiddleware.php
│   │   │   ├── MiddlewareFactory.php
│   │   │   ├── MiddlewareInterface.php
│   │   │   ├── MiddlewarePipeline.php
│   │   │   ├── RateLimitMiddleware.php
│   │   │   ├── RoutePatternMiddleware.php
│   │   │   ├── SessionMiddleware.php
│   │   │   └── TimingMiddleware.php
│   │   │
│   │   ├── Security/
│   │   │   ├── Captcha/
│   │   │   │   ├── CaptchaServiceInterface.php
│   │   │   │   └── GoogleReCaptchaService.php
│   │   │   │
│   │   │   ├── BruteForceProtectionService.php
│   │   │   ├── TokenService.php
│   │   │   └── TokenServiceInterface.php
│   │   │
│   │   ├── Services/
│   │   │   └── ConfigService.php
│   │   │
│   │   ├── Session/
│   │   │   ├── SessionManager.php
│   │   │   └── SessionManagerInterface.php
│   │   │
│   │   ├── Testing/
│   │   │   └── HttpInspector.php
│   │   │
│   │   ├── Controller.php
│   │   ├── ErrorHandler.php
│   │   ├── FrontController.php
│   │   ├── Logger.php
│   │   ├── Router.php
│   │   ├── RouterInterface.php
│   │   └── View.php
│   │
│   ├── Database/                          # Application database
│   │   ├── Migrations/                    # Migration files
│   │   │   ├── CreateRateLimitAttemptsTable.php
│   │   │   ├── CreateTestTable.php
│   │   │   └── CreateUsersTable.php
│   │   │
│   │   └── Seeders/                       # Database seeders
│   │       └── TestSeeder.php             # Test data seeder
│   │
│   ├── public_html/
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   ├── menu.css
│   │   │   │   ├── style.css
│   │   │   │   └── themes/
│   │   │   │       └── forms/
│   │   │   │           ├── dotted.css
│   │   │   │           └── rounded.css
│   │   │   │
│   │   │   └── docs/
│   │   │       ├── commit_notes.md
│   │   │       ├── Forms/
│   │   │       │   ├── Form Layout Customization.md
│   │   │       │   └── Form System Guide.md
│   │   │       ├── MVC Migrations - Complete Reference Guide.md
│   │   │       ├── project_tree.md
│   │   │       ├── Rate Limiting - 3. TODO Future Rate Limikting and Brute Force Protection Enhancements.md
│   │   │       ├── TODO Docs.md
│   │   │       ├── User Registration - 1. New User Registration Process.md
│   │   │       ├── User Registration - 2. Activation Process.md
│   │   │       ├── Using .env, $_ENV, and ConfigService in Your Project.md
│   │   │       ├── ViewAs - User Impersonation Feature - TODO Future Implementation Guide.md
│   │   │       └── xampp - database corruption.md
│   │   │
│   │   ├── .htaccess
│   │   └── index.php
│   │
│   └── dependencies.php                   # DI container definitions
│
├── Tests/                                 # Unit tests mirror src structure
├── vendor/                                # Composer dependencies
│
├── .env                                  # Environment variables
├── .favorites.json
├── .gitattributes
├── .gitignore
├── .phpunit.result.cache
├── composer.json
├── composer.lock
├── MVCLixo Form System -  Our two Architectural Patterns Aproach - Login vs Contact
├── MVCLixo Framework Development Plan.md
├── mvclixo.code-workspace
├── phpunit.xml
├── README.md
├── scratch.md
├── scratchfile.php
└── test-logger.php