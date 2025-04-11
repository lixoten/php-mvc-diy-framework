<?php

declare(strict_types=1);

namespace App\Services\Email;

use App\Helpers\DebugRt as Debug;
use App\Services\Interfaces\EmailServiceInterface;
use Core\Interfaces\ConfigInterface;
use Core\View;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Psr\Log\LoggerInterface;

class SMTPEmailService implements EmailServiceInterface
{
    private ConfigInterface $config;
    private LoggerInterface $logger;
    private View $view;
    private ?string $lastError = null;
    // private array $smtpConfig;
    // private string $fromEmail;
    // private string $fromName;

    public function __construct(ConfigInterface $config, LoggerInterface $logger, View $view)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->view = $view;
    }

    public function send($to, string $subject, string $body, array $options = []): bool
    {
        // Get email config for the current environment
        $emailConfig = $this->config->get('email');
        if (empty($emailConfig)) {
            $this->lastError = "Email configuration missing";
            $this->logger->error($this->lastError);
            return false;
        }

        // Check for required SMTP settings before attempting to send
        $requiredSettings = ['host', 'username', 'password', 'encryption', 'port'];
        foreach ($requiredSettings as $setting) {
            if (!isset($emailConfig['providers']['smtp'][$setting])) {
                $this->lastError = "Missing required SMTP setting: " . $emailConfig['providers']['smtp'][$setting];
                $this->logger->error($this->lastError);
                return false;
            }
        }


        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $emailConfig['providers']['smtp']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $emailConfig['providers']['smtp']['username'];
            $mail->Password = $emailConfig['providers']['smtp']['password'];
            $mail->SMTPSecure = $emailConfig['providers']['smtp']['encryption'];
            $mail->Port = (int)$emailConfig['providers']['smtp']['port'];

            // From
            $fromEmail = $emailConfig['from_email']; // No Fallback needed: 'noreply@mvclixo.tv';
            $fromName = $emailConfig['from_name']; // No Fallback needed: 'MVCLixo';
            $mail->setFrom($fromEmail, $fromName);

            // $to
            $mail->addAddress($to);

            // Reply To
            if (isset($options['reply_to'])) {
                $mail->addReplyTo($options['reply_to']);
            }

            // CC & BCC
            if (isset($options['cc'])) {
                foreach ((array)$options['cc'] as $cc) {
                    $mail->addCC($cc);
                }
            }

            if (isset($options['bcc'])) {
                foreach ((array)$options['bcc'] as $bcc) {
                    $mail->addBCC($bcc);
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            // Plain text alternative
            if (!isset($options['text']) && is_string($body)) {
                $mail->AltBody = strip_tags($body);
            } elseif (isset($options['text'])) {
                $mail->AltBody = $options['text'];
            }

            // Attachments
            if (isset($options['attachments'])) {
                foreach ((array)$options['attachments'] as $attachment) {
                    if (is_array($attachment)) {
                        $mail->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? '',
                            $attachment['encoding'] ?? 'base64',
                            $attachment['type'] ?? ''
                        );
                    } else {
                        $mail->addAttachment($attachment);
                    }
                }
            }

            return $mail->send();
        } catch (Exception $e) {
            $this->lastError = $mail->ErrorInfo;
            $this->logger->error('Email sending failed via SMTP: ' . $mail->ErrorInfo);
            return false;
        }
    }

    public function sendTemplate($to, string $subject, string $template, array $data = [], array $options = []): bool
    {
        try {
            // Get the template with the provided data
            $body = $this->view->getTemplate($template, $data);

            // Send the email with the rendered template
            return $this->send($to, $subject, $body, $options);
        } catch (\Exception $e) {
            $this->lastError = "Template rendering failed: " . $e->getMessage();
            $this->logger->error('Email template rendering failed: ' . $this->lastError);
            return false;
        }
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
