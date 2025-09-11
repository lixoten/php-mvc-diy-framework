<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

/**
 * @var array $data
 * @var string $message
 */

// Extract the exception from data
$exception = $data['exception'] ?? null;
$isRecordNotFound = $exception instanceof \Core\Exceptions\RecordNotFoundException;
?>
<div class="error-container">
    <div class="error-header">
        <h1>Page Not Found</h1>

        <div class="error-message">
            <?php if ($isRecordNotFound) : ?>
                <h3><?= htmlspecialchars($message ?? 'The requested record could not be found.') ?></h3>

                <?php if ($entityType = $exception->getEntityType()): ?>
                    <p>Looking for: <?= htmlspecialchars(ucfirst($entityType)) ?></p>
                <?php endif; ?>

                <?php if ($entityId = $exception->getEntityId()): ?>
                    <p>ID: <?= htmlspecialchars((string)$entityId) ?></p>
                <?php endif; ?>
            <?php else : ?>
                <h3>The page you requested could not be found.</h3>
                <p><?= htmlspecialchars($message ?? 'The URL may be mistyped or the page may have been moved.') ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="error-body">
        <div class="helpful-links">
            <h3>You might want to try:</h3>
            <ul>
                <?php if ($isRecordNotFound && !empty($exception->getHelpfulLinks())) : ?>
                    <?php foreach ($exception->getHelpfulLinks() as $label => $url) : ?>
                        <?php
                        // Create a default label if the key is not a string
                        if (!is_string($label)) {
                            $label = (string) $url;
                        }

                        // Ensure the URL is a string. If not, the link is invalid.
                        if (!is_string($url)) {
                            // Skip this item, as it's not a valid link
                            continue;
                        }
                        ?>
                        <li><a href="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($label) ?></a></li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <li><a href="javascript:history.back()">Go back to the previous page</a></li>
                    <li><a href="/">Return to Homepage</a></li>
                    <li><a href="/search">Search our site</a></li>
                    <!-- // TODO - Search does not exist yet -->
                <?php endif; ?>
            </ul>
        </div>

        <div class="error-explanation">
            <?php if ($isRecordNotFound) : ?>
                <p>The record you're looking for may have been deleted or moved.</p>
            <?php else : ?>
                <p>Double-check the URL for typos, or use the navigation above to find what you're looking for.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (app()->isDebug()) : ?>
        <div class="debug-info">
            <h3>Debug Information (404 - Not Found)</h3>
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
