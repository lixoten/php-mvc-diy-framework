<?php

declare(strict_types=1);

namespace App\Features\Post\Form;

use App\Helpers\DebugRt;
use Core\Form\AbstractFormType;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Form\CaptchaAwareTrait;
use Core\Services\FieldRegistryService;
use Core\Form\Constants\ErrorDisplay as CONST_ED;
use Core\Form\Constants\Layouts as CONST_L;
use Core\Form\Constants\SecurityLevels as CONST_SL;
use Core\Interfaces\ConfigInterface;
use Core\Services\ConfigService;

/**
 * Post form type
 */
class PostFormType extends AbstractFormType
{
    //use CaptchaAwareTrait;

    private const VIEW_FOCUS    = 'post';
    private const VIEW_NAME     = 'post_edit';
    // protected string $viewFocus2;
    // protected string $viewName2;

    protected array $options = [];


    /**
     * Constructor
     */
    public function __construct(
        // string $viewFocus2,
        // string $viewName2,
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
        protected CaptchaServiceInterface $captchaService,
    ) {
        // $this->viewFocus2 = $viewFocus2;
        // $this->viewName2 = $viewName2;
        $this->fieldRegistryService = $fieldRegistryService;
        $this->configService = $configService;
        $this->captchaService = $captchaService;

        parent::__construct(
            // viewFocus2: $viewFocus2,
            // viewName2: $viewName2,
            fieldRegistryService: $this->fieldRegistryService,
            captchaService: $this->captchaService,
            configService: $this->configService,
            // viewFocus: static::VIEW_FOCUS,
            // viewName: static::VIEW_NAME,
        );
    }
}
