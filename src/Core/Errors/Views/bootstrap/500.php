<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $data
 * @var string $message
 */

use App\Helpers\DebugRt;

?>
<div class="container py-5">
    <div class="card border-danger mb-4">
        <div class="card-header bg-danger text-white">
            <h1 class="mb-0">Something Went Wrong</h1>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <h3>We encountered a problem processing your request.</h3>
                <p><?= htmlspecialchars($message ?? 'An unexpected error occurred.') ?></p>
            </div>
            <div class="mb-4">
                <h4>You might want to try:</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <a href="javascript:history.back()">Go back to the previous page</a>
                    </li>
                    <li class="list-group-item">
                        <a href="/">Return to Homepage</a>
                    </li>
                    <li class="list-group-item">
                        <a href="/contact">Contact Support</a>
                    </li>
                </ul>
            </div>
            <div>
                <p>The system has logged this error and our team will look into it.</p>
                <p>Error Reference: <?= uniqid('ERR-') ?></p>
            </div>
        </div>
        <?php if (app()->isDebug()) : ?>
            <div class="card-footer bg-light">
                <h5>Debug Information (500 - Internal Server Error)</h5>
                <p>File: <?= htmlspecialchars($data['file'] ?? 'N/A') ?></p>
                <p>Line: <?= htmlspecialchars((string)($data['line'] ?? 'N/A')) ?></p>
                <p>Debug Help: <?= htmlspecialchars($data['debugHelp'] ?? 'Internal server error') ?></p>
                <?php if (isset($data['trace'])) : ?>
                    <pre><?= htmlspecialchars($data['trace']) ?></pre>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>