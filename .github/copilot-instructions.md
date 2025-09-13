---
applyTo: '**'
---
---
# Copilot Instructions for MVC LIXO Framework (php-mvc-diy-framework)

This document serves as a guide for GitHub Copilot to ensure all code contributions align with the project's standards, conventions, and architecture.

## Coding Style

* **Maximum Line Length:** All code lines must not exceed 120 characters.
* Do not use single-line if statements. Always use block syntax:
  ```php
  // Good:
  if ($condition) {
      // code here
  }

  // Bad:
  if ($condition) doSomething();
  ```



## 1. General Principles and Language

* **PHP Version:** All code must be compatible with **PHP 8.2** and above. Leverage new language features like Readonly Properties, Enums, and Intersection Types where appropriate.
* **PSR Standards:** Strictly follow PSR-3 (Logger), PSR-4 (Autoloading), PSR-7 (HTTP), PSR-11 (DI), PSR-12 (Coding Style), PSR-15 (Middleware).
* **SOLID Principles:** Maintain clean architecture with strong separation of concerns.

## Architecture Overview

**Feature-Based MVC Framework** with layered configuration and dependency injection:

- **Feature Organization:** Code organized under `src/App/Features/{FeatureName}/` with Controller, List/, Form/, Views/ subdirectories
- **Thin Type Classes:** ListType/FormType classes only set context (entity name, default columns/fields). All business logic resides in abstract base classes.
- **Abstract Base Classes:** `AbstractListType` and `AbstractFormType` implement all shared logic for building, rendering, and field resolution.
- **Field Registry Pattern:** `FieldRegistryService` centralizes field/column/field definition lookup with **layered fallbacks**:
  1. Page/view context (e.g., `list_fields/local_posts.php`)
  2. Entity/table context (e.g., `list_fields/posts.php`)
  3. Base/global config (`list_fields/base.php`)
- **Configuration System:** `ConfigService` supports dot notation and folder structure for per-entity overrides
- **Dependency Injection:** All services injected via PHP-DI container defined in `src/dependencies.php`
- **Middleware Stack:** PSR-15 middleware for timing, error handling, session, CSRF, rate limiting, authentication

## Key Patterns & Conventions

### Controller Patterns
- Controllers extend base `Controller` class and receive extensive dependencies via constructor injection
- Use traits for common functionality (`AuthorizationTrait`, `EntityNotFoundTrait`)
- Route parameters accessed via `$this->route_params`
- Context-aware routing with `$this->scrap->getRouteType()` (account/store/core)
- Form handling: Create form → Process submission → Handle validation → Return appropriate HTTP status

### ListType/FormType Patterns
- **Keep classes thin:** Only set entity name, page name, and minimal per-entity overrides
- **Configuration-driven:** Field definitions loaded from `src/Config/list_fields/` with fallback hierarchy
- **Formatter closures:** Use PHP closures for custom field formatting in config files
- **Validation rules:** Defined in config alongside field definitions

### Field Definition Pattern
```php
// Example from src/Config/list_fields/posts.php
'title' => [
    'label' => 'Post Title',
    'list' => [
        'sortable' => true,
        'formatter' => function ($value) {
            return htmlspecialchars((string)$value ?? '');
        },
    ],
    'form' => [
        'type' => 'text',
        'required' => true,
        'minlength' => 2,
        'maxlength' => 100,
        'attributes' => ['class' => 'form-control']
    ]
]
```

### Database & Repository Patterns
- Repository interfaces in `App\Repository\` with implementations following naming convention
- Entity classes in `App\Entities\` with getter/setter methods
- Database abstraction layer with migrations and seeding

## Code Quality and Conventions

* **DocBlocks:** Every class, method, and property must be documented with PHPDoc blocks including `@param`, `@return`, `@throws`
* **Type Declarations:** Use strict typing with `declare(strict_types=1)`
* **Error Handling:** Use custom exception classes from `Core\Exceptions\`
* **Security:** Always use prepared statements, validate/sanitize input, implement CSRF protection

## Configuration and Environment

* **Environment Variables:** Store sensitive data in `.env` file, access via `vlucas/phpdotenv`
* **Config Loading:** Use `ConfigService` with dot notation: `$config->get('security.captcha.force_captcha')`
* **Per-Entity Config:** Override defaults in `src/Config/list_fields/{entity}.php`

## Developer Workflows

### Database Operations
```bash
# Run migrations
php bin/console.php migrate

# Rollback migrations
php bin/console.php rollback 1

# Run seeders
php bin/console.php seed UsersSeeder
php bin/console.php seed --all
```

### Testing
```bash
# Run PHPUnit tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Code Quality
```bash
# PHP CodeSniffer
vendor/bin/phpcs src/

# PHPStan static analysis
vendor/bin/phpstan analyse src/
```

## Key Files & Directories

### Core Framework
- `src/Core/List/AbstractListType.php` – Base list functionality with field resolution
- `src/Core/Form/AbstractFormType.php` – Base form functionality
- `src/Core/Services/FieldRegistryService.php` – Field definition lookup with fallbacks
- `src/Core/Services/ConfigService.php` – Configuration loading with dot notation
- `src/dependencies.php` – PHP-DI container definitions
- `src/Core/Middleware/` – PSR-15 middleware components

### Application Structure
- `src/App/Features/*/Controller/` – Feature controllers
- `src/App/Features/*/List/` – Thin ListType classes
- `src/App/Features/*/Form/` – Thin FormType classes
- `src/Config/list_fields/` – Per-entity field definitions
- `src/Config/list_fields_base.php` – Global field definitions
- `Tests/` – Unit tests mirroring src structure

### External Integrations
- **Database:** PDO with migration system
- **Email:** PHPMailer integration
- **CAPTCHA:** Google reCAPTCHA
- **Session:** Custom session management
- **Security:** CSRF tokens, rate limiting

## Integration & Extensibility

### Adding New Features
1. Create feature directory under `src/App/Features/`
2. Implement thin Controller, ListType, FormType classes
3. Add field definitions to `src/Config/list_fields/`
4. Register dependencies in `src/dependencies.php`
5. Add routes and middleware as needed

### Custom Field Types
- Define in config files with closures for formatting
- Extend `FieldRegistryService` for complex dynamic fields
- Store field definitions in database for admin-editable fields

### Route Context Handling
- Use `$this->scrap->getRouteType()` to determine account/store/core context
- Adjust URLs, permissions, and data filtering based on context
- URL enums defined in `App\Enums\Url` with context-specific variants

## Examples

### Controller Pattern
```php
class PostsController extends Controller {
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
        // ... other dependencies
    ) {
        parent::__construct($route_params, $flash, $view, $httpFactory, $container, $scrap);
    }
}
```

### ListType Pattern
```php
class PostsListType extends AbstractListType {
    protected const LIST_TYPE = 'POSTS';
    protected const LIST_NAME = 'posts_list';

    public function __construct(FieldRegistryService $fieldRegistryService) {
        $this->fieldRegistryService->setEntityName(static::LIST_TYPE);
        $this->fieldRegistryService->setPageName(static::LIST_NAME);
        parent::__construct($fieldRegistryService);
    }
}
```

### Config-Driven Fields
See `src/Config/list_fields/posts.php` and `src/Config/list_fields_base.php` for field definition patterns with fallback hierarchy.

---
If any section is unclear or missing, please provide feedback to improve these instructions.