<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

/**
 * @var array $data
 * @var string $message
 */
?>
<div class="error-container">
    <div class="error-header">
        <h1>Something Went Wrong</h1>

        <div class="error-message">
            <h3>We encountered a problem processing your request.</h3>
            <p><?= htmlspecialchars($message ?? 'An unexpected error occurred.') ?></p>
        </div>
    </div>

    <div class="error-body">
        <div class="helpful-links">
            <h3>You might want to try:</h3>
            <ul>
                <li><a href="javascript:history.back()">Go back to the previous page</a></li>
                <li><a href="/">Return to Homepage</a></li>
                <li><a href="/contact">Contact Support</a></li>
            </ul>
        </div>

        <div class="error-explanation">
            <p>The system has logged this error and our team will look into it.</p>
            <p>Error Reference: <?= uniqid('ERR-') ?></p>
        </div>
    </div>

    <?php if (app()->isDebug()) : ?>
        <div class="debug-info">
            <h3>Debug Information (500 - Internal Server Error)</h3>
            <p>File: <?= htmlspecialchars($data['file'] ?? 'N/A') ?></p>
            <p>Line: <?= htmlspecialchars((string)($data['line'] ?? 'N/A')) ?></p>
            <p>Debug Help: <?= htmlspecialchars($data['debugHelp'] ?? 'Internal server error') ?></p>
            <?php if (isset($data['trace'])) : ?>
                <pre><?= htmlspecialchars($data['trace']) ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>