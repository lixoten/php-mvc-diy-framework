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
        <h1>Bad Request</h1>

        <div class="error-message">
            <h3>Your request could not be processed.</h3>
            <p><?= htmlspecialchars($message ?? 'The request was invalid or malformed.') ?></p>
        </div>
    </div>

    <div class="error-body">
        <div class="helpful-links">
            <h3>You might want to try:</h3>
            <ul>
                <li><a href="javascript:history.back()">Go back and check your input</a></li>
                <li><a href="/">Return to Homepage</a></li>
                <li>Double-check the form data you submitted</li>
                <li>Make sure all required fields are filled out</li>
                <li>Clear browser cache or cookies</li>
            </ul>
        </div>

        <div class="error-explanation">
            <p>This usually happens when:</p>
            <ul>
                <li>Required form fields are missing</li>
                <li>The data format is incorrect</li>
                <li>Invalid characters were used</li>
                <li>The request size is too large</li>
            </ul>
        </div>
    </div>

    <?php if (app()->isDebug()) : ?>
        <div class="debug-info">
            <h3>Debug Information (400 - Bad Request)</h3>
            <p>File: <?= htmlspecialchars($data['file'] ?? 'N/A') ?></p>
            <p>Line: <?= htmlspecialchars((string)($data['line'] ?? 'N/A')) ?></p>
            <p>Debug Help: <?= htmlspecialchars($data['debugHelp'] ?? 'Bad request') ?></p>
            <p>Request Method: <?= htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A') ?></p>
            <p>Request URI: <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') ?></p>
            <?php if (isset($data['trace'])) : ?>
                <pre><?= htmlspecialchars($data['trace']) ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>