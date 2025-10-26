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
            <h1 class="mb-0">Access Forbidden</h1>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <h3>You don't have permission to access this resource.</h3>
                <p><?= htmlspecialchars($message ?? 'Access to this page is restricted.') ?></p>
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
                        <a href="/contact">Contact support if you believe this is an error</a>
                    </li>
                    <li class="list-group-item">
                        Check if you need to upgrade your account permissions
                    </li>
                </ul>
            </div>
            <div>
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
            <div class="card-footer bg-light">
                <h5>Debug Information (403 - Forbidden)</h5>
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
</div>