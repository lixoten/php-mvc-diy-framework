<?php

declare(strict_types=1);

use App\Helpers\DebugRt;
use App\Helpers\UiHelper;

// $helperObj = new UiHelper();
// $linkList = $helperObj->ulLinks($actionLinks);

/**
 * @var array $data
 */
// DebugRt::p($data);
//  $_SESSION['csrf_token'] ?? ''
// DebugRt::j('1', '', $_SESSION['csrf_token'] ?? '');

?>

<div class="container">
    <?php if (isset($error)) : ?>
        <div class="alert alert-danger">
            <h3>Error:</h3>
            <p><?= htmlspecialchars($error) ?></p>
            <?php if (isset($trace)) : ?>
                <pre><?= htmlspecialchars($trace) ?></pre>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <?= $albumsList->render() ?>
    <?php endif; ?>

    <div class="mt-4">
        <a href="<?= BASE_URL ?>/testy" class="btn btn-primary">Back to Testy</a>
    </div>
</div>