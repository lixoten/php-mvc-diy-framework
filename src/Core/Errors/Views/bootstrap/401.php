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
            <?php
                $traceInfo = htmlspecialchars($data['additionalContext']['trace']);
                $helpInfo  = $data['debugHelp']['helpInfo'] ?? 'Unauthorized access';
                $fileInfo  = htmlspecialchars($data['file'] ?? 'N/A');
                $lineInfo  = htmlspecialchars((string)($data['line'] ?? 'N/A'));
                $requestedMethodInfo = htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'N/A');
                $requestedUriInfo    = htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A');
                $userAgentInfo       = htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A');
            ?>
            <?= <<<HTML
            <div class="card-footer bg-light">
                <h5>Debug Information (401 - Unauthorized)</h5>
                <p>File: $fileInfo</p>
                <p>Line: $lineInfo</p>
                <p>Debug Help: $helpInfo</p>
                <p>Request Method: $requestedMethodInfo</p>
                <p>Request URI: $requestedUriInfo</p>
                <p>User Agent: $userAgentInfo</p>
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