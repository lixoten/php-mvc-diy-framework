<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array $data
 * @var string $message
 */
//Debug::p($message);
///////////////////
// ValidationException
///////////////////
?>

<div class="error-container validation-error">
    <h1><?= htmlspecialchars($message) ?></h1>

    <?php if ($data['exception'] instanceof \Core\Exceptions\ValidationException): ?>
        <div class="validation-errors">
            <?php if ($data['exception']->hasErrors()): ?>
                <ul class="error-list">
                    <?php foreach ($data['exception']->getErrors() as $field => $error): ?>
                        <li>
                            <strong><?= htmlspecialchars(ucfirst($field)) ?>:</strong>
                            <?= htmlspecialchars($error) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <p><a href="javascript:history.back()" class="btn">Go Back</a></p>
</div>