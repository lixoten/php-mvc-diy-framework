<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

/**
 * @var array $data
 */
// DebugRt::p($data);
?>
<h1><?= $title ?></h1>
<?php if (isset($captcha_scripts)) : ?>
    <?= $captcha_scripts ?>
    <script>
    function validateCaptcha() {
        var response = grecaptcha.getResponse();
        if (response.length === 0) {
            alert("Please complete the CAPTCHA before submitting.");
            return false; // Prevent form submission
        }
        return true; // Allow form submission
    }
</script>
<?php endif; ?>
<div class="card-body">
    <?= $form->errorSummary() ?>

    <!-- <form method="post"> -->
        <?= $form->start(['onsubmit' => 'return validateCaptcha()']) ?>

        <?= $form->row('username') ?>
        <?= $form->row('password') ?>

        <div class="mb-3 form-check">
            <?= $form->row('remember') ?>
        </div>


        <?php if ($form->has('captcha')) : ?>
            <div class="mb-3">
                <?= $form->captcha('captcha', ['theme' => 'dark']) ?>
            </div>
        <?php endif; ?>

        <div class="d-grid gap-2">
            <?= $form->submit('Login', ['class' => 'btn btn-primary']) ?>
        </div>

        <?= $form->end() ?>

        <div class="mt-3 text-center">
            <p>Don't have an account? <a href="/registration">Create one</a></p>

            <p class="mt-2"><small>
                <a href="/verify-email/resend" class="text-muted">Need to verify your email?</a></small>
            </p>
        </div>
    </form>
</div>