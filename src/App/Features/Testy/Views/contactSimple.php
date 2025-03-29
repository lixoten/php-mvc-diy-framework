<?php

declare(strict_types=1);

use App\Helpers\DebugRt;
use App\Helpers\UiHelper;

$helperObj = new UiHelper();
$linkList = $helperObj->ulLinks($actionLinks);


/**
 * @var array $data
 */
// DebugRt::p($data);
?>
<h1><?= $title ?></h1>
<?= $linkList ?>

<h1>CSRF Test</h1>

<h1>Contact Form Test</h1>

<div class="card">
    <div class="card-header">
        <h5>Contact Us</h5>
    </div>
    <div class="card-body">
        <form method="post" action="/testy/contactsimple">
            <!-- CSRF token field -->
            <?= $csrf->getTokenField() ?>

            <?= $formHelper->textField('name', 'Name:', $errors, $formData) ?>

            <?= $formHelper->textField('email', 'Email:', $errors, $formData, ['type' => 'email']) ?>

            <?= $formHelper->textField('subject', 'Subject:', $errors, $formData) ?>

            <?= $formHelper->textareaField('message', 'Message:', $errors, $formData, ['rows' => 5]) ?>

            <?= $formHelper->submitButton('Submit') ?>
        </form>
    </div>
</div>

<!-- This section shows the CSRF token for demonstration -->
<div class="card mt-4">
    <div class="card-header">
        <h5>CSRF Token Information (Debug)</h5>
    </div>
    <div class="card-body">
        <p>Current CSRF Token: <code><?= $csrf->getToken() ?></code></p>
        <p>This token is automatically added to the form above as a hidden field.</p>
    </div>
</div>

<?php if ($formData && empty($errors)) : ?>
<div class="card mt-4">
    <div class="card-header">
        <h5>Submitted Form Data (Debug)</h5>
    </div>
    <div class="card-body">
        <pre><?= htmlspecialchars(json_encode($formData, JSON_PRETTY_PRINT)) ?></pre>
    </div>
</div>
<?php endif; ?>