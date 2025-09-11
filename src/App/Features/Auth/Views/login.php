<?php

/**
 * Direct form rendering example
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


        <div class="mt-4 mb-3">
            <p>should be "don't have an account....Already have an account? <a href="/login">Log in</a></p>
        </div>

        <div class="d-grid gap-2">
             <!-- $form->submit('Register', ['class' => 'btn btn-primary']) -->
        </div>
    </div>
</div>
