<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\EmailServiceInterface;
use Core\Interfaces\ConfigInterface;
use Core\View;
use Psr\Log\LoggerInterface;

class MailgunEmailService implements EmailServiceInterface
{
    private ConfigInterface $config;
    private LoggerInterface $logger;
    private View $view;
    private ?string $lastError = null;

    /**
     * Constructor
     */
    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        View $view
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->view = $view;
    }

    /**
     * Send an email
     *
     * @param string|array $to Recipient email or array of emails
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param array $options Additional options (cc, bcc, replyTo, attachments)
     * @return bool Whether the email was sent successfully
     */
    public function send($to, string $subject, string $body, array $options = []): bool
    {
        $recipients = is_array($to) ? implode(', ', $to) : $to;

        // Get email config for the current environment
        $emailConfig = $this->config->get('email');
        if (empty($emailConfig)) {
            $this->lastError = "Email configuration missing";
            $this->logger->error($this->lastError);
            return false;
        }

        // Get Mailgun credentials
        //$fromEmail = $emailConfig['from_email'] ?? 'noreply@mvclixo.tv';
        $fromEmail = $emailConfig['from_email'] ?? 'lixoten@gmail.com';
        $fromName = $emailConfig['from_name'] ?? 'MVCLixo';

        // Check if providers config exists
        if (!isset($emailConfig['providers']) || !isset($emailConfig['providers']['mailgun'])) {
            $this->lastError = "Mailgun provider configuration missing";
            $this->logger->error($this->lastError);
            return false;
        }

        $mailgunConfig = $emailConfig['providers']['mailgun'];
        $apiKey = $mailgunConfig['api_key'] ?? '';
        $domain = $mailgunConfig['domain'] ?? '';
        $baseUrl = $mailgunConfig['base_url'] ?? 'https://api.mailgun.net/v3'; // Fallback ok
        if (empty($apiKey) || empty($domain)) {
            $this->lastError = "Mailgun API key or domain missing";
            $this->logger->error($this->lastError);
            return false;
        }

        // Set up request data
        $postData = [
            'from' => "{$fromName} <{$fromEmail}>",
            'to' => $recipients,
            'subject' => $subject,
            'html' => $body
        ];

        // Add optional parameters
        if (!empty($options['cc'])) {
            $postData['cc'] = is_array($options['cc']) ? implode(', ', $options['cc']) : $options['cc'];
        }

        if (!empty($options['bcc'])) {
            $postData['bcc'] = is_array($options['bcc']) ? implode(', ', $options['bcc']) : $options['bcc'];
        }

        if (!empty($options['replyTo'])) {
            $postData['h:Reply-To'] = $options['replyTo'];
        }

        // Make API request to Mailgun
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "{$baseUrl}/{$domain}/messages");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "api:{$apiKey}");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $this->lastError = 'Curl error: ' . curl_error($ch);
            $this->logger->error('Email sending failed: ' . $this->lastError);
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        // Log the response
        if ($httpCode < 200 || $httpCode >= 300) {
            $this->lastError = "API error: HTTP {$httpCode} - {$response}";
            $this->logger->error('Email sending failed: ' . $this->lastError);
            return false;
        }

        $this->logger->info("Email sent successfully to {$recipients}");
        return true;
    }

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
    public function sendTemplate($to, string $subject, string $template, array $data = [], array $options = []): bool
    {
        try {
            // Get the template with the provided data
            $body = $this->view->getTemplate($template, $data);

            // Send the email with the rendered template
            return $this->send($to, $subject, $body, $options);
        } catch (\Exception $e) {
            $this->lastError = 'Template rendering error: ' . $e->getMessage();
            $this->logger->error('Email template rendering failed: ' . $this->lastError);
            return false;
        }
    }

    /**
     * Get the last error if any
     *
     * @return string|null The last error message or null if none
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
