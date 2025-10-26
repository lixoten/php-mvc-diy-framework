<?php

declare(strict_types=1);

/**
 * @var array<string, mixed> $data
 * @var string $message
 */

$exception = $data['exception'] ?? null;
$isRecordNotFound = $exception instanceof \Core\Exceptions\RecordNotFoundException;
?>
<div class="container py-5">
    <div class="card border-danger mb-4">
        <div class="card-header bg-danger text-white">
            <h1 class="mb-0">Page Not Found</h1>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <?php if ($isRecordNotFound) : ?>
                    <h3><?= htmlspecialchars($message ?? 'The requested record could not be found.') ?></h3>
                    <?php if ($entityType = $exception->getEntityType()) : ?>
                        <p>Looking for: <span class="fw-bold"><?= htmlspecialchars(ucfirst($entityType)) ?></span></p>
                    <?php endif; ?>
                    <?php if ($entityId = $exception->getEntityId()) : ?>
                        <p>ID: <span class="fw-bold"><?= htmlspecialchars((string)$entityId) ?></span></p>
                    <?php endif; ?>
                <?php else : ?>
                    <h3>The page you requested could not be found.</h3>
                    <p><?= htmlspecialchars($message ?? 'The URL may be mistyped or the page may have been moved.') ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-4">
                <h4>You might want to try:</h4>
                <ul class="list-group list-group-flush">
                    <?php if ($isRecordNotFound && !empty($exception->getHelpfulLinks())) : ?>
                        <?php foreach ($exception->getHelpfulLinks() as $label => $url) : ?>
                            <?php
                            if (!is_string($label)) {
                                $label = (string)$url;
                            }
                            if (!is_string($url)) {
                                continue;
                            }
                            ?>
                            <li class="list-group-item">
                                <a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($label) ?></a>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li class="list-group-item"><a href="javascript:history.back()">Go back to the previous page</a></li>
                        <li class="list-group-item"><a href="/">Return to Homepage</a></li>
                        <li class="list-group-item"><a href="/search">Search our site</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div>
                <?php if ($isRecordNotFound) : ?>
                    <p>The record you're looking for may have been deleted or moved.</p>
                <?php else : ?>
                    <p>Double-check the URL for typos, or use the navigation above to find what you're looking for.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php if (app()->isDebug()) : ?>
            <div class="card-footer bg-light">
                <h5>Debug Information (404 - Not Found)</h5>
                <p>File: <?= htmlspecialchars($data['file'] ?? 'N/A') ?></p>
                <p>Line: <?= htmlspecialchars((string)($data['line'] ?? 'N/A')) ?></p>
                <p>Debug Help: <?= htmlspecialchars($data['debugHelp'] ?? 'Resource not found') ?></p>
                <p>Requested URL: <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'N/A') ?></p>
                <?php if ($isRecordNotFound && $exception) : ?>
                    <p>Exception Type: <?= htmlspecialchars(get_class($exception)) ?></p>
                    <p>Entity Type: <?= htmlspecialchars($exception->getEntityType() ?? 'N/A') ?></p>
                    <p>Entity ID: <?= htmlspecialchars((string)($exception->getEntityId() ?? 'N/A')) ?></p>
                <?php endif; ?>
                <?php if (isset($data['trace'])) : ?>
                    <pre><?= htmlspecialchars($data['trace']) ?></pre>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>