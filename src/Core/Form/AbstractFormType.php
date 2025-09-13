<?php

declare(strict_types=1);

namespace Core\Form;

use App\Enums\Url;
use App\Helpers\DebugRt;
use Core\Services\FieldRegistryService;
use Core\Form\Constants\ErrorDisplay as CONST_ED;
use Core\Form\Constants\Layouts as CONST_L;
use Core\Form\Constants\SecurityLevels as CONST_SL;
use Core\Interfaces\ConfigInterface;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Services\ConfigService;

/**
 * Abstract base class for form types
 * Implements common functionality while allowing specific implementations in child classes
 */
abstract class AbstractFormType implements FormTypeInterface
{
    // public string $routeType = 'root';
    protected array $options = [];
    protected array $urlEnumArray;

    /**
     * Constructor
     */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
        protected CaptchaServiceInterface $captchaService,
        public readonly string $viewFocus = '',
        public readonly string $viewName = '',
    ) {
        $this->fieldRegistryService = $fieldRegistryService;
        $this->configService = $configService;
        $this->captchaService = $captchaService;

        $this->init();
    }




    /** {@inheritdoc} */
    public function validateFields(array $fields): array
    {
        if (!isset($fields) || !is_array($fields) || empty($fields)) {
            $this->logWarning("No Fields/Columns found. - ERR-DEV85");
        }

        $this->fieldRegistryService->setEntityName($this->viewFocus);
        $this->fieldRegistryService->setPageName($this->viewName);
        $validFields = $this->fieldRegistryService->filterAndValidateFields($fields);

        return $validFields;
    }



    /** {@inheritdoc} */
    public function getOptions(): array
    {
        return $this->options;
    }


    /** {@inheritdoc} */
    // public function buildForm(FormBuilderInterface $builder, array $options = []): void
    public function buildForm(FormBuilderInterface $builder): void
    {
        // Set Render Options for the builder
        $builder->setRenderOptions($this->options['render_options']);

        $fieldNames = $this->getFormFields();
        if ($this->applyCaptchaIfNeeded()) {
            $fieldNames[] = 'captcha';
        }

        // $actionType = 'login';
        // $captchaNeeded = $this->isCaptchaNeeded($actionType, $this->renderOptions);
        // if ($captchaNeeded) {
        //     $fieldNames[] = 'captcha';
        // }


        // Process each field
        foreach ($fieldNames as $name) {
            $columnDef = $this->fieldRegistryService->getFieldWithFallbacks($name);
            if ($columnDef && isset($columnDef['form'])) {
                $options = $columnDef['form'];
                if (isset($columnDef['label'])) {
                    $options['label'] = $columnDef['label']; 
                }
                $builder->add($name, $options);
            }
        }


        // if ($captchaNeeded) {
            // $this->renderOptions['captcha_required'] = $captchaNeeded;
            // $this->renderOptions['captcha_scripts'] = $this->captchaService->getScripts();
        // }

        // $layout = $this->generateLayout($fieldNames);
        $layout = $this->options['render_options']['layout'];
        $validatedLayout = $this->validateAndFixLayoutFields($layout, $fieldNames);
        $builder->setLayout($validatedLayout);
    }





    // /** {@inheritdoc} */
    // public function mergeFormOptions(array $options): void
    // {
    //     $this->options = array_replace_recursive($this->options, $options);
    // }




    // /** {@inheritdoc} */
    // public function getFormRenderOptions(): array
    // {
    //     return $this->options['render_options'];
    // }
    // /** {@inheritdoc} */
    // public function mergeFormRenderOptions(array $renderOptions): void
    // {
    //     $this->options['render_options'] = array_merge($this->options['render_options'], $renderOptions);
    // }


    // /** {@inheritdoc} */
    // public function setFormOptions(array $options): void
    // {
    //     $this->options = $options;
    // }



    // /** {@inheritdoc} */
    // public function setFormFields(array $listFields): void
    // {
    //     $validFields = $this->fieldRegistryService->filterAndValidateFields($listFields);
    //     $this->options['render_options']['form_fields'] = $validFields;
    // }






    // // /**
    // //  * Get client IP address from current request
    // //  */
    // // protected function getIpAddress(): string
    // // {
    // //     return $this->request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
    // // }

    // // /**
    // //  * Check if CAPTCHA is being forced via query parameter
    // //  */
    // // protected function isForcedCaptcha(): bool
    // // {
    // //     return (bool)($this->request->getQueryParams()['show_captcha'] ?? false);
    // // }


    ////////////////////////////////////////////////////////////////////////////////////
    // Private Methods /////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////


    private function init(): void
    {
        $securityConfig = $this->configService->get('security');
        if ($securityConfig === null) {
            throw new \RuntimeException('Fatal error: Required config file "security.php" is missing.');
        }
        $forceCaptcha = $securityConfig['captcha']['force_captcha'] ?? false;


        ///////////////////////////////////////////////////////////////////////
        // Retrieve Default Config values
        ///////////////////////////////////////////////////////////////////////
        $defaultConfig  = $this->configService->get('view.form', []);
        $formTheme          = $defaultConfig['default_form_theme'] ?? "christmas";

        $defaultRenderOptions       = [
            'testFoo' => 'config value',
            'force_captcha'         => $forceCaptcha,   // ok
            'layout_type'           => $defaultConfig['render_options']['layout_type'] ?? CONST_L::SEQUENTIAL,
            'security_level'        => CONST_SL::LOW ?? 'low',      // HIGH / MEDIUM / LOW
            'error_display'         => $defaultConfig['render_options']['error_display'] ??  CONST_ED::SUMMARY,
            'html5_validation'      => $defaultConfig['render_options']['html5_validation'] ?? false,
            'css_form_theme_class'  => $this->configService->getConfigValue(
                'view',
                'form.themes.$formTheme.class'
            ) ?? 'form-theme-christmas',
            'css_form_theme_file'   => 'christmas',
            'form_heading'          => 'Create Record',
            'submit_text'           => $defaultConfig['render_options']['submit_text'] ?? 'Submit',
            'submit_class'          => $defaultConfig['render_options']['submit_class'] ?? 'btn btn-primary', // extra?
            'show_error_container'  => $defaultConfig['render_options']['show_error_container'] ?? false, // extra?
            'default_form_theme'    => $formTheme,  // extra??v// fixme
                        // ip_address
            'layout'                => [],
        ];
        $defaultFormFields       = [];
        ///////////////////////////////////////////////////////////////////////


        ///////////////////////////////////////////////////////////////////////
        // Retrieve View Config values
        ///////////////////////////////////////////////////////////////////////
        // Form View Defaults - These will be applied on top of the Form Defaults
        $viewName           = $this->viewName;
        $viewConfig         = $this->configService->get('view_options/' . $viewName); // loads "list_fields/posts.php"
        if ($viewConfig === null) {
            throw new \RuntimeException(
                "Fatal error: Required config file \"view_options/{$viewName}.php\" is missing."
            );
        }

        $viewRenderOptions      = $viewConfig['render_options'] ?? [];
        $viewFormFields         = $viewConfig['form_fields'] ?? [];
        ///////////////////////////////////////////////////////////////////////


        ///////////////////////////////////////////////////////////////////////
        // Merge default and view Config values
        // Except for List_field values, they replace if set
        ///////////////////////////////////////////////////////////////////////
        $finalRenderOptions = array_merge($defaultRenderOptions, $viewRenderOptions);
        if (!isset($viewFormFields) || !is_array($viewFormFields) || empty($viewFormFields)) {
            $finalFormFields   = $defaultFormFields;
        } else {
            $finalFormFields   = $viewFormFields;
        }


        // $this->setOptions($finalOptions);
        // $this->setPaginationOptions($finalPagination);
        $this->setRenderOptions($finalRenderOptions);
        $this->setFormFields($finalFormFields);
        // How you would use the methods
        //$formType->buildForm()
        // $validFields = $this->validateFields($finalOptions['render_options']['form_fields']);
        // $finalOptions['render_options']['form_fields'] =  $validFields;
        // $this->setRenderOptions($finalOptions['render_options']);

        //DebugRt::j('1', '', $securityConfig);
    }



    /** {@inheritdoc} */
    public function getRenderOptions(): array
    {
        return $this->options['render_options'];
    }
    /** {@inheritdoc} */
    public function setRenderOptions(array $renderOptions): void
    {
        $this->options['render_options'] = $renderOptions;
    }



    /** {@inheritdoc} */
    public function getFormFields(): array
    {
        return $this->options['form_fields'];
    }

    /** {@inheritdoc} */
    public function setFormFields(array $formFields): void
    {
        $this->fieldRegistryService->setPageName($this->viewName);
        $this->fieldRegistryService->setEntityName($this->viewFocus);
        $validFields = $this->fieldRegistryService->filterAndValidateFields($formFields);
        $this->options['form_fields'] = $validFields;
    }





    /**
     * Extension point for child forms to determine if CAPTCHA should be applied.
     *
     * Child classes should override this method to implement custom CAPTCHA logic.
     * If CAPTCHA is needed, this method should return true; otherwise, false.
     *
     * @return bool True if CAPTCHA should be applied, false otherwise.
     */
    private function applyCaptchaIfNeeded(): bool // fixme protected
    {
        return false;
    }


    /**
     * Validate and fix layout so only existing fields are used
     */
    private function validateAndFixLayoutFields(array $layout, array $availableFields): array
    {
        // For section layout
        if (!empty($layout)) {
            foreach ($layout as $secId => &$section) {
                if (isset($section['fields'])) {
                    $invalidFields = [];
                    $section['fields'] = array_filter(
                        $section['fields'],
                        function ($field) use ($availableFields, &$invalidFields) {
                            $isValid = in_array($field, $availableFields);
                            if (!$isValid) {
                                $invalidFields[] = $field;
                            }
                            return $isValid;
                        }
                    );

                    if (!empty($invalidFields)) {
                        $this->logWarning(
                            "Removed invalid fields from section {$secId}: " .
                            implode(', ', $invalidFields) . ' - ERR-DEV89'
                        );
                    }

                    // If no fields left in this section, remove it
                    if (empty($section['fields'])) {
                        unset($layout[$secId]);
                        $this->logWarning("Removed empty section at index {$secId} - ERR-DEV90");
                    }
                } else {
                     unset($layout[$secId]);
                     $this->logWarning("Removed empty section at index {$secId} - ERR-DEV91");
                }
            }

            // Re-index sections array if any were removed
            if (isset($layout)) {
                $layout = array_values($layout);
            }
        }
        return $layout;
    }


    /**
     * Log a warning message in development mode
     */
    private function logWarning(string $message): void
    {
        if ($_ENV['APP_ENV'] === 'development') {
            trigger_error("Form Warning: {$message}", E_USER_WARNING);
        }

        // Always log to system log
        error_log("Form Warning: {$message}");
    }
}
