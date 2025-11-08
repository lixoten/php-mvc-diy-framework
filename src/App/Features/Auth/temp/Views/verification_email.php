<?php

// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\App\Features\Auth\Views\email_templates\verification_email.php

use Core\Email\EmailTemplate;

// Extract variables
$username = $username ?? 'User';
$verificationUrl = $verificationUrl ?? '#';
$expiryHours = $expiryHours ?? 24;
$siteName = $siteName ?? 'MVCLixo';

// Build email content
$content = <<<HTML
<h2>Verify Your Email Address</h2>

<p>Hello {$username},</p>

<p>Thank you for registering with {$siteName}.
    To complete your registration and verify your email address, please click the button below:</p>

<p style="text-align: center;">
    <a href="{$verificationUrl}" class="button">Verify Email Address</a>
</p>

<p>This verification link will expire in {$expiryHours} hours.</p>

<p>If you didn't create an account, you can ignore this email.</p>

<p>If the button doesn't work, you can also copy and paste the following URL into your browser:</p>

<p style="word-break: break-all;
          background-color: #f5f5f5;
          padding: 10px; border-radius: 4px; font-size: 12px;">{$verificationUrl}</p>

<p>Thank you,<br>
The {$siteName} Team</p>
HTML;

// Wrap content in email template
echo EmailTemplate::wrap($content, 'Verify Your Email Address');
