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
            <h1 class="mb-0">Unauthorized Access</h1>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <h3>You need to be logged in to access this page.</h3>
                <p><?= htmlspecialchars($message ?? 'Authentication is required to view this content.') ?></p>
            </div>
            <div class="mb-4">
                <h4>You might want to try:</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="/login">Log in to your account</a></li>
                    <li class="list-group-item"><a href="/register">Create a new account</a></li>
                    <li class="list-group-item"><a href="javascript:history.back()">Go back to the previous page</a></li>
                    <li class="list-group-item"><a href="/">Return to Homepage</a></li>
                </ul>
            </div>
            <div>
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
            <div class="card-footer bg-light">
                <h5>Debug Information (401 - Unauthorized)</h5>
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
</div>