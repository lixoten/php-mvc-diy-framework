<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Security\Captcha\CaptchaServiceInterface;

/**
 * Provides CAPTCHA functionality for forms
 */
trait CaptchaAwareTrait
{
    //private CaptchaServiceInterface $captchaService;

    /**
     * Check if CAPTCHA is required for this form
     *
     * @param string $actionType  The form type identifier
     * @param array $options Form options array which may contain 'force_captcha' and 'ip_address'
     * @return bool
     */
    protected function isCaptchaNeeded(string $actionType, array $options = []): bool
    {
        $forceCaptcha = $options['force_captcha'] ?? false;
        $ipAddress = $options['ip_address'] ?? '0.0.0.0';

        return $this->captchaService->isEnabled() &&
            ($forceCaptcha || $this->captchaService->isRequired($actionType, $ipAddress));
    }
}
