<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array<string, mixed> $data
 * @var string $message
 */

$fff = htmlspecialchars($data['additionalContext']['trace']);

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
                <!-- <h5>Debug Information (403 - Forbidden)</h5> -->
                <!-- <p>File: <= htmlspecialchars($data['file'] ?? 'N/A') ?></p> -->
                <!-- <p>Line: <= htmlspecialchars((string)($data['line'] ?? 'N/A')) ?></p> -->
                <!-- <p>Debug Help: <= htmlspecialchars($data['debugHelp']['devHelp'] ?? 'Access forbidden') ?></p> -->
                <!-- <p>Requested URI: <= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') ?></p> -->
                <!-- <p>User ID: <= htmlspecialchars((string)($_SESSION['user_id'] ?? 'Not logged in')) ?></p> -->
                <!-- <p>User Role: <= htmlspecialchars($_SESSION['user_role'] ?? 'N/A') ?></p> -->
                <!--
                <= <<<HTML
                <div style="background-color: #fff3cd; padding: 15px;">
                    <h3>Stack Trace</h3>
                    <pre>$fff </pre>
                </div>
                HTML
                ?> -->

            </div>
            <?php
                $traceInfo = htmlspecialchars($data['additionalContext']['trace']);
                $helpInfo  = $data['debugHelp']['helpInfo'] ?? 'Access forbidden';
                $fileInfo  = htmlspecialchars($data['file'] ?? 'N/A');
                $lineInfo  = htmlspecialchars((string)($data['line'] ?? 'N/A'));
                $requestedUriInfo = htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A');
                $userIdInfo   = htmlspecialchars((string)($_SESSION['user_id'] ?? 'Not logged in'));
                $userRoleInfo = htmlspecialchars($_SESSION['user_role'] ?? 'N/A');
            ?>
            <?= <<<HTML
            <div class="card-footer bg-light">
                <h5>Debug Information (403 - Forbidden)</h5>
                <p>File: $fileInfo</p>
                <p>Line: $lineInfo</p>
                <p>Debug Help: $helpInfo</p>
                <p>Requested URI: $requestedUriInfo</p>
                <p>User ID: $userIdInfo</p>
                <p>User Role: $userRoleInfo</p>
                <div style="background-color: #fff3cd; padding: 15px;">
                    <h3>Stack Trace</h3>
                    <pre>$traceInfo</pre>
                </div>
            </div>
            HTML
            ?>
        <?php endif; ?>
    </div>
</div>