<?php

use App\Helpers\UiHelper;


/**
 * Home page template
 *
 * @var string $title - Page title
 * @var array $actionLinks - Array of action links with 'url' and 'text' keys
 */
?>
<h1><?= $title ?></h1>
<?= UiHelper::tableLinks($actionLinks, 4) ?>


<?= $additional_content ?>

<div class="card">
    <div class="card-body">
        Testing Navigation on testy logger page.
    </div>
</div>
