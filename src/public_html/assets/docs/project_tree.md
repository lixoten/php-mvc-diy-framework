MVCLIXO/ (project-root)
│
├── bin/
│   └─ console.php                        # Command-line interface script
│
├── src/
│   ├── App/
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
│   │   │       └── Views/
│   │   │           ├── index.php
│   │   │           └── testlogger.php
│   │   │   
│   │   ├── Helpers/
│   │   │   ├── DebugRt.php
│   │   │   ├── HtmlHelper.php
│   │   │   └── UiHelper.php
│   │   │
│   │   ├── Services/
│   │   │   └── FlashMessengerService.php
│   │   │       └── Interfaces/
│   │   │           └── FlashMessageServiceInterface.php
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
│   │   ├── database.php                  # Database configuration
│   │   ├── logger.php                    # Logger configuration
│   │   └── (config files)
│   │
│   ├── Core/
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
│   │   │   │   ├── Index.php              # Index definition
│   │   │   │   ├── Column.php             # Column definition
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

│   │   │
│   │   ├── Form/                          ...# New form handling system
│   │   │   ├── CSRF/                      ...# CSRF protection
│   │   │   │   └── CSRFToken.php
│   │   │   │
│   │   │   ├── FormBuilder.php            ...# Form generation
│   │   │   ├── FormValidator.php          ...# Validation rules
│   │   │   └── ValidationError.php        ...# Error representation
│   │   │


│   │   ├── Http/
│   │   │   ├── HttpFactory.php
│   │   │   └── ResponseEmitter.php
│   │   │
│   │   ├── Interfaces/
│   │   │   ├── HttpFactory.php
│   │   │   └── ConfigInterface.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── CSRFMiddleware.php         ...# New CSRF middleware
│   │   │   ├── ErrorHandlerMiddleware.php
│   │   │   ├── MiddlewareFactory.php
│   │   │   ├── MiddlewareInterface.php
│   │   │   ├── MiddlewarePipeline.php
│   │   │   ├── SessionMiddleware.php      # Session handler middleware
│   │   │   └── TimingMiddleware.php
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
│   │   │   ├── CreateUsersTable.php       # 
│   │   │   └── CreateTestTable.php        #
│   │   │
│   │   └── Seeder/                        # Database seeders
│   │       └── TestSeeder.php             # Test data seeder
│   │
│   ├── logs/
│   │   └── (log files)
│   │
│   │── public_html/
│   │   ├── assets/
│   │   ├── .htaccess
│   │   ├── index.php
│   │   └── Assets/
│   │       ├── Docs/
│   │   │   │   └── project_tree.md        # This file
│   │       └── Css/
│   │           ├── menu.css
│   │           └── style.css
│   └── dependencies.php                   # DI container definitions
│
├── temp/
│   └── (temporary files)
│
├── vendor/
│   └── (vendor files)
│
├── .env                                   # Environment variables
├── .favorites.json
├── composer.json
└── composer.lock