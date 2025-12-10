---
applyTo: '**'
---
---
# Copilot Instructions for MVC LIXO Framework (php-mvc-diy-framework)

This document serves as a guide for GitHub Copilot to ensure all code contributions align with the project's standards, conventions, and architecture.


**Mandate (Mandatory Rules for All Code Generation):**

1.  **Strict SOLID Adherence:** All generated code MUST adhere to the SOLID principles.
3.  **Single Responsibility Principle (SRP):** Classes and methods MUST only handle one concern. Never mix concerns like **Persistence (Database)**, **Business Logic (Validation/Calculation)**, and **Presentation (HTML/Headers)** within the same class or method.
4.  **Separation of Concerns (SoC):** All code MUST separate high-level logic (orchestration) from low-level details (implementation).
    * **Controllers** MUST only handle request parsing and delegating work to Services. They MUST NOT contain business logic or database queries.
    * **Services** MUST contain all business logic and MUST interact with Repositories.
    * **Repositories** MUST contain all persistence (database) logic.
5.  **Dependency Inversion Principle (DIP):** Depend on **abstractions** (Interfaces) over **concretions** (Classes). Use Constructor Injection for all dependencies.
6.  **Interface Segregation Principle (ISP):** Avoid "fat" interfaces. If an interface is used, it must be small and role-specific.
7. **Framework Neutrality** We are developing initially with Bootstrap, but we also have MaterialDesign, Vanilla, and tailwind..
- This framework supports multiple renderers (BootstrapListRenderer, MaterialListRenderer, etc.)

If a code suggestion violates SRP, OCP, or DIP, you MUST refuse the suggestion and explain which principle was violated.
same applies to mixing concerns. If you spot code mixing concerns please alert me.

Try to exposing a missing configuration!


## Code Style and Best Practices

## keep me happy
- Include a proposed file name with display code if the code if for me to use.
    - if the code is a random sample no need for a file name.
- Also always include the file name if you are suggesting a fix or enhancement, and also a line number would also be nice.
- if you are suggesting a change or fix. Mark it clearly. i love it when you use ✅, ❌, ⚠️ and other icons to indicate what i need to change.
- my public folder is `public_html` NOT `public'


### Null Coalescing Operator (`??`) Usage

The null coalescing operator (`??`) should be used judiciously. Avoid its blanket application with `?? ''` (empty string fallback) for all variables passed to `htmlspecialchars()`.

**Guidance:**

*   **Expose Bugs:** If a variable passed to `htmlspecialchars()` is *expected* to be a non-null string (e.g., a required URL, a CSS class name from `themeService->getElementClass()`, a mandatory label, or a value from `paginationData` that is guaranteed to exist by a preceding `if` condition), **do not use `?? ''`**. In such cases, if the variable is `null`, it indicates a deeper bug in the data source or logic, and allowing `htmlspecialchars()` to throw an `Argument #1 ($string) must be of type string, null given` error is the correct behavior to highlight the problem.
*   **Intentional Fallbacks:** Use `?? ''` (or `?? false`, `?? 0`, etc.) **only when `null` is an expected and valid state** for that variable, and the fallback value (e.g., an empty string) is a sensible and non-buggy default for rendering. This applies to truly optional configuration values, dynamic data attributes, or optional display text.
*   **Boolean/Numeric Defaults:** For boolean flags, use `?? false`. For numeric defaults, use `?? 0` or `?? 1` as appropriate.

**Example (Bad - masks bug):**
```php
// Bad: If $user->getName() can be null, it's a bug in the User entity or data retrieval.
// htmlspecialchars($user->getName() ?? '')
```

**Example (Good - exposes bug):**
```php
// Good: If $user->getName() is null, htmlspecialchars will throw an error,
// indicating a problem that needs fixing upstream.
htmlspecialchars($user->getName())
```

**Example (Good - intentional fallback):**
```php
// Good: 'add_url' is an optional configuration, so an empty string is a valid default.
htmlspecialchars($options['add_url'] ?? '')
```


## Coding Style
### General Principles and Formatting
- Line Length: Adhere to a maximum line length of 120 characters. Break long lines for improved readability.
- Indentation: Use a consistent indentation style of 4 spaces for PHP and 4 spaces for HTML and JavaScript.
- Code Formatting: Keep tags, attributes, and content on logical lines.
- Comments: Use clear, concise comments to explain complex sections or to add notes about specific functionality.
- PSR2 - Add newline at the end if none is found
- PSR2 - Function closing brace must go on the next line following the body;
- in my phpDI file, dependencies.php please use:
    - autowire when ever possible and use fully qualified class name (FQCN)
        - example:
        ```php
        \Core\Console\Commands\MakeMigrationCommand::class => \DI\autowire()
            ->constructorParameter('migrationGenerator', \DI\get(Core\Console\Generators\MigrationGenerator::class))
            ->constructorParameter('schemaLoaderService', \DI\get(Core\Services\SchemaLoaderService::class)),
        ```


