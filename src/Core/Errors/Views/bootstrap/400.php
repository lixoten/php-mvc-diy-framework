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
            <h1 class="mb-0">Bad Request</h1>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <h3>Your request could not be processed.</h3>
                <p><?= htmlspecialchars($message ?? 'The request was invalid or malformed.') ?></p>
            </div>
            <div class="mb-4">
                <h4>You might want to try:</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <a href="javascript:history.back()">Go back and check your input</a>
                    </li>
                    <li class="list-group-item">
                        <a href="/">Return to Homepage</a>
                    </li>
                    <li class="list-group-item">Double-check the form data you submitted</li>
                    <li class="list-group-item">Make sure all required fields are filled out</li>
                    <li class="list-group-item">Clear browser cache or cookies</li>
                </ul>
            </div>
            <div>
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
            <div class="card-footer bg-light">
                <h5>Debug Information (400 - Bad Request)</h5>
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
</div>