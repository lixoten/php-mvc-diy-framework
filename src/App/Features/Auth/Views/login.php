<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

// use App\Helpers\UiHelper;

// $helperObj = new UiHelper();

/**
 * @var array $data
 */
// DebugRt::p($data);
?>
<h1><?= $title ?></h1>

<div class="card-body">
    <?= $form->errorSummary() ?>

    <form method="post">
        <?= $form->start() ?>

        <?= $form->row('username') ?>
        <?= $form->row('password') ?>

        <div class="mb-3 form-check">
            <?= $form->row('remember') ?>
        </div>

        <div class="d-grid gap-2">
            <?= $form->submit('Login', ['class' => 'btn btn-primary']) ?>
        </div>

        <?= $form->end() ?>
    </form>
</div>