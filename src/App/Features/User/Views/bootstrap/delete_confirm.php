<?php

declare(strict_types=1);

use App\Helpers\LinkBuilder;

/**
 * Delete Confirmation Page Template for User entity
 *
 * @var string $title - Page title (e.g., "Confirm Delete: John Doe")
 * @var int $recordId - ID of the user record to be deleted
 * @var string $recordTitle - Username of the record for confirmation message
 * @var string $deleteUrl - URL for the POST delete action (e.g., /user/{id}/delete)
 * @var string $cancelUrl - URL to redirect to if deletion is cancelled (e.g., /user/list)
 * @var string $csrfToken - CSRF token for the delete form
 */
?>

<div class="container mt-5">
    <div class="card border-danger">
        <div class="card-header bg-danger text-white">
            <h1 class="card-title mb-0"><?= htmlspecialchars($title) ?></h1>
        </div>
        <div class="card-body">
            <p class="card-text">
                You are about to delete the user: <strong><?= htmlspecialchars($recordTitle) ?></strong> (ID: <?= htmlspecialchars((string)$recordId) ?>).
            </p>
            <p class="card-text">
                This action cannot be undone. Are you absolutely sure you want to proceed?
            </p>

            <form action="<?= htmlspecialchars($deleteUrl) ?>" method="POST" class="d-inline-block me-2">
                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$recordId) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-1"></i> Confirm Delete
                </button>
            </form>

            <?= LinkBuilder::generateButtonLink(
                url: $cancelUrl,
                text: 'Cancel',
                attributes: ['class' => 'btn btn-secondary']
            ) ?>
        </div>
    </div>
</div>