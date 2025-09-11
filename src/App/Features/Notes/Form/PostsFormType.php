<?php

declare(strict_types=1);

namespace App\Features\Posts\Form;

use App\Helpers\DebugRt;
// use Core\Form\Constants\ErrorDisplay as CONST_ED;
// use Core\Form\Constants\Layouts as CONST_L;
// use Core\Form\Constants\SecurityLevels as CONST_SL;
use Core\Form\AbstractFormType;
// use Core\Form\FormBuilderInterface;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Form\CaptchaAwareTrait;
use Core\Services\FieldRegistryService;

/**
 * Post form type
 */
class PostsFormType extends AbstractFormType
{
    use CaptchaAwareTrait;

    protected const FORM_TYPE = 'POSTS';
    protected const FORM_NAME = 'post_edit_form';
    protected FieldRegistryService $fieldRegistryService;
    private CaptchaServiceInterface $captchaService;
    protected array $options = [
        // 'default_sort_key'          => PostFields2::ID->value,
        // 'default_sort_direction'    => SortDirection::ASC->value,//'DESC',
        'render_options' => [
            // 'title' => 'list.posts.title 222',
            'form_fields' => [
                //'title', 'content', 'boo'
            ],
        ],
    ];


    /**
     * Constructor
     */
    public function __construct(
        FieldRegistryService $fieldRegistryService,
        CaptchaServiceInterface $captchaService,
    ) {
        $this->fieldRegistryService = $fieldRegistryService;
        $this->fieldRegistryService->setEntityName(static::FORM_TYPE);
        $this->fieldRegistryService->setPageName(static::FORM_NAME);
        $this->captchaService = $captchaService;

        parent::__construct(fieldRegistryService: $this->fieldRegistryService);
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


    /**
     * Generate a layout
     * Change id needed
     */
    private function generateLayoutxxxx(array $fieldNames): array
    {
        $layout = [
            [
                'id' => 'personal_info',
                'title' => 'Personal Information',
                'fields' => ['title', 'content'],
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
