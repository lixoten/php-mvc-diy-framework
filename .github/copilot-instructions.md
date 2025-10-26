---
applyTo: '**'
---
---
# Copilot Instructions for MVC LIXO Framework (php-mvc-diy-framework)

This document serves as a guide for GitHub Copilot to ensure all code contributions align with the project's standards, conventions, and architecture.


## Code Style and Best Practices

## keep me happy
- Also include a proposed file name with display code if the code if for me to use.
    - if the code is a random sample no need for a file name.
- Also include file name if you are sugdesting a fix or enhancement, and line number would also be nice.
- my public folder is `public_html` NOT `public'

## Coding Style
### General Principles and Formatting
- Line Length: Adhere to a maximum line length of 120 characters. Break long lines for improved readability.
- Indentation: Use a consistent indentation style of 4 spaces for PHP and 4 spaces for HTML and JavaScript.
- Code Formatting: Keep tags, attributes, and content on logical lines.
- Comments: Use clear, concise comments to explain complex sections or to add notes about specific functionality.


### Adoption of a Consistent Singular Naming Convention
- improve codebase consistency, we will now adopt a singular naming convention across all components: tables, controllers, models, and routes.

- The new convention is as follows:
    - Models: Singular (Post, Comment). This is standard practice.
    - Tables: Singular (post, comment). A table is a collection of a single entity type, so we will use the singular name to represent it.
    - Controllers: Singular (PostController, CommentController). This avoids confusion and emphasizes that the controller class is a single blueprint for handling a resource.
    - Routes: Singular (/post, /comment). We will access resource collections via singular paths (e.g., GET /post).
- Why this change?
    - This decision prioritizes consistency above all else. Instead of asking "should this be singular or plural?", we will all follow one simple, unified rule: always use the singular form.
    - This rule applies to all new features and components going forward. We will ALSO be refactoring existing code to this new standard. So please point this out if you see this rule not being followed


### PHP Conventions
- Type Hinting: For PHPDoc array type hints, prefer the use of generics to specify both key and value types for clarity and static analysis.
    - Do not use Type[].
    - Do use array<KeyType, ValueType>.
- Exceptions: Always include a declare(strict_types=1); statement at the top of every PHP file. Use custom exceptions for specific application errors, like the ValidatorNotFoundException.
- Newline at end of file - PSR2.Files.EndFileNewline


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

### Database-Agnostic Design

* **Data Types:** When defining database schema in code or comments, use portable, standard SQL data types (e.g., `VARCHAR`, `INTEGER`) and avoid database-specific types (e.g., Oracle's `NUMBER`, MySQL's `ENUM`).

* **Constraints:** To enforce a limited set of values, describe the use of a **`CHECK` constraint** on a `VARCHAR` column, which is a standard SQL feature. Do not propose a database-specific `ENUM` data type.

* **SQL Functions:** Avoid database-specific functions (e.g., `NOW()` or `SYSDATE`). Assume a framework or application service will handle these translations.

### Forms and Input Elements

* **Semantic Fields:** Always wrap `<input>` elements within a `<label>` tag or use the `for` attribute to associate a label with its input. This is crucial for accessibility.

* **Input Types:** Use the most appropriate HTML5 input type (`email`, `tel`, `date`, `password`, etc.) to leverage built-in browser validation and improve the user experience.

* **Required Fields:** Use the `required` attribute on inputs that are mandatory for form submission.

* **Placeholders and Helpers:** Use the `placeholder` attribute to provide a hint to the user about the expected input format. Do not use placeholders as a substitute for labels.

* **Accessibility:** Add ARIA attributes where necessary to improve accessibility, especially for complex form controls.

## 5. Project Architecture and File Structure

* **Assets:** All styling should be in a single `<style>` block within the HTML file. JavaScript logic should be placed in a `<script>` block.


## 1. General Principles and Language

* **PHP Version:** All code must be compatible with **PHP 8.2** and above. Leverage new language features like Readonly Properties, Enums, and Intersection Types where appropriate.
* **PSR Standards:** Strictly follow PSR-3 (Logger), PSR-4 (Autoloading), PSR-7 (HTTP), PSR-11 (DI), PSR-12 (Coding Style), PSR-15 (Middleware). I also try to follow PSR-2
* **SOLID Principles:** Maintain clean architecture with strong separation of concerns.

## Architecture Overview

**Feature-Based MVC Framework** with layered configuration and dependency injection:

- **Feature Organization:** Code organized under `src/App/Features/{FeatureName}/` with Controller, List/, Form/, Views/ subdirectories
- **Thin Type Classes:** ListType/FormType classes only set context (entity name, default columns/fields). All business logic resides in abstract base classes.
- **Abstract Base Classes:** `AbstractListType` and `AbstractFormType` implement all shared logic for building, rendering, and field resolution.
- **Field Registry Pattern:** `FieldRegistryService` centralizes field/column/field definition lookup with **layered fallbacks**:
  1. Page/view context (e.g., `list_fields/local_post.php`)
  2. Entity/table context (e.g., `list_fields/post.php`)
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
// Example from src/Config/list_fields/post.php
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
class PostController extends Controller {
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
class PostListType extends AbstractListType {
    protected const LIST_TYPE = 'POST';
    protected const LIST_NAME = 'post_list';

    public function __construct(FieldRegistryService $fieldRegistryService) {
        $this->fieldRegistryService->setEntityName(static::LIST_TYPE);
        $this->fieldRegistryService->setPageName(static::LIST_NAME);
        parent::__construct($fieldRegistryService);
    }
}
```

### Config-Driven Fields
See `src/Config/list_fields/post.php` and `src/Config/list_fields_base.php` for field definition patterns with fallback hierarchy.

---
If any section is unclear or missing, please provide feedback to improve these instructions.