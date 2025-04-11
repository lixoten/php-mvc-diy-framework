# MVC LIXO Framework

A lightweight, feature-based PHP MVC framework focused on clean architecture and SOLID principles.

## Features

- Feature-based directory structure
- Comprehensive exception handling with HTTP status codes
- Flash messaging system
- Session management
- Dependency injection using PHP-DI
- View rendering with layout support
- Environment-aware configuration service
- PSR-3 compliant logging system with rotation and sampling
- PSR-7 HTTP message interfaces for requests and responses
- PSR-15 Middleware and request handler interfaces
- Request execution timing and performance monitoring
- View helpers for common UI components
- Clean separation of core framework and application code
- **Middleware Protection**: Route protection via middleware
- **Database Layer**: Full database abstraction with migrations, seeding, and query building
- **Form System**: Advanced form handling with validation and CSRF protection
- **Role-Based Access Control**: User roles and permissions
- **Authentication System**: Login, logout, remember me, and session management
    - Login Remember Me
    - Rate Limiting

## Key Features Missing and upcoming
- CAPTCHA.

## PSR Standards Implemented
- **PSR-3**: Logger Interface - Standardized logger implementation
- **PSR-4**: Autoloading - Composer autoloading for class mapping
- **PSR-7**: HTTP Message Interface - Standard HTTP request/response objects
- **PSR-11**: Container Interface - DI Container implementation via PHP-DI
- **PSR-12**: Extended Coding Style - Code formatting and structure standards
- **PSR-15**: HTTP Server Request Handlers - Middleware and request handler interfaces

## Requirements

- PHP 8.0 or higher
- Composer

### Project Structure
```
mvclixo/
├── src/
│   ├── App/
│   │   ├── Features/      # Feature-based organization
│   │   │   ├── Home/
│   │   │   ├── About/
│   │   │   └── Admin/
│   │   └── Services/      # Application services
│   ├── Core/              # Framework core components
│   │   ├── Interfaces/    # Framework interfaces
│   │   │── Middleware/    # PSR-15 middleware components
│   │   └── Services/      # Framework services
│   ├── Config/            # Configuration files
│   └── public_html/       # Document root
├── Tests/                 # Unit tests mirror src structure
├── logs/                  # Log files
└── vendor/                # Composer dependencies
```



## Notes
I am learning php, so I figure I would work on a php mvc application. That takes into account OOP PHP, and I will try to follow SOLID Princicles.  
Another thing, I plan to use and follow PSR's


