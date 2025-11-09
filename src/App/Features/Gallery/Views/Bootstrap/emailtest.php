<?php

declare(strict_types=1);

use App\Helpers\UiHelper;

/**
 * page template
 *
 * @var string $title - Page title
 * @var array $actionLinks - Array of action links with 'url' and 'text' keys
 */
?>
<h1><?= $title ?></h1>
<?= UiHelper::tableLinks($actionLinks, 4) ?>

<div class="container">
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h2>Email Testing Tool</h2>
        </div>

        <div class="card-body">
            <?php if ($sent) : ?>
                <!-- Show results if email was sent -->
                <div class="alert <?= $result['success'] ? 'alert-success' : 'alert-danger' ?>">
                    <strong><?= htmlspecialchars($result['message']) ?></strong>

                    <?php if (!$result['success'] && isset($result['error'])) : ?>
                        <div class="mt-2">
                            <strong>Error:</strong> <?= htmlspecialchars($result['error']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <h4>Email Details</h4>
                    <table class="table table-bordered">
                        <tr>
                            <th width="150">Recipient:</th>
                            <td><?= htmlspecialchars($result['recipient'] ?? 'Not specified') ?></td>
                        </tr>
                        <tr>
                            <th>Template:</th>
                            <td><?= htmlspecialchars($result['template'] ?? 'Not specified') ?></td>
                        </tr>
                        <tr>
                            <th>Test Time:</th>
                            <td><?= htmlspecialchars($timestamp) ?></td>
                        </tr>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="/testy/emailtest" class="btn btn-primary">Test Another Email</a>
                </div>
            <?php else : ?>
                <!-- Show form if email hasn't been sent yet -->
                <div class="alert alert-info">
                    <p><strong>Email Test Form</strong></p>
                    <p>This will test sending a verification email using the Mailgun API.</p>
                    <p>Click the button below to send a test email to:
                        <strong>Lixo Ten &lt;lixoten@gmail.com&gt;</strong>
                    </p>
                </div>

                <form method="post" action="/testy/emailtest">
                    <!-- CSRF Protection -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <div class="mb-3">
                        <label for="template" class="form-label">Email Template</label>
                        <input type="text" class="form-control"
                                 id="template" name="template" value="Auth/verification_email" readonly>
                        <div class="form-text">This is the template path that will be used.</div>
                    </div>

                    <button type="submit" class="btn btn-primary">Send Test Email</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card-footer">
            <a href="/testy" class="btn btn-outline-secondary">Back to Testy</a>
        </div>
    </div>
</div>