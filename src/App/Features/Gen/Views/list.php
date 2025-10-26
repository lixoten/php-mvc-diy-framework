<?php

declare(strict_types=1);

use App\Enums\Url;
use App\Helpers\DebugRt;
use App\Helpers\LinkBuilder;
use App\Helpers\UiHelper;

// $helperObj = new UiHelper();
// $linkList = $helperObj->ulLinks($actionLinks);

/**
 * Testy Index page template
 *
 * @var string $title - Page title
 * @var ListView $postList - List View
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
        <?= LinkBuilder::generateButtonLink(
            Url::CORE_TESTY,
            showIcon: false,
            text: 'Back to Testy',
            attributes: ['class' => "btn btn-primary"]
            // attributes: ['class' =>  Url::CORE_TESTY->class()]
        ) ?>
    </div>
</div>
