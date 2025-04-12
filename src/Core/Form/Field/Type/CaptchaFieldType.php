<?php

declare(strict_types=1);

namespace Core\Form\Field\Type;

use Core\Form\Field\Field;
use Core\Security\Captcha\CaptchaServiceInterface;

/**
 * CAPTCHA field type
 */
class CaptchaFieldType extends AbstractFieldType
{
    private CaptchaServiceInterface $captchaService;

    /**
     * Constructor
     *
     * @param CaptchaServiceInterface $captchaService
     */
    public function __construct(CaptchaServiceInterface $captchaService)
    {
        $this->captchaService = $captchaService;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'captcha';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return array_merge(parent::getDefaultOptions(), [
            'label' => 'Security Verification',
            'help_text' => 'Please complete the security check',
            'required' => true,
            'theme' => 'light',
            'size' => 'normal'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(string $name, array $options = []): Field
    {
        //$options['type'] = 'hidden';
        $field = parent::buildField($name, $options);

        // Store CAPTCHA service in the field's options for rendering
        $field->setOptions(array_merge($field->getOptions(), [
            'captcha_service' => $this->captchaService,
            'theme' => $options['theme'] ?? 'light',
            'size' => $options['size'] ?? 'normal'
        ]));

        return $field;
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $options = []): string
    {
        $siteKey = $this->captchaService->getSiteKey();
        $theme = $options['theme'] ?? 'light';
        $size = $options['size'] ?? 'normal';

        return sprintf(
            '<div class="g-recaptcha" data-sitekey="%s" data-theme="%s" data-size="%s"></div>',
            htmlspecialchars($siteKey),
            htmlspecialchars($theme),
            htmlspecialchars($size)
        );
    }

    public function xxxxrender(Field $field): string
    {
        // Get the site key directly from the service
        $siteKey = $this->captchaService->getSiteKey();

        // Get theme and size options from field options
        $options = $field->getOptions();
        $theme = $options['theme'] ?? 'light';
        $size = $options['size'] ?? 'normal';

        // Generate the reCAPTCHA HTML
        return '<div class="g-recaptcha" data-sitekey="' . htmlspecialchars($siteKey) . '"' .
               ' data-theme="' . htmlspecialchars($theme) . '"' .
               ' data-size="' . htmlspecialchars($size) . '"></div>';
    }
}
