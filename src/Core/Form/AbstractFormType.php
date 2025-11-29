<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Services\FieldRegistryService;
use Core\Interfaces\ConfigInterface;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Services\FormConfigurationService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract base class for form types
 * Implements common functionality while allowing specific implementations in child classes
 */
abstract class AbstractFormType implements FormTypeInterface
{
    protected array $options = []; // For general form-wide options (e.g., default behavior flags)
    protected array $renderOptions = [];
    protected array $layout = [];
    protected array $hiddenFields = [];
    protected array $fields = [];

    public readonly string $pageKey;
    public readonly string $pageName;
    public readonly string $pageAction;
    public readonly string $pageFeature;
    public readonly string $pageEntity;

    /**
     * Constructor.
     *
     * @param FieldRegistryService $fieldRegistryService Service for field/column definitions
     * @param ConfigInterface $configService Configuration service
     * @param FormConfigurationService $formConfigService Form configuration service
     * @param LoggerInterface $logger Logger instance
     */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
        protected FormConfigurationService $formConfigService,
        protected LoggerInterface $logger,
        protected CaptchaServiceInterface $captchaService,
        protected ContainerInterface $container // ✅ ADD THIS LINE
    ) {
        // No manual property assignments - handled by 'protected' promotion
    }

    /** {@inheritdoc} */
    public function setFocus(
        string $pageKey,
        string $pageName,
        string $pageAction,
        string $pageFeature,
        string $pageEntity,
    ): void {
        $this->pageKey      = $pageKey;
        $this->pageName     = $pageName;
        $this->pageAction   = $pageAction;
        $this->pageFeature   = $pageFeature;
        $this->pageEntity    = $pageEntity;

        // ✅ Delegate configuration loading to FormConfigurationService
        $config = $this->formConfigService->loadConfiguration(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity,
        );

        // ✅ Apply loaded configuration to properties
        // Use ?? [] to ensure it's an array if not defined in config
        $this->options       = $config['options'] ?? []; // For general form options
        $this->renderOptions = $config['render_options'] ?? [];
        $this->layout        = $config['layout'] ?? [];
        $this->hiddenFields  = $config['hidden_fields'] ?? [];
        // $this->fields is populated by filterValidateFormFields()

        // ✅ Validate and clean fields based on layout
        $this->filterValidateFormFields();
    }

    /** {@inheritdoc} */
    public function getOptions(): array
    {
        return $this->options;
    }

    /** {@inheritdoc} */
    public function getRenderOptions(): array
    {
        return $this->renderOptions;
    }

    /** {@inheritdoc} */
    public function setRenderOptions(array $renderOptions): void
    {
        $this->renderOptions = $renderOptions;
    }

    /** {@inheritdoc} */
    public function mergeRenderOptions(array $renderOptions): void
    {
        $this->renderOptions = array_merge(
            $this->renderOptions,
            $renderOptions
        );
    }

    /** {@inheritdoc} */
    public function getFields(): array
    {
        return $this->fields;
    }

    /** {@inheritdoc} */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /** {@inheritdoc} */
    public function getLayout(): array
    {
        return $this->layout;
    }

    /** {@inheritdoc} */
    public function setLayout(array $layout): void
    {
        $this->layout = $layout;
    }

    /** {@inheritdoc} */
    public function getHiddenFields(): array
    {
        return $this->hiddenFields;
    }

    /** {@inheritdoc} */
    public function setHiddenFields(array $hiddenFields): void
    {
        $this->hiddenFields = $hiddenFields;
    }

    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder): void
    {
        // Set Render Options for the builder
        $builder->setRenderOptions($this->getRenderOptions());
        $builder->setLayout($this->getLayout());

        $fieldNames = $this->getFields();
        if ($this->applyCaptchaIfNeeded()) {
            $fieldNames[] = 'captcha';
        }

        // $actionType = 'login';
        // $captchaNeeded = $this->isCaptchaNeeded($actionType, $this->renderOptions);
        // if ($captchaNeeded) {
        //     $fieldNames[] = 'captcha';
        // }

        // Process each field
        foreach ($fieldNames as $fieldName) {
            $columnDef = $this->fieldRegistryService->getFieldWithFallbacks(
                $fieldName,
                $this->pageKey,
                $this->pageEntity
            );

            if ($columnDef && isset($columnDef['form'])) {
                $options = $columnDef['form'];
                $options['formatters'] = $columnDef['formatters'] ?? null;
                $options['validators'] = $columnDef['validators'] ?? null;
                

                if (
                    isset($options['type']) &&
                    in_array($options['type'], ['select', 'radio_group', 'checkbox_group'], true) &&
                    isset($options['options_provider'])
                ) {
                    [$serviceClass, $methodName] = $options['options_provider'];
                    $params = $options['options_provider_params'] ?? [];

                    try {
                        // Get service from container
                        $service = $this->container->get($serviceClass);

                        // Call provider method with parameters from config and potentially the current pageName
                        // ✅ CHANGE: Pass only the parameters from $params.
                        //    If getSelectChoices needs $pageName, it should be included in $options_provider_params.
                        $resolvedOptions = $service->$methodName(...array_values($params));

                        // Store the resolved options in the 'choices' key for the renderer
                        $options['choices'] = $resolvedOptions;

                        // Clean up provider keys (no longer needed by the renderer)
                        unset($options['options_provider'], $options['options_provider_params']);

                    } catch (\Throwable $e) {
                        $this->logger->error(sprintf(
                            'Error resolving options_provider for field "%s" (%s::%s): %s',
                            $fieldName,
                            $serviceClass,
                            $methodName,
                            $e->getMessage()
                        ));
                        // Fail gracefully: ensure 'options' key is an empty array if provider fails
                        $options['choices'] = [];
                    }
                }


                if (isset($options['region'])) {
                    // Formatter
                    if (isset($options['formatters']) && is_array($options['formatters'])) {
                        foreach ($options['formatters'] as &$formatter) {
                            if (is_array($formatter) && !isset($formatter['options']['region'])) {
                                $formatter['options']['region'] = $options['region'];
                            }
                        }
                        unset($formatter);
                    }
                    // Validator
                    if (isset($options['validators']) && is_array($options['validators'])) {
                        foreach ($options['validators'] as &$validator) {
                            if (is_array($validator) && !isset($validator['region'])) {
                                $validator['region'] = $options['region'];
                            }
                        }
                        unset($validator);
                    }
                }

                $builder->add($fieldName, $options);
            } else {
                $this->logger->warning('AbstractFormType: Field definition not found', [
                    'fieldName' => $fieldName,
                    'pageKey'  => $this->pageKey,
                    'pageEntity' => $this->pageEntity,
                ]);
            }
        }

        // if ($captchaNeeded) {
            // $this->renderOptions['captcha_required'] = $captchaNeeded;
            // $this->renderOptions['captcha_scripts'] = $this->captchaService->getScripts();
        // }

        // $layout = $this->generateLayout($fieldNames);
        // $layout = $this->options['render_options']['layout'];
        // $validatedLayout = $this->validateAndFixLayoutFields($layout, $fieldNames);
        // $builder->setLayout($validatedLayout);
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

        if (isset($options['hidden_fields']) && is_array($options['hidden_fields'])) {
            $this->hiddenFields = array_merge($this->hiddenFields, $options['hidden_fields']);
        }

        // Clean up after applying controller overrides
        $this->filterValidateFormFields();
    }


    ////////////////////////////////////////////////////////////////////////////////////
    // Private / Protected  Methods ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////
    protected function filterValidateFormFields(): void
    {
        $layout             = $this->getLayout();
        $viewHiddenFields   = $this->getHiddenFields();

        // 1. Collect all field names from layout sections
        $layoutFields = [];
        foreach ($layout as $section) {
            if (isset($section['fields']) && is_array($section['fields'])) {
                foreach ($section['fields'] as $field) {
                    // Skip commented-out fields (if present as strings with //)
                    if (is_string($field) && strpos($field, '//') !== 0) {
                        $layoutFields[] = $field;
                    }
                }
            }
        }

        // 2. Merge layout fields with explicit hidden form fields
        $allConfiguredFields = array_merge($layoutFields, $viewHiddenFields);


        // 3. Filter and Validate ALL Fields
        $validFields = $this->fieldRegistryService->filterAndValidateFields(
            $allConfiguredFields,
            $this->pageKey,
            $this->pageEntity
        );

        // 4. Validate Layout fields against against All fields and remove invalid ones from layout
        $validatedLayout = $this->validateAndFixLayoutFields($layout, $validFields);

        // 5. Filter hidden fields against the now-known valid fields
        // ✅ Ensure only valid fields remain in hiddenFields
        $validatedHiddenFields = array_values(array_intersect($viewHiddenFields, $validFields));

        // 5. Set Form Fields
        $this->setFields($validFields);

        // 6. Set the cleaned and validated properties
        $this->setFields($validFields);
        $this->setLayout($validatedLayout);
        $this->setHiddenFields($validatedHiddenFields);
    }

    /**
     * Extension point for child forms to determine if CAPTCHA should be applied.
     *
     * Child classes should override this method to implement custom CAPTCHA logic.
     * If CAPTCHA is needed, this method should return true; otherwise, false.
     *
     * @return bool True if CAPTCHA should be applied, false otherwise.
     */
    protected function applyCaptchaIfNeeded(): bool
    {
        return false;
    }

    /**
     * Validate and fix layout so only existing fields are used
     */
    protected function validateAndFixLayoutFields(array $layout, array $availableFields): array
    {
        // For section layout
        if (!empty($layout)) {
            foreach ($layout as $secId => &$section) {
                if (isset($section['fields'])) {
                    //$invalidFields = [];
                    $section['fields'] = array_filter(
                        $section['fields'],
                        function ($field) use ($availableFields, &$invalidFields) {
                            $isValid = in_array($field, $availableFields);
                            // if (!$isValid) {
                            //     $invalidFields[] = $field;
                            // }
                            return $isValid;
                        }
                    );

                    // if (!empty($invalidFields)) {
                    //     $this->logger->warning(
                    //         "Removed invalid fields from section {$secId}: " .
                    //         implode(', ', $invalidFields) . ' - ERR-DEV89'
                    //     );
                    // }

                    // If no fields left in this section, remove it
                    if (empty($section['fields'])) {
                        unset($layout[$secId]);
                        $this->logger->warning("Removed empty section at index {$secId} - ERR-DEV90");
                    }
                } else {
                     unset($layout[$secId]);
                     $this->logger->warning("Removed empty section at index {$secId} - ERR-DEV91");
                }
            }

            // Re-index sections array if any were removed
            if (isset($layout)) {
                $layout = array_values($layout);
            }
        }
        return $layout;
    }
}
