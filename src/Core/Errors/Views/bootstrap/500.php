<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $data
 * @var string $message
 */
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
                <!-- <= $data['additionalContext']['display'] ?? 'xxx' ?> -->
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
            <?php
                $traceInfo = htmlspecialchars($data['additionalContext']['trace']);
                $helpInfo  = $data['debugHelp']['helpInfo'] ?? 'Internal server error';
                $fileInfo  = htmlspecialchars($data['file'] ?? 'N/A');
                $lineInfo  = htmlspecialchars((string)($data['line'] ?? 'N/A'));
            ?>
            <?= <<<HTML
            <div class="card-footer bg-light">
                <h5>Debug Information (500 - Internal Server Error)</h5>
                <p>File: $fileInfo</p>
                <p>Line: $lineInfo</p>
                <p>Debug Help: $helpInfo</p>
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