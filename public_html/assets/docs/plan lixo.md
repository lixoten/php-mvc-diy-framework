# Phased Plan for Evolving editAction to Component-Based Rendering

This plan outlines the steps to refactor the `editAction` in `AbstractCrudController.php` from producing a single form to generating reusable components (e.g., forms, lists) that can be composed on a single page. This follows SOLID principles, avoids mixing concerns, and leverages the framework's configuration-driven architecture. Components will be thin, config-driven wrappers around existing logic, with shared behavior in abstract classes.

The plan is divided into phases for incremental development and testing. Each phase includes:
- **New Files**: Files to create.
- **Edited Files**: Existing files to modify (with key changes summarized).
- **Renamed Files**: Files to rename (none initially, but noted if needed later).
- **Deleted Files**: Files to remove (none in this plan; old files can coexist during migration).
- **Key Notes**: Implementation details, dependencies, and testing.

Assumptions:
- Develop alongside existing `FormType`/`ListType` (e.g., via `V2` suffixes) before phasing out.
- Use PHP 8.2 features (e.g., readonly properties).
- Append "Service" to services (e.g., `ComponentService`).
- Follow PSR-12, strict types, and the provided coding instructions.
- No hacks; separate concerns (e.g., validation in forms, rendering in components).

## Phase 1: Core Component Abstractions
Introduce the component system foundations without altering controllers yet. Focus on interfaces and abstract classes for reusability.

### New Files
- `src/Core/Components/ComponentInterface.php`: Interface for all components (e.g., `render(): string`).
- `src/Core/Components/AbstractComponent.php`: Abstract base with shared logic (e.g., config loading, rendering helpers).
- `src/Core/Components/FormComponent.php`: Concrete component wrapping a `FormInterface`.
- `src/Core/Components/ListComponent.php`: Concrete component wrapping a list (reusing `AbstractListType`).
- `src/Core/Interfaces/ComponentInterface.php`: Alias or extension if needed (but prefer direct in Components/).

### Edited Files
- None in this phase.

### Renamed Files
- None.

### Deleted Files
- None.

### Key Notes
- `ComponentInterface` defines `render(array $options = []): string` for output.
- `AbstractComponent` injects `ConfigService` and `FieldRegistryService` for config-driven behavior.
- `FormComponent` takes a `FormInterface` in constructor and delegates rendering.
- `ListComponent` takes a list array/data and renders via a template.
- Unit tests: Create `Tests/Core/Components/` mirroring structure.

## Phase 2: ComponentService Creation
Build the service to manage component creation and integration.

### New Files
- `src/Core/Services/ComponentService.php`: Service for building components (e.g., `createFormComponent(FormInterface $form): FormComponent`).

### Edited Files
- dependencies.php: Add `ComponentService` to DI container (e.g., autowire with injected `FieldRegistryService` and `ConfigService`).

### Renamed Files
- None.

### Deleted Files
- None.

### Key Notes
- `ComponentService` uses `FieldRegistryService` for field resolution and `ConfigService` for options.
- Methods: `createFormComponent()`, `createListComponent()`, `renderComponent()`.
- Inject in controllers via constructor (update `AbstractCrudController` in next phase).
- Test: Unit tests for service methods.

## Phase 3: Controller Refactor
Modify `AbstractCrudController` to produce components instead of a single form.

### New Files
- None.

### Edited Files
- AbstractCrudController.php:
  - Inject `ComponentService` in constructor.
  - Update `editAction` to return an array of components (e.g., `['form' => $componentService->createFormComponent($form)]`).
  - Remove direct form rendering; pass components to view.
  - Add `overrideFormTypeRenderOptions()` as abstract if needed for component options.

### Renamed Files
- None.

### Deleted Files
- None.

### Key Notes
- Ensure `editAction` builds components post-form processing.
- Example: `$components = ['mainForm' => $componentService->createFormComponent($form), 'sidebarList' => $componentService->createListComponent($list)]`.
- Maintain existing logic for permissions, data fetching.
- Test: Functional tests for controller actions.

## Phase 4: View Updates
Adapt views to render components dynamically.

### New Files
- `src/App/Features/Testys/Views/edit_v2.php`: New view for component rendering (loop over `$components`).

### Edited Files
- edit.php: Keep original; update to support components if desired (e.g., add component loop).
- View.php: Extend to handle component arrays (e.g., pass `$components` to templates).

### Renamed Files
- None (rename `edit.php` to `edit_legacy.php` in Phase 7 if migrating fully).

### Deleted Files
- None.

### Key Notes
- Views: Use `<?php foreach ($components as $component): ?><?= $component->render() ?><?php endforeach; ?>`.
- Ensure progressive enhancement (JS-optional).
- Test: Render tests with multiple components.

## Phase 5: FormView Enhancements
Extend rendering for component-based forms.

### New Files
- None.

### Edited Files
- FormView.php: Add methods for component rendering (e.g., render individual fields/groups as components).

### Renamed Files
- None.

### Deleted Files
- None.

### Key Notes
- Allow rendering subsets (e.g., form rows) for composability.
- Integrate with `ComponentService`.
- Test: Rendering unit tests.

## Phase 6: Config Integration
Add component configs with fallbacks.

### New Files
- `src/Config/component_fields/base.php`: Global component options.
- `src/Config/component_fields/testys_edit.php`: Feature-specific overrides.

### Edited Files
- `src/Core/Services/ComponentService.php`: Update to load configs via `ConfigService` with fallbacks (page > entity > base).

### Renamed Files
- None.

### Deleted Files
- None.

### Key Notes
- Fallback order: Page (e.g., `testys_edit`) > Entity (`testys`) > Base.
- Config example: `'form' => ['render_options' => ['theme' => 'default']]`.
- Test: Config loading tests.

## Phase 7: Testing and Migration
Full testing and optional migration.

### New Files
- `Tests/Core/Components/`: Unit tests for components.
- `Tests/Core/Services/ComponentServiceTest.php`: Service tests.
- `Tests/App/Features/Testys/Controller/TestysControllerComponentTest.php`: Controller integration tests.

### Edited Files
- All previous edited files: Add PHPDoc, ensure strict types, no single-line ifs.

### Renamed Files
- edit.php → `src/App/Features/Testys/Views/edit_legacy.php` (if migrating fully).
- AbstractFormType.php → `src/Core/Form/AbstractFormTypeV1.php` (if phasing out).

### Deleted Files
- AbstractFormType.php (after migration, if no longer needed).

### Key Notes
- Run PHPUnit for all tests.
- Migrate features incrementally (e.g., start with `Testys`).
- Monitor for mixed concerns; warn if validation/rendering overlap.
- Final: Update routes to use new views if applicable.