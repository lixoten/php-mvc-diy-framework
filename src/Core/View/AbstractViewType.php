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
    protected array $options = []; // For general view-wide options (e.g., default behavior flags)
    protected array $renderOptions = [];
    protected array $layout = [];
    protected array $fields = [];

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

        // ✅ Delegate configuration loading to ViewConfigurationService
        $config = $this->viewConfigService->loadConfiguration(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity,
        );

        // ✅ Apply loaded configuration to properties
        // Use ?? [] to ensure it's an array if not defined in config
        $this->options = $config['options'] ?? [];
        $this->renderOptions = $config['render_options'] ?? [];
        $this->layout = $config['layout'] ?? [];
        // $this->fields = $config['fields'] ?? [];
        $this->setFields($config['fields'] ?? []);

        $this->filterAndValidateViewStructure();
    }


    /** {@inheritdoc} */
    public function getOptions(): array
    {
        return $this->options;
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
        $validFields = $this->fieldRegistryService->filterAndValidateFields(
            $fields,
            $this->pageKey,
            $this->pageEntity
        );
        $this->fields = $validFields;
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
     * {@inheritdoc}
     */
    public function buildView(ViewBuilderInterface $builder): void
    {
        // Set Render Options and Layout for the builder.
        $builder->setRenderOptions($this->getRenderOptions());
        $builder->setLayout($this->getLayout());
        $builder->setTitle($this->renderOptions['title'] ?? $this->pageName);

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


    /** {@inheritdoc} */
    public function overrideConfig(array $options): void
    {
        // ✅ Apply overrides to the distinct, flat properties
        if (isset($options['options']) && is_array($options['options'])) {
            $this->options = array_merge($this->options, $options['options']);
        }
        if (isset($options['render_options']) && is_array($options['render_options'])) {
            $this->renderOptions = array_merge($this->renderOptions, $options['render_options']);
        }
        if (isset($options['layout']) && is_array($options['layout'])) {
            // Note: Merging layouts can be complex. For simplicity, this replaces.
            // If deeper merging is needed (e.g., merging fields within sections),
            // a dedicated layout merging service/method would be required.
            $this->layout = $options['layout'];
        }

        // ✅ Allow controllers to override 'fields' directly, consistent with setFocus()
        if (isset($options['fields']) && is_array($options['fields'])) {
            // Use setFields() to ensure these new fields are validated against the registry
            $this->setFields($options['fields']);
        }

        // Clean up after applying controller overrides
        // ✅ Always call the central validation method to ensure consistency
        $this->filterAndValidateViewStructure();
    }


    /**
     * Orchestrates the validation and fixing of the view's fields and layout.
     *
     * This method should be called after initial configuration in setFocus()
     * and after applying any overrides in overrideConfig() to ensure the
     * internal state of fields and layout is consistent and valid.
     *
     * @return void
     */
    protected function filterAndValidateViewStructure(): void // ✅ This method is correct as previously proposed
    {
        // 1. Get the current fields (which would have been validated by setFields())
        $currentFields = $this->getFields();
        $currentLayout = $this->getLayout();

        // 2. Re-validate current fields against the registry.
        // This ensures $this->fields is clean and reflects only valid fields.
        // It's technically redundant if setFields() was the last setter, but harmless and ensures robustness.
        $validatedFields = $this->fieldRegistryService->filterAndValidateFields(
            $currentFields,
            $this->pageKey,
            $this->pageEntity
        );

        // 3. Validate and fix the layout based on the *validated* fields
        $validatedLayout = $this->validateAndFixLayoutFields($currentLayout, $validatedFields);

        // 4. Update the internal properties with the validated results
        $this->fields = $validatedFields; // Direct assignment after re-validation
        $this->layout = $validatedLayout;
    }


    /**
     * Validates the layout configuration against the available fields and fixes it by
     * removing any references to non-existent fields or empty sections.
     *
     * @param array<string, mixed> $layout The raw layout configuration.
     * @param array<string> $availableFields An array of field names that are considered valid.
     * @return array<string, mixed> The validated and fixed layout configuration.
     */
    protected function validateAndFixLayoutFields(array $layout, array $availableFields): array
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
                    $this->logger->warning("Removed empty section at index {$secId} from view layout - ERR-VIEW90");
                }
            } else {
                // If a section has no 'fields' key or it's not an array, it's invalid.
                unset($layout[$secId]);
                $this->logger->warning("Removed invalid or empty section at index {$secId} ' .
                                       'from view layout - ERR-VIEW91");
            }
        }
        unset($section); // Unset reference to avoid unexpected behavior.

        // Re-index sections array if any were removed to ensure a clean array.
        return array_values($layout);
    }
}
