<?php

use Core\Email\EmailTemplate;

// Extract variables
$username = $username ?? 'User';
$resetUrl = $resetUrl ?? '#';
$expiryHours = $expiryHours ?? 1;
$siteName = $siteName ?? 'MVCLixo';

// Build email content
$content = <<<HTML
<h2>Reset Your Password</h2>

<p>Hello {$username},</p>

<p>We received a request to reset your password for your {$siteName} account.
    To proceed with the password reset, please click the button below:</p>

<p style="text-align: center;">
    <a href="{$resetUrl}" class="button">Reset Password</a>
</p>

<p>This password reset link will expire in {$expiryHours} hour(s).</p>

<p>If you didn't request a password reset, you can safely ignore this email.</p>

<p>If the button doesn't work, you can also copy and paste the following URL into your browser:</p>

<p style="word-break: break-all; background-color: #f5f5f5; padding: 10px;
          border-radius: 4px; font-size: 12px;">{$resetUrl}
</p>

<p>Thank you,<br>
The {$siteName} Team</p>
HTML;

// Wrap content in email template
echo EmailTemplate::wrap($content, 'Reset Your Password');
