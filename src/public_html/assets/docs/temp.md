GitHub Copilot: # 📋 **Form Configuration Refactoring - Phased Plan**

After reviewing your Form and List implementations side-by-side, you're absolutely right. The Form system has **similar issues that were fixed in the List system during Phases 1-3**, and we need to apply the same clean architecture principles.

---

## 🔍 **Current State Analysis: Form System**

### ❌ **Problems Identified (Mixed Concerns)**

| Issue | Location | Problem | Similar List Issue (Fixed) |
|-------|----------|---------|---------------------------|
| **Config loading in `AbstractFormType::init()`** | Lines 323-425 | Directly calls `$this->configService->get()` and `getFromFeature()` | ✅ Fixed in Phase 2 with `ListConfigurationService` |
| **No centralized config service** | N/A | No `FormConfigurationService` exists | ✅ `ListConfigurationService` created in Phase 2 |
| **Field validation mixed with config loading** | `AbstractFormType::filterValidateFormFields()` (lines 201-245) | Validation logic embedded in config processing | ✅ Separated in `AbstractListType::validateFields()` |
| **Layout validation in `AbstractFormType`** | `validateAndFixLayoutFields()` (lines 281-317) | Business logic mixed with type class | ✅ Moved to service layer for lists |
| **Hardcoded config paths** | Lines 355-380 | Uses string concatenation like `'view_options/' . $pageName` | ✅ Fixed with `ListConfigurationService::loadPageConfiguration()` |
| **No URL generation for actions** | Missing | Forms don't inject action URLs like lists do | ✅ Fixed in Phase 3 for lists |

---

## 🎯 **Refactoring Plan: 3 Phases (Mirrors List Refactoring)**

### **Phase 1: Create `FormConfigurationService`** ⭐ **START HERE**

**Goal:** Centralize all form configuration loading logic (same as Phase 2 for lists).

**What to Create:**

#### **File:** [`src/Core/Services/FormConfigurationService.php`](src/Core/Services/FormConfigurationService.php )

````php
<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for loading and merging form configurations
 * from various sources (default, feature-specific, page-specific)
 *
 * Configuration hierarchy (highest to lowest priority):
 * 1. Page-specific: src/App/Features/{Feature}/Config/{page}_view.php
 * 2. Entity-specific: src/App/Features/{Feature}/Config/{entity}_view.php (if needed)
 * 3. Base/default: src/Config/view.form.php
 */
