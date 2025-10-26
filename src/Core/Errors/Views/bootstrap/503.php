<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array<string, mixed> $data
 * @var string|null $message
 */
?>
<div class="container py-5">
    <div class="card border-warning mb-4">
        <div class="card-header bg-warning text-dark">
            <h1 class="mb-0">Service Temporarily Unavailable</h1>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <h3>We're currently experiencing high traffic or performing maintenance.</h3>
                <p><?= htmlspecialchars($message ?? 'Our service is temporarily unavailable.') ?></p>
                <p><strong>Please try again in a few minutes.</strong></p>
            </div>
            <div class="mb-4">
                <h4>You might want to try:</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Wait a few minutes and refresh this page</li>
                    <li class="list-group-item"><a href="/">Return to Homepage</a></li>
                    <li class="list-group-item">Check back later – we're working to restore service</li>
                </ul>
            </div>
            <div>
                <p>This is a temporary issue. Our team is working to restore normal service.</p>
                <p>If this problem persists, you can contact us for updates.</p>
            </div>
        </div>
        <?php if (app()->isDebug()) : ?>
            <div class="card-footer bg-light">
                <h5>Debug Information (503 - Service Unavailable)</h5>
                <p>File: <?= htmlspecialchars($data['file'] ?? 'N/A') ?></p>
                <p>Line: <?= htmlspecialchars((string)($data['line'] ?? 'N/A')) ?></p>
                <p>Debug Help: <?= htmlspecialchars($data['debugHelp'] ?? 'Service temporarily unavailable') ?></p>
                <?php if (isset($data['trace'])) : ?>
                    <pre><?= htmlspecialchars($data['trace']) ?></pre>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>