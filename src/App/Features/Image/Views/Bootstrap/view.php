<?php

declare(strict_types=1);

use App\Enums\Url;
use App\Helpers\LinkBuilder;

/**
 * Testy View (Detail) page template
 *
 * @var string $title - Page title
 * @var string $renderedView - Rendered HTML from ViewRenderer
 * @var array<string, mixed> $actionLinks - Navigation links
 */
?>

<div class="container mt-4">
    <h1><?= htmlspecialchars($title) ?></h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <h3>Error:</h3>
            <p><?= htmlspecialchars($error) ?></p>
            <?php if (isset($trace)): ?>
                <pre><?= htmlspecialchars($trace) ?></pre>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <?php
        // Output the fully rendered View from the ViewRenderer
        echo $renderedView;
        ?>
    <?php endif; ?>

    <div class="mt-4">
        <?= LinkBuilder::generateButtonLink(
            Url::CORE_TESTY_LIST,
            showIcon: true,
            text: 'Back to List',
            attributes: ['class' => 'btn btn-secondary']
        ) ?>
    </div>
</div>