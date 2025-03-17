MVCLIXO/ (project-root)
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
│   │   └── (config files)
│   │
│   ├── Core/
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
│   │   │   ├── ForbiddenException.php
│   │   │   ├── HttpException.php
│   │   │   ├── PageNotFoundException.php
│   │   │   ├── RecordNotFoundException.php
│   │   │   ├── ServerErrorException.php
│   │   │   ├── ServiceUnavailableException.php
│   │   │   ├── RecException.php
│   │   │   ├── UnauthenticatedException.php
│   │   │   └── ValidationException.php
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
│   │   │   ├── ErrorHandlerMiddleware.php
│   │   │   ├── MiddlewareFactory.php
│   │   │   ├── MiddlewareInterface.php
│   │   │   ├── MiddlewarePipeline.php
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
│   ├── logs/
│   │   └── (log files)
│   │
│   └── public_html/
│       ├── assets/
│       ├── .htaccess
│       ├── index.php
│       └── Assets/
│           └── Css/
│               ├── menu.css
│               └── style.css
│
├── temp/
│   └── (temporary files)
│
├── vendor/
│   └── (vendor files)
│
├── .favorites.json
├── composer.json
└── composer.lock