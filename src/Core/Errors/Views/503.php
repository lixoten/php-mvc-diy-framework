<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array $data
 */
?>
<div class="error-container">
    <div class="error-header">
        <h1>Service Temporarily Unavailable</h1>

        <div class="error-message">
            <h3>We're currently experiencing high traffic or performing maintenance.</h3>
            <p><?= htmlspecialchars($message ?? 'Our service is temporarily unavailable.') ?></p>
            <p><strong>Please try again in a few minutes.</strong></p>
        </div>
    </div>

    <div class="error-body">
        <div class="helpful-links">
            <h3>You might want to try:</h3>
            <ul>
                <li>Wait a few minutes and refresh this page</li>
                <li><a href="/">Return to Homepage</a></li>
                <li>Check back later - we're working to restore service</li>
            </ul>
        </div>

        <div class="error-explanation">
            <p>This is a temporary issue. Our team is working to restore normal service.</p>
            <p>If this problem persists, you can contact us for updates.</p>
        </div>
    </div>

    <?php if (app()->isDebug()) : ?>
        <div class="debug-info">
            <h3>Debug Information (503 - Service Unavailable)</h3>
            <p>File: <?= htmlspecialchars($data['file'] ?? 'N/A') ?></p>
            <p>Line: <?= htmlspecialchars((string)($data['line'] ?? 'N/A')) ?></p>
            <p>Debug Help: <?= htmlspecialchars($data['debugHelp'] ?? 'Service temporarily unavailable') ?></p>
            <?php if (isset($data['trace'])) : ?>
                <pre><?= htmlspecialchars($data['trace']) ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>