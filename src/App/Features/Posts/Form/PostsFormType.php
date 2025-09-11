<?php

declare(strict_types=1);

namespace App\Features\Posts\Form;

use App\Helpers\DebugRt;
use Core\Form\AbstractFormType;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Form\CaptchaAwareTrait;
use Core\Services\FieldRegistryService;
use Core\Form\Constants\ErrorDisplay as CONST_ED;
use Core\Form\Constants\Layouts as CONST_L;
use Core\Form\Constants\SecurityLevels as CONST_SL;

/**
 * Post form type
 */
class PostsFormType extends AbstractFormType
{
    use CaptchaAwareTrait;

    protected const FORM_TYPE = 'POSTS';
    protected const FORM_NAME = 'posts_edit';

    protected FieldRegistryService $fieldRegistryService;
    private CaptchaServiceInterface $captchaService;
    protected array $options = [];


    /**
     * Constructor
     */
    public function __construct(
        FieldRegistryService $fieldRegistryService,
        CaptchaServiceInterface $captchaService,
    ) {
        $this->fieldRegistryService = $fieldRegistryService;
        $this->captchaService = $captchaService;

        $this->fieldRegistryService->setEntityName(static::FORM_TYPE);
        $this->fieldRegistryService->setPageName(static::FORM_NAME);

        // parent::__construct(fieldRegistryService: $this->fieldRegistryService);
    }
}
