<?php

declare(strict_types=1);

namespace App\Features\Testys\Form;

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
class TestysFormType extends AbstractFormType
{
    //use CaptchaAwareTrait;

    private const VIEW_FOCUS    = 'testys';
    private const VIEW_NAME     = 'testys_edit';

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
