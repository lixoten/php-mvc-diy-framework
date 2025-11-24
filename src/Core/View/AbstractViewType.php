<?php

declare(strict_types=1);

namespace Core\View;

use Core\Services\FieldRegistryService;
use Core\Interfaces\ConfigInterface;
use Core\Services\ViewConfigurationService;
use Psr\Log\LoggerInterface;

/**
 * Abstract base class for View types.
 *
 * Handles View configuration, layout, and field definitions.
 * Uses FieldRegistryService for field definitions with fallback logic:
 *   1. Page/view context (set via setFocus)
 *   2. Entity/table context
 *   3. Base/global config
 *
 * This class serves as a blueprint for concrete ViewType implementations,
 * ensuring consistency and adherence to SOLID principles.
 */
abstract class AbstractViewType implements ViewTypeInterface
{
    /** @var array<string, mixed> */
    protected array $options = [];
    /** @var array<string, mixed> */
    protected array $renderOptions = [];
    /** @var array<string, mixed> */
    protected array $fields = [];
    /** @var array<string, mixed> */
    protected array $layout = [];
    /** @var array<string> */
    protected array $hiddenFields = []; // Fields that are part of the view but not explicitly in layout

    public readonly string $pageKey;
    public readonly string $pageName;
    public readonly string $pageAction;
    public readonly string $pageFeature;
    public readonly string $pageEntity;

