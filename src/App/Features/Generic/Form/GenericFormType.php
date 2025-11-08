<?php

declare(strict_types=1);

namespace App\Features\Generic\Form;

use App\Features\Generic\Form\GenericFieldRegistry;
use Core\Form\AbstractFormType;
use Core\Form\FormBuilderInterface;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Form\CaptchaAwareTrait;
use Core\Form\Constants\Layouts as CONST_L;
use Core\Form\Constants\SecurityLevels as CONST_SL;
use Core\Form\Constants\ErrorDisplay as CONST_ED;
use InvalidArgumentException;

class GenericFormType extends AbstractFormType
{
    use CaptchaAwareTrait;

    private GenericFieldRegistry $fieldRegistry;
    protected CaptchaServiceInterface $captchaService;

    // Sensible framework defaults for render options
    private array $defaultRenderOptions = [
        'force_captcha' => false,
        'layout_type' => CONST_L::SEQUENTIAL,
        'security_level' => CONST_SL::MEDIUM,
        'error_display' => CONST_ED::INLINE,
        'html5_validation' => true,
        'css_form_theme_class' => '', // Or get from global config
        'css_form_theme_file' => '',  // Or get from global config
        'form_heading' => 'Edit genContent',
        'submit_text' => 'Save',
    ];

    public function __construct(
        GenericFieldRegistry $fieldRegistry,
        CaptchaServiceInterface $captchaService
    ) {
        $this->fieldRegistry = $fieldRegistry;
        $this->captchaService = $captchaService;
    }

    public function getName(): string
    {
        // Generic name for the dynamic form type
        return 'generic_content_form';
    }

    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $actionType =  $options['content_type'];
        ##############################################################################


        // --- 1. Get Configuration ---
        // if (empty($options['content_type']) || empty($options['content_config']) || empty($options['content_config']['fields'])) {
        if (empty($options['content_type'])) {
            // throw new InvalidArgumentException('GenericFormType requires "content_type" and "content_config" with "fields" in options.');
            throw new InvalidArgumentException('GenericFormType requires "content_type" in options.');
        }
        $contentType = $options['content_type'];
        //$contentConfig = $options['content_config'];
        //$fieldDefinitions = $contentConfig['fields'];
        $actionType = $options['action_type'] ?? $contentType; // Use content type as fallback action type


        //DebugRt::j('1', '', $options);

        // Notes-: Security High/Medium/Low
        // Security High: Only validate CAPTCHA, not other fields.
        // - If CAPTCHA fails, return immediately so the user must complete the CAPTCHA before other validations happen.
        // Security Medium/Low: Validate CAPTCHA AND all other fields, then return the combined validation status.

        ### Important!!! - This is where we can override everything.
        // Notes-: - Most Inportant Options, these override everything. Config sets in FormFactory,
        // and Options in controller-action. In: "form = $this->formFactory->create("
        ###########################################################################################
        $this->formRenderOptions = array_merge($options, [
            'force_captcha' => false,
            'layout_type' => CONST_L::SEQUENTIAL,    // FIELDSETS / SECTIONS / SEQUENTIAL
            'security_level' => CONST_SL::LOW,      // HIGH / MEDIUM / LOW
            'error_display' => CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
            'html5_validation' => false,
            'css_form_theme_class' => "form-theme-christmas",
            'css_form_theme_file' => "christmas",
            'form_heading' => "ddEdit Record",
            'submit_text' => "ddSave",
        ]);
        // Define default fields
        // $fieldNames = ['name', 'email', 'subject', 'message'];
        //$fieldNames = ['title', 'content'];
        //$fieldNames = array_keys($fieldDefinitions);
        $entityType = $options['content_type'];
        $fieldNames = $this->fieldRegistry->getFieldsForEntity($entityType);
        ### Important!!! ##########################################################################

        // $captchaNeeded = $this->isCaptchaNeeded($actionType, $this->formRenderOptions);
        // if ($captchaNeeded) {
            // $fieldNames[] = 'captcha';
        // }

        // Process each field
        foreach ($fieldNames as $name) {
            // $fieldDef = $this->fieldRegistry->get($name) ?? [];
            // $fieldDef = $fieldDefinitions[$name];
            $fieldDef = $this->fieldRegistry->getForEntity($name, $entityType);
            $builder->add($name, $fieldDef);
        }

        // if ($captchaNeeded) {
            // $this->formRenderOptions['captcha_required'] = $captchaNeeded;
            // $this->formRenderOptions['captcha_scripts'] = $this->captchaService->getScripts();
        // }

