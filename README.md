# MVC LIXO Framework

A lightweight, feature-based PHP MVC framework focused on clean architecture and SOLID principles.

## Features

- Feature-based directory structure
- Comprehensive exception handling
- Flash messaging system
- Session management
- Dependency injection using PHP-DI
- View rendering with layout support
- Environment-aware configuration service
- PSR-3 compliant logging system with rotation and sampling

## Key Features Missing
- Database, there is none 
- Login, Authentication, there is none
- Repository Layer, nope...
- Middleware, not yet...
- Request/Response Layer. Coming up next

## PSR Standards Implemented
- **PSR-3**: Logger Interface - Standardized logger implementation
- **PSR-4**: Autoloading - Composer autoloading for class mapping
- **PSR-11**: Container Interface - DI Container implementation via PHP-DI
- **PSR-12**: Extended Coding Style - Code formatting and structure standards

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




