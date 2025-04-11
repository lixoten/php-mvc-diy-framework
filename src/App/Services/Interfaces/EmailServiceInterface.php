<?php

// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\App\Services\Interfaces\EmailServiceInterface.php

declare(strict_types=1);

namespace App\Services\Interfaces;

interface EmailServiceInterface
{
    /**
     * Send an email
     *
     * @param string|array $to Recipient email or array of emails
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param array $options Additional options (cc, bcc, replyTo, attachments)
     * @return bool Whether the email was sent successfully
     */
    public function send($to, string $subject, string $body, array $options = []): bool;

    /**
     * Send an email using a template
     *
     * @param string|array $to Recipient email or array of emails
     * @param string $subject Email subject
     * @param string $template Template name/path
     * @param array $data Data to pass to the template
     * @param array $options Additional options (cc, bcc, replyTo, attachments)
     * @return bool Whether the email was sent successfully
     */
    public function sendTemplate($to, string $subject, string $template, array $data = [], array $options = []): bool;

    /**
     * Get the last error if any
     *
     * @return string|null The last error message or null if none
     */
    public function getLastError(): ?string;
}