### Adoption of a Consistent Singular Naming Convention
- improve codebase consistency, we will now adopt a singular naming convention across all components: tables, controllers, models, and routes.

- The new convention is as follows:
    - Models: Singular (Testy, Post, Comment). This is standard practice.
    - Tables: Singular (testy, post, comment). A table is a collection of a single entity type, so we will use the singular name to represent it.
    - Controllers: Singular (TestyController, PostController, CommentController). This avoids confusion and emphasizes that the controller class is a single blueprint for handling a resource.
    - Routes: Singular (/testy, /post, /comment). We will access resource collections via singular paths (e.g., GET /post).
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


* **Data Types:** When defining database schema in code or comments, use portable, standard SQL data types (e.g., `VARCHAR`, `INTEGER`). **However, the framework's schema definition system *may* use `db_type: 'enum'` as an abstraction to represent a field that will be persisted as a portable `CHAR` or `VARCHAR` type with a `CHECK` constraint.** Avoid proposing other database-specific types (e.g., Oracle's `NUMBER`, MySQL's native `ENUM` type) for direct database use.


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
  1. Page/view context (e.g., `src\App\Features\Testy\Config\testy_fields.php`)
  2. Entity/table context (e.g., `src\App\Features\Testy\Config\testy_fields.php`)
  3. Base/global config (`src\Config\render\field_base.php`)
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




### Config View Definition Pattern
```php
return [
    'options' => [
        'default_sort_key' => 'created_at',
        'default_sort_direction' => 'DESC'
    ],
    'pagination' => [
        'per_page' => 4
    ],
    'render_options' => [
        'title'                 =>  'list.testy.title',
        'show_actions'          => true,
        'show_action_add'       => true,
        'show_action_edit'      => true,
        'show_action_del'       => true,
        'show_action_status'    => false,
    ],
    'list_fields' => [
        'id', 'store_id', 'user_id', 'title',  'content', 'status', 'created_at'
    ],
];
```



### Config Fields Definition Pattern
```php
// Example from ssrc\App\Features\Testy\Config\testy_list_fields.php
'title' => [
    'label' => 'Testy Title',
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

### Application Structure src\App\Features\Testy\Config\testy_list_fields.php
- `src\App\Features\Testy\TestyController.php` – Feature controllers
- `src\Core\List\ZzzzListType.php` – Thin ListType class
- `src\Core\List\ZzzzFormType.php` – Thin FormType class
-  Config fields for a page - uses a fallback
    - `src\App\Features\Testy\Config\testy_list_fields.php`(list, edit, add...) – field definitions - page level - highest
    - `src\App\Features\Testy\Config\testy_fields.php` – Per-entity field definitions - Entity level
    - `src\Config\render\field_base.php` – Common field definitions - lowest

### External Integrations
- **Database:** PDO with migration system
- **Email:** PHPMailer integration
- **CAPTCHA:** Google reCAPTCHA
- **Session:** Custom session management
- **Security:** CSRF tokens, rate limiting

## Integration & Extensibility

### Adding New Features
1. Create feature directory under `src/App/Features/Xxxx`
3. Add field definitions to `src\App\Features\Xxxx\Config\testy_fields.php`
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
class TestyController extends AbstractCrudController
{
    protected ConfigService $config;
    protected ?ServerRequestInterface $request = null;

    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash22,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        CurrentContext $scrap,
        //-----------------------------------------
        private FeatureMetadataService $featureMetadataService,
        protected FormFactoryInterface $formFactory,
        protected FormHandlerInterface $formHandler,
        FormTypeInterface $formType,
        private ListFactoryInterface $listFactory,
        private ListTypeInterface $listType,
        TestyRepositoryInterface $repository,
        protected TypeResolverService $typeResolver,
        //-----------------------------------------
        ConfigInterface $config,
        protected LoggerInterface $logger,
        protected EmailNotificationService $emailNotificationService,
        private PaginationService $paginationService,
        private FormatterService $formatter,
    ) {
        parent::__construct(
            $route_params,
            $flash22,
            $view,
            $httpFactory,
            $container,
            $scrap,
            //-----------------------------------------
            $featureMetadataService,
            $formFactory,
            $formHandler,
            $formType,
            $listFactory,
            $listType,
            $repository,
            $typeResolver,
        );
        $this->config = $config;
        $this->listType->routeType = $scrap->getRouteType();
        $this->logger = $logger;
        $this->emailNotificationService = $emailNotificationService;
        $this->paginationService = $paginationService;
        $this->formatter = $formatter;
    }
```

### ListType Pattern
```php
class ZzzzListType extends AbstractListType
{
    protected array $options = [];

    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
    ) {
        $this->fieldRegistryService = $fieldRegistryService;
        $this->configService        = $configService;

        parent::__construct(
            fieldRegistryService: $this->fieldRegistryService,
            configService: $this->configService,
        );
    }
```


---
If any section is unclear or missing, please provide feedback to improve these instructions.