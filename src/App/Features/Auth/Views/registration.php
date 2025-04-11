<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array $data
 */
?>
<h1><?= $title ?></h1>

<div class="card-body">
    <?= $form->errorSummary() ?>

    <form method="post">
        <?= $form->start() ?>

        <?= $form->row('username') ?>
        <?= $form->row('email') ?>
        <?= $form->row('password') ?>
        <?= $form->row('confirm_password') ?>

        <div class="mt-4 mb-3">
            <p>Already have an account? <a href="/login">Log in</a></p>
        </div>

        <div class="d-grid gap-2">
            <?= $form->submit('Register', ['class' => 'btn btn-primary']) ?>
        </div>

        <?= $form->end() ?>
    </form>
</div>