<?php

/**
 * Direct form rendering example
 */

use App\Helpers\DebugRt;

// DebugRt::j('1', '', $form);
?>

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
    <p><a href="/contact">View component-based form rendering</a></p>
</div>