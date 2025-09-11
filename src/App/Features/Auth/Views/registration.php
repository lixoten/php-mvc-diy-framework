<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

/**
 * @var array $data
 */
?>
<h1><?= $title ?></h1>
<?php if (isset($captcha_scripts)) : ?>
    <?= $form->getCaptchaScripts() ?>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <!-- Auto-rendering the entire form -->
        <?= $form->render() ?>
    </div>
</div>

