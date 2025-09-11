<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array $data
 * @var string $message
 */
?>

<div class="error-container">
    <div class="error-header">
        <h1>Unauthorized Access</h1>

        <div class="error-message">
            <h3>You need to be logged in to access this page.</h3>
            <p><?= htmlspecialchars($message ?? 'Authentication is required to view this content.') ?></p>
        </div>
    </div>

    <div class="error-body">
        <div class="helpful-links">
            <h3>You might want to try:</h3>
            <ul>
                <li><a href="/login">Log in to your account</a></li>
                <li><a href="/register">Create a new account</a></li>
                <li><a href="javascript:history.back()">Go back to the previous page</a></li>
                <li><a href="/">Return to Homepage</a></li>
            </ul>
        </div>

        <div class="error-explanation">
            <p>This page requires authentication. Common reasons for this error:</p>
            <ul>
                <li>You are not logged in</li>
                <li>Your session has expired</li>
                <li>You don't have permission to access this resource</li>
                <li>Your account may have been deactivated</li>
            </ul>
        </div>
    </div>

    <?php if (app()->isDebug()) : ?>
        <div class="debug-info">
            <h3>Debug Information (401 - Unauthorized)</h3>
            <p>File: <?= htmlspecialchars($data['file'] ?? 'N/A') ?></p>
            <p>Line: <?= htmlspecialchars((string)($data['line'] ?? 'N/A')) ?></p>
            <p>Debug Help: <?= htmlspecialchars($data['debugHelp'] ?? 'Unauthorized access') ?></p>
            <p>Requested URI: <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') ?></p>
            <p>User Agent: <?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') ?></p>
            <?php if (isset($data['trace'])) : ?>
                <pre><?= htmlspecialchars($data['trace']) ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>