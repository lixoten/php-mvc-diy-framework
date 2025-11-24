<?php

/**
 * Direct form rendering example
 */

use App\Helpers\DebugRt;

// DebugRt::j('1', '', $form);
?>
<!-- // js-feature -->
<!-- <script src="https://unpkg.com/imask"></script> -->
  <!-- <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19/build/js/intlTelInput.min.js"></script> -->
  <!-- <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19/build/js/utils.js"></script> -->
<!-- <script src="/assets/js/form-feature.js"></script> -->
<div class="container">
    <h1><?= htmlspecialchars($title) ?></h1>

    <?php if ($form->hasCaptchaScripts()) : ?>
        <?= $form->getCaptchaScripts() ?>
    <?php endif; ?>

    <?php echo $renderedForm ?>

    <div class="mt-3">
        <p><a href="/contact">View component-based form rendering</a></p>
    </div>
</div>