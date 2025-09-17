JS Features Added to MVC LIXO Framework
Character Counter:
Shows live character count for inputs/textareas, with color and tooltip feedback based on min/max length.

Live Validation Feedback:
Uses HTML5 validation APIs (checkValidity(), validationMessage) to show error messages and Bootstrap invalid styling as the user types or blurs a field.

LocalStorage Auto-Save/Draft:
Saves all form field values to localStorage as the user types.
Restores draft data on page reload.
Shows a notification and "Discard Draft" button if a draft is present.
Triggers validation after restoring draft values.





# MVC LIXO Framework V0.1

A lightweight, feature-based PHP MVC framework focused on clean architecture and SOLID principles.


We create a new AbstractCrudController for the framework that serves as a base controller that centralizes and standardizes common CRUD (Create, Read, Update, Delete) operations for your application's entities. The main reason was a way of reducing boilerplate code.


## Progressive Enhancements
- Progressive enhancement:
  - Added JS features for character counters,
  - live validation,
  - input masking (imaskjs),
  - auto-save with localStorage,
  - and AJAX form save with inline error handling.
  - All features are config-driven and gracefully degrade for JS-less users.

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
- **Application Middleware**
    - TimingMiddleware
    - ErrorHandlerMiddleware
    - Session Middleware
    - CSRF Protection Middleware
    - RateLimitMiddleware
    - RateLimitMiddleware
    - RoutePatternMiddleware - Route protection via middleware
    - Authentication Middleware Group
        - RequireAuthMiddleware
        - RequireRoleMiddleware
        - GuestOnlyMiddleware

- **Database Layer**: Full database abstraction with migrations, seeding, and query building
- **Form System**
    - Advanced form handling with validation
    - CSRF protection
    -  CAPTCHA integration with Google reCAPTCHA
- **Role-Based Access Control**: User roles and permissions
- **Authentication System**: Login, logout, remember me, and session management
    - Login Remember Me
    - Rate Limiting
    - CAPTCHA protection for login and registration

## Key Features Missing and upcoming
- apply captcha to other features like registration.

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


