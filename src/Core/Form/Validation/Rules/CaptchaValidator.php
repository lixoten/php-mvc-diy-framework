<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use App\Helpers\DebugRt;
use Core\Security\Captcha\CaptchaServiceInterface;

/**
 * Validator for Google reCAPTCHA verification
 */
class CaptchaValidator extends AbstractValidator
{
    private CaptchaServiceInterface $captchaService;
    private string $defaultMessage;

    /**
     * Constructor
     *
     * @param CaptchaServiceInterface $captchaService Service for captcha verification
     * @param string $defaultMessage Default error message
     */
    public function __construct(
        CaptchaServiceInterface $captchaService,
        string $defaultMessage = 'Failed security verification. Please try again.'
    ) {
        $this->captchaService = $captchaService;
        $this->defaultMessage = $defaultMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        // We don't use shouldSkipValidation here since CAPTCHA is always required
        // Get response from request context (this is important!)
        $request = $options['request'] ?? null;

        // If no request, we can't validate
        if (!$request) {
            return $this->getErrorMessage($options, 'Unable to verify security check.');
        }

        // Get the reCAPTCHA response from POST data
        $captchaResponse = $request->getParsedBody()['g-recaptcha-response'] ?? '';

        // Verify with the CAPTCHA service
        if (!$this->captchaService->verify($captchaResponse)) {
            return $this->getErrorMessage($options, $this->defaultMessage);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'captcha';
    }

        protected function getDefaultOptions(): array
    {
        DebugRt::j('1', '', 'boom');
        return [
            'required'  => null,
            'minlength'         => null,
            'maxlength'         => null,
            'pattern'           => null,

            'forbidden_words'    => ['1234', 'password'],
            'require_digit'     => false,
            'require_uppercase' => false,
            'require_lowercase' => false,
            'require_special'   => false,


            'required_message' => null,
            'minlength_message'         => null,
            'maxlength_message'         => null,
            'pattern_message'           => null,

            'forbidden_words_message'   => null,
            'require_digit_message'     => null,
            'require_uppercase_message' => null,
            'require_lowercase_message' => null,
            'invalid_message'           => null,
        ];
    }
}
