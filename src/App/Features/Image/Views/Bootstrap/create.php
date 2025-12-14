<?php

declare(strict_types=1);

use App\Helpers\UiHelper;

/**
 * Testy Index page template
 *
 * @var string $title - Page title
 * @var array $actionLinks - Array of action links with 'url' and 'text' keys
 */
?>
<h1><?= htmlspecialchars($title) ?></h1>
<?= UiHelper::tableLinks($actionLinks, 4) ?>

<div class="card">
    <div class="card-body">
        Testing Navigation on CREATE Record page.
    </div>
</div>