class FormConfigurationService
{
    public function __construct(
        protected ConfigInterface $configService,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * Load and merge form configuration for a specific page/entity context
     *
     * @param string $pageName Page identifier (e.g., 'testy_edit', 'user_login')
     * @param string $pageFeature Feature name (e.g., 'Testy', 'Auth')
     * @param string $pageEntity Entity name (e.g., 'testy', 'user')
     * @return array<string, mixed> Merged configuration array
     */
    public function loadConfiguration(
        string $pageName,
        string $pageFeature,
        string $pageEntity,
    ): array {
        // 1. Load base/default configuration
        $baseConfig = $this->loadBaseConfiguration();

        // 2. Load page-specific configuration
        $pageConfig = $this->loadPageConfiguration($pageFeature, $pageName);

        // 3. Merge configurations (page > base)
        $mergedRenderOptions = $this->deepMerge(
            $baseConfig['render_options'] ?? [],
            $pageConfig['render_options'] ?? []
        );

        // Layout: page-specific takes precedence
        $layout = $pageConfig['form_layout'] ?? [];

        // Hidden fields: page-specific takes precedence
        $hiddenFields = $pageConfig['form_hidden_fields'] ?? [];

        return [
            'render_options' => $mergedRenderOptions,
            'layout' => $layout,
            'hidden_fields' => $hiddenFields,
        ];
    }

    /**
     * Load base/global form configuration
     *
     * @return array<string, mixed>
     */
    protected function loadBaseConfiguration(): array
    {
        try {
            // Loads from src/Config/view.form.php
            $config = $this->configService->get('view.form') ?? [];
            return [
                'render_options' => $config['render_options'] ?? []
            ];
        } catch (\Exception $e) {
            $this->logger->warning('FormConfigurationService: Failed to load base configuration', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Load page-specific form configuration
     *
     * Example: src/App/Features/Testy/Config/testy_view_edit.php
     *
     * @param string $pageFeature Feature name (e.g., 'Testy')
     * @param string $pageName Page name (e.g., 'testy_edit')
     * @return array<string, mixed>
     */
    protected function loadPageConfiguration(string $pageFeature, string $pageName): array
    {
        try {
            // Extract entity name from page name (e.g., 'testy_edit' -> 'testy')
            $useEntity = explode('_', $pageName)[0];
            $action = explode('_', $pageName)[1] ?? 'form';

            // Build config key: testy_view_edit
            $configKey = "{$useEntity}_view_{$action}";
            $config = $this->configService->getFromFeature($pageFeature, $configKey) ?? [];

            return $config;
        } catch (\Exception $e) {
            $this->logger->debug('FormConfigurationService: No page-specific configuration found', [
                'feature' => $pageFeature,
                'page' => $pageName,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Deep merge multiple arrays, with later arrays taking precedence
     *
     * @param array<string, mixed> ...$arrays
     * @return array<string, mixed>
     */
    protected function deepMerge(array ...$arrays): array
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = $this->deepMerge($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}
````

---

### **Phase 2: Refactor `AbstractFormType::init()`**

**Goal:** Remove all config loading logic from `AbstractFormType` and delegate to `FormConfigurationService`.

**Changes to [`src/Core/Form/AbstractFormType.php`]AbstractFormType.php ):**

#### **Before (Lines 323-425 - Mixed Concerns):**

````php
private function init(): void
{
    $securityConfig = $this->configService->get('security');
    // ... 100+ lines of config loading, merging, validation ...
    $defaultConfig  = $this->configService->get('view.form', []);
    $viewConfig = $this->configService->getFromFeature($pageFeature, $pageName . '_view');
    // ... more config manipulation ...
    $this->filterValidateFormFields();
}
````

#### **After (Simplified, Delegates to Service):**

````php
// Lines 323-360 (simplified)

private function init(): void
{
    // ✅ NEW: Delegate configuration loading to service
    $config = $this->formConfigService->loadConfiguration(
        $this->pageName,
        $this->pageFeature,
        $this->pageEntity,
    );

    // ✅ Apply loaded configuration to properties
    $this->setRenderOptions($config['render_options']);
    $this->setLayout($config['layout']);
    $this->setHiddenFields($config['hidden_fields']);

    // ✅ Validate and clean fields
    $this->filterValidateFormFields();
}
````

---

### **Phase 3: Add URL Context Injection (Like Lists)**

**Goal:** Inject form action URLs from controller via `render_options` (same as Phase 3 for lists).

**Controller Changes ([`src/Core/AbstractCrudController.php`](src/Core/AbstractCrudController.php )):**

````php
// Lines ~260-280 (in editAction/addAction methods)

// ✅ NEW: Inject form action URL via render options
$routeType = $this->scrap->getRouteType();
$actionUrl = $this->feature->editUrlEnum?->url(['id' => $id], $routeType) ?? '';

$this->formType->mergeRenderOptions([
    'action_url' => $actionUrl,
    'route_type' => $routeType,
    // Add cancel URL if needed
    'cancel_url' => $this->feature->listUrlEnum?->url([], $routeType) ?? '',
]);
````

---

## 📊 **Phase Comparison: Lists vs Forms**

| Phase | Lists | Forms | Status |
|-------|-------|-------|--------|
| **Phase 1** | Remove rendering from `ListView` | ✅ Already done (uses `FormRendererInterface`) | ✅ **Complete** |
| **Phase 2** | Extract config to `ListConfigurationService` | ❌ **Missing `FormConfigurationService`** | 🔴 **TODO** |
| **Phase 3** | Inject URLs via `render_options` | ❌ **Missing URL injection** | 🔴 **TODO** |

---

## ✅ **Recommended Action Plan**

### **Step 1: Create `FormConfigurationService` (Phase 2 Equivalent)**

1. ✅ **Create** [`src/Core/Services/FormConfigurationService.php`](src/Core/Services/FormConfigurationService.php ) (code above)
2. ✅ **Inject** into `AbstractFormType` constructor:

````php
// Lines 47-58

public function __construct(
    protected FieldRegistryService $fieldRegistryService,
    protected ConfigInterface $configService,
    protected CaptchaServiceInterface $captchaService,
    protected FormConfigurationService $formConfigService, // ✅ NEW
) {
    $this->fieldRegistryService = $fieldRegistryService;
    $this->configService = $configService;
    $this->captchaService = $captchaService;
    $this->formConfigService = $formConfigService; // ✅ NEW
}
````

3. ✅ **Update** `AbstractFormType::init()` to use service (lines 323-360)
4. ✅ **Update** [`src/dependencies.php`](src/dependencies.php ):

````php
// Add to dependencies.php

\Core\Services\FormConfigurationService::class => \DI\autowire()
    ->constructorParameter('configService', \DI\get(\Core\Interfaces\ConfigInterface::class))
    ->constructorParameter('logger', \DI\get(Psr\Log\LoggerInterface::class)),

\Core\Form\AbstractFormType::class => \DI\autowire()
    ->constructorParameter('fieldRegistryService', \DI\get(\Core\Services\FieldRegistryService::class))
    ->constructorParameter('configService', \DI\get(\Core\Interfaces\ConfigInterface::class))
    ->constructorParameter('captchaService', \DI\get(\Core\Security\Captcha\CaptchaServiceInterface::class))
    ->constructorParameter('formConfigService', \DI\get(\Core\Services\FormConfigurationService::class)), // ✅ NEW
````

---

### **Step 2: Verify No Mixed Concerns**

After Phase 1 (creating `FormConfigurationService`), verify:

- ✅ `AbstractFormType::init()` only calls service, no direct `$configService->get()` calls
- ✅ `FormConfigurationService` handles all file I/O and merging
- ✅ Field validation (`filterValidateFormFields()`) stays in `AbstractFormType` (business logic, not config loading)
- ✅ Layout validation (`validateAndFixLayoutFields()`) stays in `AbstractFormType` (business logic, not config loading)

---

### **Step 3: Add URL Injection (Phase 3 Equivalent)**

Once Phase 2 is stable, inject form action URLs from controller:

````php
// In AbstractCrudController::editAction()

$this->formType->mergeRenderOptions([
    'action_url' => $this->feature->editUrlEnum?->url(['id' => $id], $routeType) ?? '',
    'cancel_url' => $this->feature->listUrlEnum?->url([], $routeType) ?? '',
    'route_type' => $routeType,
]);
````

---

## 🎯 **Final Architecture (After All Phases)**

| Component | Responsibility | Concerns |
|-----------|---------------|----------|
| **`FormConfigurationService`** | Load config from files, merge hierarchies | ✅ Configuration only |
| **`AbstractFormType`** | Field/layout validation, build form structure | ✅ Presentation logic only |
| **`Form`** | Data container, holds fields/layout/errors | ✅ Data structure only |
| **`FormRenderer`** | Generate HTML from `Form` | ✅ Rendering only |
| **`AbstractCrudController`** | Inject runtime context (URLs, route type) | ✅ Coordination only |

---

## 🚀 **Start with Phase 1 (FormConfigurationService)**

**Would you like me to provide the complete refactored `AbstractFormType::init()` method after creating `FormConfigurationService`?** This will be the first concrete step to match your List system's clean architecture.