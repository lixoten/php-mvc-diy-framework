<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Entities\User;
use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\EmailServiceInterface;
use Core\Interfaces\ConfigInterface;
use Psr\Http\Message\UriInterface;

class EmailNotificationService
{
    private EmailServiceInterface $emailService;
    private ConfigInterface $config;
    private string $baseUrl;
    private ?string $lastError = null;

    public function __construct(
        EmailServiceInterface $emailService,
        ConfigInterface $config
    ) {
        $this->emailService = $emailService;
        $this->config = $config;
        $this->baseUrl = $config->get('app.url'); // No Fallback needed: 'http://mvclixo.tv';
    }

    /**
     * Send a verification email
     */
    public function sendVerificationEmail(User $user, string $token, ?UriInterface $requestUri = null): bool
    {
        $siteName = $this->config->get('app.name'); // No Fallback needed : 'MVCLixo'

        $recipientEmail = $this->isProduction()
            ? $user->getEmail()
            : $this->config->get('email.test_email_recipient'); // No Fallback needed : 'lixoten@gmail.com';

        // Debug::p($recipientEmail);

        // Build verification URL
        $verificationUrl = $this->buildVerificationUrl($token, $requestUri);

        // Prepare email data
        $emailData = [
            'username' => $user->getUsername(),
            'verificationUrl' => $verificationUrl,
            'expiryHours' => 24,
            'siteName' => $siteName
        ];
        //DebugRt::p($emailData, 0, "--------------------");
        $result = $this->emailService->sendTemplate(
            $recipientEmail,
            'Verify Your Email Address',
            'Auth/verification_email',
            $emailData,
            ['aaa','ssss']
        );

        // Send the email
        if (!$result) {
            $this->lastError = $this->emailService->getLastError();
        }

        return $result;
    }

    /**
     * Send password reset email // TODO
     */
    public function sendPasswordResetEmail(User $user, string $token, ?UriInterface $requestUri = null): bool
    {
        // Similar implementation for password reset
        // Reuses the same patterns but with different template and URL path
        return true;
    }

    /**
     * Build a verification URL
     */
    private function buildVerificationUrl(string $token, ?UriInterface $requestUri = null): string
    {
        if ($requestUri) {
            return (string)$requestUri->withPath('/verify-email/verify')->withQuery("token=$token");
        }

        return "{$this->baseUrl}/verify-email/verify?token=$token";
    }

    /**
     * Check if we're in production environment
     */
    private function isProduction(): bool
    {
        return $this->config->get('app.env', 'development') === 'production';
    }

    /**
     * Get the last error message if any
     *
     * @return string|null The last error message or null if none
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
