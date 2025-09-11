---
applyTo: '**'
---
# Copilot Instructions for MVC LIXO V11.0 Framework (php-mvc-diy-framework) dd

## Architecture Overview
- Modular, feature-based PHP MVC framework with strong separation of concerns and SOLID principles.
- **Controllers** (e.g., `PostsController`) orchestrate requests, instantiate/configure ListType/FormType, and pass dynamic options.
- **ListType/FormType classes** (e.g., `PostsListType`, `PostsFormType`) are thin: set context (entity/page name, default columns/fields), inherit logic from abstract base classes.
- **Abstract base classes** (`AbstractListType`, `AbstractFormType`) implement all shared logic: building, rendering, actions, field/column/field resolution with fallback.
- **FieldRegistryService** centralizes field/column/field definition lookup with layered fallbacks: page/view context → entity/table context → base/global config. Field definitions are loaded from config files (see `src/Config/list_fields/` and `src/Config/list_fields_base.php`).
- **ConfigService** supports dot notation and folder structure for config files, enabling per-entity and global config overrides.
- **Middleware**: PSR-15 middleware for timing, error handling, session, CSRF, rate limiting, and authentication (see `src/Core/Middleware/`).
- **Database Layer**: Abstraction with migrations, seeding, and query building.
- **Role-Based Access Control** and **Authentication**: User roles, permissions, login, remember me, rate limiting, and CAPTCHA.

## Key Patterns & Conventions
- **Keep ListType/FormType classes thin:** Only set context and per-entity tweaks. All business logic and field/column resolution lives in abstract base classes and services.
- **Field/column/field definitions:** Use config files in `src/Config/list_fields/` (per-entity) and `src/Config/list_fields_base.php` (global). Use `FieldRegistryService` for all lookups and fallbacks.
- **Fallback order:** Page/view context → entity/table context → base/global config. If a field/column is not found, it is not displayed.
- **Formatter pattern:** Field definitions may reference formatter services or closures (if in PHP config). For DB-driven or dynamic fields, use a service name or class reference.
- **Dependency Injection:** All services and registries are injected via DI container (see `src/dependencies.php`).
- **PSR Standards:** Implements PSR-3 (Logger), PSR-4 (Autoloading), PSR-7 (HTTP), PSR-11 (DI), PSR-12 (Coding Style), PSR-15 (Middleware).

## Developer Workflows
- **Add a new entity/list:** Create a new ListType/FormType class (thin), add field/column definitions in config, and update the controller to use the new type.
- **Override field/column for a page:** Add a page-specific config file in `src/Config/list_fields/` and set the page context in the ListType.
- **Add/override a formatter:** Reference a formatter service/class in the field definition, and implement the formatter in `src/Core/Services/` or similar.
- **Update DI:** Register new services/types in `src/dependencies.php`.
- **Testing:** Run tests with PHPUnit. No custom build step. Debug using Xdebug or standard PHP tools.

## Key Files & Directories
- `src/App/Features/*/Controller/` – Controllers for each feature/entity
- `src/App/Features/*/List/` – ListType classes (thin, per-entity)
- `src/App/Features/*/Form/` – FormType classes (thin, per-entity)
- `src/Core/List/AbstractListType.php` – All shared list logic
- `src/Core/Form/AbstractFormType.php` – All shared form logic
- `src/Core/Services/FieldRegistryService.php` – Field/column/field lookup with fallbacks
- `src/Core/Services/ConfigService.php` – Config loading, dot notation, folder support
- `src/Config/list_fields/` – Per-entity field/column definitions
- `src/Config/list_fields_base.php` – Global/common field/column definitions
- `src/dependencies.php` – DI container configuration
- `src/Core/Middleware/` – PSR-15 middleware components
- `Tests/` – Unit tests mirror src structure
- `logs/` – Log files

## Integration & Extensibility
- **To add a new feature/entity:** Add a controller, thin ListType/FormType, and config files. Register in DI.
- **To support dynamic/admin-editable fields:** Store field definitions in DB, generate PHP config files, or extend `FieldRegistryService` to load from DB.
- **To customize per-page:** Use page context in ListType and add page-specific config.

## Examples
- See `PostsListType` and `PostsController` for a typical feature setup.
- See `FieldRegistryService::getFieldWithFallbacks()` for fallback logic.

---
If any section is unclear or missing, please provide feedback to improve these instructions.