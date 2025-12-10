<?php

declare(strict_types=1);

namespace App\Features\Contact\Form;

use App\Helpers\DebugRt;
use Core\Form\Constants\ErrorDisplay as CONST_ED;
use Core\Form\Constants\Layouts as CONST_L;
use Core\Form\Constants\SecurityLevels as CONST_SL;
use Core\Form\AbstractFormType;
use Core\Form\FormBuilderInterface;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Form\CaptchaAwareTrait;

/**
 * Contact form type
 */
class ContactFormType extends AbstractFormType
{
    use CaptchaAwareTrait;

    private ContactFieldRegistry $fieldRegistry;
    private CaptchaServiceInterface $captchaService;

    /**
     * Constructor
     */
    public function __construct(
        ContactFieldRegistry $fieldRegistry,
        CaptchaServiceInterface $captchaService,
    ) {
        $this->fieldRegistry = $fieldRegistry;
        $this->captchaService = $captchaService;
    }


    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'contact_form';
    }

    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $actionType = 'contact_index';
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
            'force_captcha' => true,
            'layout_type' => CONST_L::FIELDSETS,    // FIELDSETS / SECTIONS / SEQUENTIAL
            'security_level' => CONST_SL::LOW,      // HIGH / MEDIUM / LOW
            'error_display' => CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
            'html5_validation' => false,
            'css_form_theme_class' => "form-theme-christmas",
            'css_form_theme_file' => "christmas",
            'title_heading' => "Send us a message",
            'submit_text' => "Submit",
        ]);
        // Define default fields
        // $fieldNames = ['name', 'email', 'subject', 'message'];
        $fieldNames = ['name'];
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
            [
                'id' => 'personal_info',
                'title' => 'Personal Information',
                // 'fields' => ['name', 'email'],
                'fields' => ['name'],
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
}