        $layout = $this->generateLayout($fieldNames);

        $validatedLayout = $this->validateAndFixLayoutFields($layout, $fieldNames);
        $builder->setLayout($validatedLayout);
    }

    public function oldovercomplesbuildForm(FormBuilderInterface $builder, array $options = []): void
    {
        // --- 1. Get Configuration ---
        if (empty($options['content_type']) || empty($options['content_config']) || empty($options['content_config']['fields'])) {
            throw new InvalidArgumentException('GenericFormType requires "content_type" and "content_config" with "fields" in options.');
        }
        $contentType = $options['content_type'];
        $contentConfig = $options['content_config'];
        $fieldDefinitions = $contentConfig['fields'];
        $actionType = $options['action_type'] ?? $contentType; // Use content type as fallback action type

        // --- 2. Determine Render Options (Priority: Runtime > Content Config > Defaults) ---
        $contentTypeRenderOptions = $contentConfig['render_options'] ?? [];
        $this->formRenderOptions = array_merge(
            $this->defaultRenderOptions,
            $contentTypeRenderOptions,
            $options // Runtime options passed to buildForm override others
        );

        // --- 3. Determine Field Names & Handle Captcha ---
        $fieldNames = array_keys($fieldDefinitions);
        $captchaNeeded = $this->isCaptchaNeeded($actionType, $this->formRenderOptions);
        if ($captchaNeeded) {
            $fieldNames[] = 'captcha';
            // Ensure captcha definition exists if needed (could be added here or expected in config)
            if (!isset($fieldDefinitions['captcha'])) {
                 $fieldDefinitions['captcha'] = [ /* generic captcha definition */ ];
            }
        }

        // --- 4. Add Fields to Builder ---
        foreach ($fieldNames as $name) {
            if (!isset($fieldDefinitions[$name])) {
                 // Should not happen if captcha logic is correct, but good safeguard
                 $this->logWarning("Field definition missing for '{$name}' in content type '{$contentType}'. Skipping.");
                 continue;
            }
            $fieldDef = $fieldDefinitions[$name];

            // Special handling for captcha to inject service if needed by the field type/renderer
            if ($name === 'captcha' && $captchaNeeded) {
                 $fieldDef['captcha_service'] = $this->captchaService; // Inject service for renderer
            }

            $builder->add($name, $fieldDef);
        }

        // --- 5. Generate and Set Layout ---
        // Use layout from content config if provided, otherwise generate simple sequential
        $layoutConfig = $contentConfig['layout'] ?? null;
        $layout = $this->generateLayout($fieldNames, $layoutConfig);
        $validatedLayout = $this->validateAndFixLayoutFields($layout, $fieldNames);
        $builder->setLayout($validatedLayout);

        // --- 6. Store Final Render Options (needed by FormFactory/Form) ---
        // The AbstractFormType store $this->formRenderOptions automatically.
        // Ensure any modifications needed *after* captcha check are applied.
        if ($captchaNeeded) {
            $this->formRenderOptions['captcha_required'] = true;
            $this->formRenderOptions['captcha_scripts'] = $this->captchaService->getScripts();
        }
    }


    /**
     * Generate a layout
     * Change id needed
     */
    private function generateLayout(array $fieldNames): array
    {
        $layout = [
            [
                'id' => 'personal_info',
                'title' => 'Personal Information',
                // 'fields' => ['name', 'description'],
                'fields' => $fieldNames,
                // 'fields' => ['title'],
                'divider' => true
            ],
            // [
            //     'id' => 'message_info',
            //     'title' => 'Your Message',
            //     'fields' => ['subject', 'message'],
            //     'divider' => true
            // ]
        ];

        return $layout;
    }


    /**
     * Generate layout based on config or default to sequential.
     */
    private function xxxgenerateLayout(array $fieldNames, ?array $layoutConfig): array
    {
        if ($layoutConfig !== null && !empty($layoutConfig)) {
            // Use the provided layout config (assuming it's in the correct format)
            // Add validation/processing if needed
            return $layoutConfig;
        }

        // Default: simple sequential layout (adjust structure as needed for your renderer)
        return [
            [
                // 'id' => 'dynamic_section', // Optional generic ID
                // 'title' => $this->formRenderOptions['form_heading'] ?? 'Details', // Use form heading?
                'fields' => array_values(array_diff($fieldNames, ['captcha'])), // Exclude captcha from main layout?
                // 'divider' => false
            ],
            // Add captcha section separately if needed by layout structure
            // ...
        ];
    }
}
