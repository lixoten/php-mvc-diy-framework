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

<div class="container">
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h3>Send us a message</h3>
        </div>
        <div class="card-body">
          <?= $form->start() ?>

            <!-- No conditional needed - errorSummary will only render when appropriate -->
            <?= $form->errorSummary() ?>

            <!-- Personal Information Fieldset -->
            <fieldset class="mb-4">
              <legend>Personal Information</legend>
              <div class="row">
                <div class="col-md-6"><?= $form->row('name') ?></div>
                <div class="col-md-6"><?= $form->row('email') ?></div>
              </div>
            </fieldset>

            <!-- Message Fieldset -->
            <fieldset class="mb-4">
              <legend>Your Message</legend>
              <?= $form->row('subject') ?>
              <?= $form->row('message') ?>
            </fieldset>

            <!-- Additional Information Fieldset (if needed) -->
            <fieldset class="mb-4">
              <legend>Additional Information</legend>
              <div class="row">
                <div class="col-md-6"><?= $form->row('message2') ?></div>
              </div>
            </fieldset>

            <div class="mt-4">
              <?= $form->submit('Send Message') ?>
            </div>

          <?= $form->end() ?>
        </div>
      </div>
    </div>
  </div>
</div>