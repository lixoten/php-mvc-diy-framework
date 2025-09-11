<?php

/**
 * Direct form rendering example
 */

use App\Helpers\DebugRt;

// DebugRt::j('1', '', $form);
?>
<h1><?= $title ?></h1>

<?php if ($form->hasCaptchaScripts()) : ?>
    <?= $form->getCaptchaScripts() ?>

<?php endif; ?>

<div class="card">
    <div class="card-body">
        <!-- Auto-rendering the entire form -->
        <?= $form->render() ?>
    </div>
</div>

<div class="mt-3">
    <p><a href="/contact/direct">View direct form rendering</a></p>
</div>