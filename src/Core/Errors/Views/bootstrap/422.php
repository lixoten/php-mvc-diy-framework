<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array<string, mixed> $data
 * @var string $message
 */
?>
<div class="container py-5">
    <div class="card border-warning mb-4">
        <div class="card-header bg-warning text-dark">
            <h1 class="mb-0">Validation Error</h1>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <h3>The data you submitted could not be processed.</h3>
                <p><?= htmlspecialchars($message ?? 'Please check the errors below and try again.') ?></p>
            </div>
            <?php if (isset($data['exception']) && $data['exception'] instanceof \Core\Exceptions\ValidationException) : ?>
                <div class="mb-4">
                    <h4>Please fix the following errors:</h4>
                    <?php if ($data['exception']->hasErrors()) : ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($data['exception']->getErrors() as $field => $error) : ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $field))) ?>:</strong>
                                    <?= htmlspecialchars($error) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="mb-4">
                <h4>You might want to try:</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="javascript:history.back()">Go back and fix the form</a></li>
                    <li class="list-group-item">Double-check all required fields are filled out</li>
                    <li class="list-group-item">Make sure email addresses are valid</li>
                    <li class="list-group-item">Check password requirements if applicable</li>
                    <li class="list-group-item"><a href="/">Return to Homepage</a></li>
                </ul>
            </div>
            <div>
                <p>This error occurs when the form data doesn't meet our validation requirements.</p>
                <p>All highlighted fields must be corrected before you can continue.</p>
            </div>
        </div>
        <?php if (app()->isDebug()) : ?>
            <div class="card-footer bg-light">
                <h5>Debug Information (422 - Unprocessable Entity)</h5>
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
</div>