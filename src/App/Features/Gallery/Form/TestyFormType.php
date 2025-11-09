<?php

declare(strict_types=1);

namespace App\Features\Testy\Form;

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
 * Testy form type
 */
class TestyFormType extends AbstractFormType
{
    //use CaptchaAwareTrait;

    private const VIEW_FOCUS    = 'testy';
    private const VIEW_NAME     = 'testy_edit';

    protected array $options = [];


    /**
     * Constructor
     */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
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
}
