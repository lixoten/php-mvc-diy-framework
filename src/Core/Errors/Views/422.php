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
        <h1>Validation Error</h1>

        <div class="error-message">
            <h3>The data you submitted could not be processed.</h3>
            <p><?= htmlspecialchars($message ?? 'Please check the errors below and try again.') ?></p>
        </div>
    </div>

    <div class="error-body">
        <?php if (isset($data['exception']) && $data['exception'] instanceof \Core\Exceptions\ValidationException) : ?>
            <div class="validation-errors">
                <h3>Please fix the following errors:</h3>
                <?php if ($data['exception']->hasErrors()) : ?>
                    <ul class="error-list">
                        <?php foreach ($data['exception']->getErrors() as $field => $error) : ?>
                            <li>
                                <strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $field))) ?>:</strong>
                                <?= htmlspecialchars($error) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="helpful-links">
            <h3>You might want to try:</h3>
            <ul>
                <li><a href="javascript:history.back()">Go back and fix the form</a></li>
                <li>Double-check all required fields are filled out</li>
                <li>Make sure email addresses are valid</li>
                <li>Check password requirements if applicable</li>
                <li><a href="/">Return to Homepage</a></li>
            </ul>
        </div>

        <div class="error-explanation">
            <p>This error occurs when the form data doesn't meet our validation requirements.</p>
            <p>All highlighted fields must be corrected before you can continue.</p>
        </div>
    </div>

    <?php if (app()->isDebug()) : ?>
        <div class="debug-info">
            <h3>Debug Information (422 - Unprocessable Entity)</h3>
            <p>File: <?= htmlspecialchars($data['file'] ?? 'N/A') ?></p>
            <p>Line: <?= htmlspecialchars((string)($data['line'] ?? 'N/A')) ?></p>
            <p>Debug Help: <?= htmlspecialchars($data['debugHelp'] ?? 'Validation failed') ?></p>
            <p>Request Method: <?= htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A') ?></p>
            <?php if (isset($data['exception']) && $data['exception'] instanceof \Core\Exceptions\ValidationException) : ?>
                <p>Validation Errors Count: <?= count($data['exception']->getErrors()) ?></p>
            <?php endif; ?>
            <?php if (isset($data['trace'])) : ?>
                <pre><?= htmlspecialchars($data['trace']) ?></pre>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>