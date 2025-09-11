<?php

declare(strict_types=1);

// namespace App\Features\Albums\Form;
// namespace App\Features\Albums\Form;
namespace App\Features\Albums\Form;

// use App\Features\Albums\Form\AlbumsFieldRegistry;
use App\Features\Albums\Form\AlbumsFieldRegistry;
use App\Helpers\DebugRt;
use Core\Form\Constants\ErrorDisplay as CONST_ED;
use Core\Form\Constants\Layouts as CONST_L;
use Core\Form\Constants\SecurityLevels as CONST_SL;
use Core\Form\AbstractFormType;
use Core\Form\FormBuilderInterface;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Form\CaptchaAwareTrait;

/**
 * Album form type
 */
class AlbumsFormType extends AbstractFormType
{
    use CaptchaAwareTrait;

    private AlbumsFieldRegistry $fieldRegistry;
    private CaptchaServiceInterface $captchaService;

    // Sensible framework defaults for render options
    ### Important!!! - This is where we can override everything.
    // Notes-: Security High/Medium/Low
    // Security High: Only validate CAPTCHA, not other fields.
    // - If CAPTCHA fails, return immediately so the user must complete the CAPTCHA before other validations happen.
    // Security Medium/Low: Validate CAPTCHA AND all other fields, then return the combined validation status.
    // Notes-: - Most Important Options, these override everything. Config sets in FormFactory,
    // and Options in controller-action. In: "form = $this->formFactory->create("
    ###########################################################################################
    private array $defaultRenderOptions = [
        'force_captcha' => false,
        'layout_type' => CONST_L::SEQUENTIAL, //SEQUENTIAL, FIELDSETS
        'security_level' => CONST_SL::MEDIUM,
        'error_display' => CONST_ED::INLINE,
        'html5_validation' => true,
        'css_form_theme_class' => 'form-theme-christmas', // Or get from global config
        'css_form_theme_file' => 'christmas',  // Or get from global config
        'form_heading' => 'ddEdit ddAlbum',
        'submit_text' => 'ddSave',
    ];

    /**
     * Constructor
     */
    public function __construct(
        AlbumsFieldRegistry $fieldRegistry,
        CaptchaServiceInterface $captchaService,
    ) {
        $this->fieldRegistry = $fieldRegistry;
        $this->captchaService = $captchaService;
    }


    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'album_edit_form';
    }

    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options = []): void
    {
        $actionType = 'album_edit';
        ##############################################################################

        $this->formRenderOptions = array_merge(
            $options,
            $this->defaultRenderOptions,
        );


        // Define default fields
        // $fieldNames = ['name', 'email', 'subject', 'message'];
        $fieldNames = ['name', 'description'];
        ### Important!!! ##########################################################################

        // Process each field
        foreach ($fieldNames as $name) {
            $fieldDef = $this->fieldRegistry->get($name) ?? [];
            $builder->add($name, $fieldDef);
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
}
