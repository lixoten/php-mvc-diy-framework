<?php

declare(strict_types=1);

namespace App\Features\Auth\Form;

use App\Helpers\DebugRt;
use Core\Form\Constants\ErrorDisplay as CONST_ED;
use Core\Form\Constants\Layouts as CONST_L;
use Core\Form\Constants\SecurityLevels as CONST_SL;
use Core\Form\AbstractFormType;
use Core\Form\FormBuilderInterface;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Form\CaptchaAwareTrait;
use Core\Services\ConfigService;
use Core\Services\FieldRegistryService;

/**
 * Login form type
 */
class LoginFormType extends AbstractFormType
{
    use CaptchaAwareTrait;

    private const VIEW_FOCUS    = 'user';
    private const VIEW_NAME     = 'login_index';

    protected array $options = [];


    /**
     * Constructor
     */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigService $configService,
        protected CaptchaServiceInterface $captchaService,
    ) {
        $this->fieldRegistryService = $fieldRegistryService;
        $this->configService = $configService;
        $this->captchaService = $captchaService;

        parent::__construct(
            fieldRegistryService: $this->fieldRegistryService,
            captchaService: $this->captchaService,
            configService: $this->configService,
            viewFocus: static::VIEW_FOCUS,
            viewName: static::VIEW_NAME,
        );
    }

    // /** {@inheritdoc} */
    // public function getFormName(): string
    // {
    //     return 'login_form';
    // }


    /** {@inheritdoc} */
    public function xxbuildForm(FormBuilderInterface $builder, array $options = []): void
    {

        $actionType = 'login';
        ##############################################################################


        //DebugRt::j('1', '', $options);

        // Notes-: Security High/Medium/Low
        // Security High: Only validate CAPTCHA, not other fields.
        // - If CAPTCHA fails, return immediately so the user must complete the CAPTCHA before other validations happen.
        // Security Medium/Low: Validate CAPTCHA AND all other fields, then return the combined validation status.

        ### Important!!! - This is where we can override everything.
        // Notes-: - Most Inportant Options, these override everything. Config sets in FormFactory,
        // and Options in controller-action. In: "form = $this->formFactory->create("
        ###########################################################################################
        $this->renderOptionsxx = array_merge($options, [
            'force_captcha' => true,
            'layout_type' => CONST_L::SEQUENTIAL,    // FIELDSETS / SECTIONS / SEQUENTIAL
            'security_level' => CONST_SL::LOW,      // HIGH / MEDIUM / LOW
            'error_display' => CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
            'html5_validation' => false,
            'css_form_theme_class' => "form-theme-christmas",
            'css_form_theme_file' => "christmas",
            'form_heading' => "Send us a message",
            'submit_text' => "Submizzzt",
        ]);
        // Define default fields
        $fieldNames = ['username', 'password', 'remember'];
        ### Important!!! ##########################################################################

        // $captchaNeeded = $this->isCaptchaNeeded($actionType, $this->options['render_options']);
        // if ($captchaNeeded) {
        //     $this->options['render_options']['captcha_required'] = $captchaNeeded;
        //     $this->options['render_options']['captcha_scripts'] = $this->captchaService->getScripts();

        //     $fieldNames[] = 'captcha';
        // }
        if ($this->applyCaptchaIfNeeded()) {
            $fieldNames[] = 'captcha';
        }

        // // Process each field
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
            // $this->options['render_options']['captcha_required'] = $captchaNeeded;
            // $this->options['render_options']['captcha_scripts'] = $this->captchaService->getScripts();
        // }

        $builder->setRenderOptions($this->options['render_options']);


        $layout = $this->generateLayout($fieldNames);

        $validatedLayout = $this->validateAndFixLayoutFields($layout, $fieldNames);
        $builder->setLayout($validatedLayout);
    }

    /**
     * Check if CAPTCHA is needed and update render options accordingly.
     *
     * @return bool True if CAPTCHA is required and options updated, false otherwise.
     */
    protected function applyCaptchaIfNeeded(): bool
    {
        $actionType = 'login';
        $captchaNeeded = $this->isCaptchaNeeded($actionType, $this->options['render_options']);
        if ($captchaNeeded) {
            $this->options['render_options']['captcha_required'] = $captchaNeeded;
            $this->options['render_options']['captcha_scripts'] = $this->captchaService->getScripts();

            return true;
        }
        return false;
    }




    // /**
    //  * Generate a layout
    //  * Change id needed
    //  */
    // private function generateLayout(array $fieldNames): array
    // {
    //     $layout = [
    //         // [
    //         //     'fields' => $fieldNames
    //         // ],
    //         [
    //             'id' => 'message_info',
    //             'title' => 'Your Message',
    //             'fields' => $fieldNames,
    //             'divider' => true
    //         ]
    //     ];
    //     return $layout;
    // }
}