    /**
     * Constructor.
     *
     * @param FieldRegistryService $fieldRegistryService Service for field definitions.
     * @param ConfigInterface $configService Configuration service.
     * @param ViewConfigurationService $viewConfigService View configuration service.
     * @param LoggerInterface $logger Logger instance.
     */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
        protected ViewConfigurationService $viewConfigService,
        protected LoggerInterface $logger,
    ) {
        // Properties are promoted directly by the constructor.
    }

    /**
     * {@inheritdoc}
     */
    public function setFocus(
        string $pageKey,
        string $pageName,
        string $pageAction,
        string $pageFeature,
        string $pageEntity,
    ): void {
        $this->pageKey     = $pageKey;
        $this->pageName    = $pageName;
        $this->pageAction  = $pageAction;
        $this->pageFeature = $pageFeature;
        $this->pageEntity  = $pageEntity;

        // Delegate configuration loading to service.
        $config = $this->viewConfigService->loadConfiguration(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity,
        );

        // Apply loaded configuration to properties.
        $this->options = $config['options'] ?? [];
        $this->renderOptions = $config['render_options'] ?? [];
        $this->layout = $config['layout'] ?? [];
        $this->hiddenFields = $config['hidden_fields'] ?? [];

        // Validate and clean fields based on layout and hidden fields.
        $this->filterValidateViewFields();
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderOptions(): array
    {
        return $this->renderOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function setRenderOptions(array $renderOptions): void
    {
        $this->renderOptions = $renderOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeRenderOptions(array $renderOptions): void
    {
        $this->renderOptions = array_merge($this->renderOptions, $renderOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function setFields(array $fields): void
    {
        // Internal fields property updated directly, but validation is done via filterValidateViewFields.
        $this->fields = $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getLayout(): array
    {
        return $this->layout;
    }

    /**
     * {@inheritdoc}
     */
    public function setLayout(array $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * Validates and filters the fields based on the loaded layout and hidden fields.
     *
     * This method ensures that only valid fields from the field registry are included
     * in the final view configuration and that the layout correctly reflects these fields.
     *
     * @return void
     */
    protected function filterValidateViewFields(): void
    {
        $currentLayout = $this->getLayout();
        $currentHiddenFields = $this->hiddenFields;

        // 1. Get all fields explicitly defined in the layout.
        $layoutFields = [];
        foreach ($currentLayout as $section) {
            if (isset($section['fields']) && is_array($section['fields'])) {
                foreach ($section['fields'] as $field) {
                    // Skip commented-out fields (if present as strings with //)
                    if (is_string($field) && strpos($field, '//') !== 0) {
                        $layoutFields[] = $field;
                    }
                }
            }
        }

        // 2. Merge layout fields with any explicitly defined hidden fields.
        $allConfiguredFields = array_unique(array_merge($layoutFields, $currentHiddenFields));

        // 3. Filter and validate ALL identified fields against the FieldRegistryService.
        // This sets the context for field resolution.
        $validFields = $this->fieldRegistryService->filterAndValidateFields(
            $allConfiguredFields,
            $this->pageKey,
            $this->pageEntity
        );

        // 4. Validate and fix the layout, removing any fields not present in $validFields.
        $validatedLayout = $this->validateAndFixLayoutFields($currentLayout, $validFields);

        // 5. Update the internal fields property with only the valid fields.
        $this->fields = $validFields;

        // 6. Update the layout property with the validated layout.
        $this->setLayout($validatedLayout);

        // 7. Clean up internal hidden_fields array, as they are now merged into `fields`.
        $this->hiddenFields = [];
    }

    /**
     * Validates an array of field names against the known schema for this View type.
     *
     * Sets the entity and page context on the FieldRegistryService before validation.
     * Returns only valid field names, while logging and triggering warnings for any invalid ones.
     *
     * @param array<string> $fields Array of field names to validate.
     * @return array<string> Array of valid field names.
     */
    public function validateFields(array $fields): array
    {
        if (empty($fields)) {
            $this->logWarning("No fields provided for validation.");
            return [];
        }

        $validFields = $this->fieldRegistryService->filterAndValidateFields(
            $fields,
            $this->pageKey,
            $this->pageEntity
        );

        return $validFields;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(ViewBuilderInterface $builder): void
    {
        // Set Render Options and Layout for the builder.
        $builder->setRenderOptions($this->getRenderOptions());
        $builder->setLayout($this->getLayout());
        $builder->setTitle($this->renderOptions['title'] ?? $this->pageName); // Set title from render options or page name

        // Add fields to the builder.
        foreach ($this->fields as $fieldName) {
            $fieldDef = $this->fieldRegistryService->getFieldWithFallbacks(
                $fieldName,
                $this->pageKey,
                $this->pageEntity
            );

            if ($fieldDef && isset($fieldDef['view'])) {
                $viewOptions = $fieldDef['view'];
                $viewOptions['formatters'] = $fieldDef['formatters'] ?? null;
                // Note: Validators are typically not needed for a read-only view.

                $builder->add($fieldName, $viewOptions);
            } else {
                $this->logger->warning('AbstractViewType: Field definition for "view" context not found', [
                    'fieldName' => $fieldName,
                    'pageKey'   => $this->pageKey,
                    'pageEntity' => $this->pageEntity,
                ]);
            }
        }
    }

    /**
     * Validates the layout configuration against the available fields and fixes it by
     * removing any references to non-existent fields or empty sections.
     *
     * @param array<string, mixed> $layout The raw layout configuration.
     * @param array<string> $availableFields An array of field names that are considered valid.
     * @return array<string, mixed> The validated and fixed layout configuration.
     */
    private function validateAndFixLayoutFields(array $layout, array $availableFields): array
    {
        if (empty($layout)) {
            return [];
        }

        foreach ($layout as $secId => &$section) {
            if (isset($section['fields']) && is_array($section['fields'])) {
                // Filter out fields that are not in the availableFields list.
                $section['fields'] = array_filter(
                    $section['fields'],
                    fn ($field) => in_array($field, $availableFields, true)
                );

                // If no fields left in this section, remove the section itself.
                if (empty($section['fields'])) {
                    unset($layout[$secId]);
                    $this->logWarning("Removed empty section at index {$secId} from view layout - ERR-VIEW90");
                }
            } else {
                // If a section has no 'fields' key or it's not an array, it's invalid.
                unset($layout[$secId]);
                $this->logWarning("Removed invalid or empty section at index {$secId} from view layout - ERR-VIEW91");
            }
        }
        unset($section); // Unset reference to avoid unexpected behavior.

        // Re-index sections array if any were removed to ensure a clean array.
        return array_values($layout);
    }

    /**
     * Log a warning message in development mode.
     *
     * @param string $message Warning message.
     * @return void
     */
    private function logWarning(string $message): void
    {
        if ($_ENV['APP_ENV'] === 'development') {
            trigger_error("View Warning: {$message}", E_USER_WARNING);
        }
        // Always log to system log.
        error_log("View Warning: {$message}");
    }
}
