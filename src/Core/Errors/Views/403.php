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
        <h1>Access Forbidden</h1>

        <div class="error-message">
            <h3>You don't have permission to access this resource.</h3>
            <p><?= htmlspecialchars($message ?? 'Access to this page is restricted.') ?></p>
        </div>
    </div>

    <div class="error-body">
        <div class="helpful-links">
            <h3>You might want to try:</h3>
            <ul>
                <li><a href="javascript:history.back()">Go back to the previous page</a></li>
                <li><a href="/">Return to Homepage</a></li>
                <li><a href="/contact">Contact support if you believe this is an error</a></li>
                <li>Check if you need to upgrade your account permissions</li>
            </ul>
        </div>

        <div class="error-explanation">
            <p>This error typically occurs when:</p>
            <ul>
                <li>Your account doesn't have the required permissions</li>
                <li>The resource is restricted to certain user roles</li>
                <li>You're trying to access admin-only content</li>
                <li>The page is temporarily restricted</li>
            </ul>
        </div>
    </div>

    <?php if (app()->isDebug()) : ?>
        <div class="debug-info">
            <h3>Debug Information (403 - Forbidden)</h3>
            <p>File: <?= htmlspecialchars($data['file'] ?? 'N/A') ?></p>
            <p>Line: <?= htmlspecialchars((string)($data['line'] ?? 'N/A')) ?></p>
            <p>Debug Help: <?= htmlspecialchars($data['debugHelp'] ?? 'Access forbidden') ?></p>
            <p>Requested URI: <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') ?></p>
            <p>User ID: <?= htmlspecialchars((string)($_SESSION['user_id'] ?? 'Not logged in')) ?></p>
            <p>User Role: <?= htmlspecialchars($_SESSION['user_role'] ?? 'N/A') ?></p>
            <?php if (isset($data['trace'])) : ?>
                <pre><?= htmlspecialchars($data['trace']) ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>