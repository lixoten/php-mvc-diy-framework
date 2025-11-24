<?php

declare(strict_types=1);

// namespace App\Features\Testy\Form;
namespace Core\Form;

use App\Helpers\DebugRt;
use Core\Form\AbstractFormType;
use Core\Security\Captcha\CaptchaServiceInterface;
use Core\Services\FieldRegistryService;
use Core\Interfaces\ConfigInterface;
use Core\Services\FormConfigurationService;
use Psr\Log\LoggerInterface;

/**
 * Post form type
 */
class ZzzzFormType extends AbstractFormType
{
    //use CaptchaAwareTrait;
    //protected array $options = [];

    /**
     * Constructor
     */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
        protected FormConfigurationService $formConfigService,
        protected LoggerInterface $logger,
        protected CaptchaServiceInterface $captchaService,
    ) {
        parent::__construct(
            fieldRegistryService: $fieldRegistryService,
            configService: $configService,
            formConfigService: $formConfigService,
            logger: $logger,
            captchaService: $captchaService,
        );
    }
}
