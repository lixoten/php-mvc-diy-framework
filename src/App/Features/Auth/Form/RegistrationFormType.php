<?php

declare(strict_types=1);

namespace App\Features\Auth\Form;

use App\Helpers\DebugRt;
use Core\Form\Constants\ErrorDisplay as CONST_ED;
use Core\Form\Constants\Layouts as CONST_L;
use Core\Form\Constants\SecurityLevels as CONST_SL;
// use App\Helpers\DebugRt;
use App\Features\ErrorDisplay as ED;
use App\Features\Layouts as L;
use App\Features\SecurityLevels as SL;
use Core\Form\AbstractFormType;
use Core\Form\CaptchaAwareTrait;
use Core\Form\FormBuilderInterface;
use Core\Security\Captcha\CaptchaServiceInterface;

/**
 * Registration form type
 */
class RegistrationFormType extends AbstractFormType
{
    use CaptchaAwareTrait;

    private RegistrationFormFieldRegistry $fieldRegistry;
    private CaptchaServiceInterface $captchaService;

    /**
     * Constructor
     */
    public function __construct(
        RegistrationFormFieldRegistry $fieldRegistry,
        CaptchaServiceInterface $captchaService,
    ) {
        $this->fieldRegistry = $fieldRegistry;
        $this->captchaService = $captchaService;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'register_form';
    }


    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $actionType = 'registration';
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
        $this->formRenderOptions = array_merge($options, [
            // 'force_captcha' => true,
            'layout_type' => CONST_L::SEQUENTIAL,    // FIELDSETS / SECTIONS / SEQUENTIAL
            'security_level' => CONST_SL::LOW,      // HIGH / MEDIUM / LOW
            'error_display' => CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
            'html5_validation' => false,
            'css_form_theme_class' => "form-theme-christmas",
            'css_form_theme_file' => "christmas",
            'form_heading' => "Send us a message",
            'submit_text' => "Submit",
        ]);
        // Define default fields
        $fieldNames = ['username', 'email', 'password', 'confirm_password'];
        ### Important!!! ##########################################################################

        $captchaNeeded = $this->isCaptchaNeeded($actionType, $this->formRenderOptions);
        if ($captchaNeeded) {
            $fieldNames[] = 'captcha';
        }

        // Process each field
        foreach ($fieldNames as $name) {
            $fieldDef = $this->fieldRegistry->get($name) ?? [];
            $builder->add($name, $fieldDef);
        }

        if ($captchaNeeded) {
            $this->formRenderOptions['captcha_required'] = $captchaNeeded;
            $this->formRenderOptions['captcha_scripts'] = $this->captchaService->getScripts();
        }

        $layout = $this->generateLayout($fieldNames);

        $validatedLayout = $this->validateAndFixLayoutFields($layout, $fieldNames);
        $builder->setLayout($validatedLayout);
    }

    /**
     * Generate a layout
     * Change id needed
     */
    private function generateLayout(array $fieldNames): array
    {
        $layout = [
            // [
            //     'fields' => $fieldNames
            // ],
            [
                'id' => 'message_info',
                'title' => 'Your Message',
                'fields' => $fieldNames,
                'divider' => true
            ]
        ];
        return $layout;
    }


    // /** {@inheritdoc} */
    // public function oldbuildForm(FormBuilderInterface $builder, array $options = []): void
    // {
    //     $captchaRequired = $this->isCaptchaRequired('registration', $options);

    //     // Define registration fields
    //     $fieldNames = ['username', 'email', 'password', 'confirm_password'];
    //     if ($options['captcha_required'] ?? false) {
    //         $fieldNames[] = 'captcha';
    //     }

    //     // Process each field
    //     foreach ($fieldNames as $name) {
    //         // If confirm_password, use confirmPassword method
    //         $registryName = ($name === 'confirm_password') ? 'confirmPassword' : $name;
    //         $fieldDef = $this->fieldRegistry->get($registryName) ?? [];

    //         // Add field to form
    //         $builder->add($name, $fieldDef);
    //     }

    //     // Store CAPTCHA scripts in form options if needed
    //     if ($captchaRequired) {
    //         $options['captcha_scripts'] = $this->captchaService->getScripts();
    //     }


    //     ### Important!!! - This is where we can override everything.
    //     $this->formRenderOptions = array_merge($options, [
    //         'layout_type' => L::FIELDSETS,
    //         // 'layout_type' => L::SECTIONS,
    //         // 'layout_type' => L::SEQUENTIAL,
    //         'security_level' => SL::LOW, // Set to high security
    //         'error_display' => ED::SUMMARY,
    //         'html5_validation' => false,
    //         'css_form_theme_class' => "form-theme-christmas",
    //         'css_form_theme_file' => "christmas",
    //         'form_heading' => "Send us a message",
    //         'submit_text' => "Submit"
    //     ]);
    //     ### Important!!! ##########################################################################
    //     // DebugRt::j('1', '', $options['layout_type']);

    //     $layout = $this->generateAppropriateLayout($fieldNames, $this->formRenderOptions['layout_type']);
    //     // DebugRt::j('1', '', $layout);
    //     $builder->setLayout($layout);
    // }

    /**
     * Provide default data for the form
     */
    public function getDefaultData(): array
    {
        return [
            'username' => '',
            'email' => '',
            'password' => '',
            'confirm_password' => ''
        ];
    }


    // /**
    //  * Generate appropriate layout based on specified type
    //  */
    // private function generateAppropriateLayout(array $fieldNames, string $layoutType): array
    // {
    //     //Debug::p($options['layout']);

    //     return match ($layoutType) {
    //         // L::FIELDSETS => $this->generateFieldsetLayout($fieldNames),
    //         // L::SECTIONS => $this->generateSectionLayout($fieldNames),
    //         L::SEQUENTIAL => $this->generateSequentialLayout($fieldNames),
    //         default =>  $this->generateSequentialLayout($fieldNames),
    //     };
    // }



    /**
     * Check if CAPTCHA is required for this form
     *
     * @param string $actionType  The form type identifier
     * @param array $options Form options array which may contain 'force_captcha' and 'ip_address'
     * @return bool
     */
    private function isCaptchaRequired(string $actionType, array $options = []): bool
    {
        $forceCaptcha = $options['force_captcha'] ?? false;
        $ipAddress = $options['ip_address'] ?? '0.0.0.0';

        return $this->captchaService->isEnabled() &&
            ($forceCaptcha || $this->captchaService->isRequired($actionType, $ipAddress));
    }
}
