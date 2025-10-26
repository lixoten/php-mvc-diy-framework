<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

/**
 * Testy Index page template
 *
 * @var string $title - Page title
 */
?>
<div class="container">
    <h1><?= htmlspecialchars($title) ?></h1>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger">
            <h3>Error:</h3>
            <p><?= htmlspecialchars($error) ?></p>
            <?php if (isset($trace)) : ?>
                <pre><?= htmlspecialchars($trace) ?></pre>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <?= $list->render() ?>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?= BASE_URL ?>/testy" class="btn btn-primary">Back to Testy</a>
    </div>
</div>