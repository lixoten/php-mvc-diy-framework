<?php

declare(strict_types=1);

namespace Core\Security\Captcha;

interface CaptchaServiceInterface
{
    /**
     * Check if CAPTCHA functionality is globally enabled
     *
     * @return bool True if CAPTCHA is enabled
     */
    public function isEnabled(): bool;

    /**
     * Check if CAPTCHA should be displayed for a specific action type and identifier
     *
     * @param string $actionType The action being performed (login, registration, etc.)
     * @param string|null $identifier The identifier (IP, user ID, email, etc.)
     * @return bool True if CAPTCHA should be shown
     */
    public function isRequired(string $actionType, ?string $identifier = null): bool;

    /**
     * Generate HTML for displaying the CAPTCHA
     *
     * @param string|null $formId Optional form ID for multiple CAPTCHAs on same page
     * @param array $options Provider-specific rendering options
     * @return string HTML for the CAPTCHA
     */
    public function render(string $formId = null, array $options = []): string;

    /**
     * Verify CAPTCHA response from user
     *
     * @param string $response The CAPTCHA response token from the client
     * @return bool True if verification succeeds
     */
    public function verify(string $response): bool;

    /**
     * Get client-side script tags needed for CAPTCHA functionality
     *
     * @return string HTML script tags
     */
    public function getScripts(): string;

    /**
     * Get site key for client-side rendering
     *
     * @return string The site key
     */
    public function getSiteKey(): string;
}
