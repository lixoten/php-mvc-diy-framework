<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;

/**
 * @var array $data
 * @var string $message
 */
//Debug::p($message);
///////////////////
// ValidationException - 422
///////////////////
?>

<div class="error-container validation-error">
    <h1>Error Page: View 422</h1>
    <h4><?= htmlspecialchars($message) ?></h4>
    <p><?= '422 BOOOOO LINE: ' . $data['line'] ?></p>



    <?php if ($data['exception'] instanceof \Core\Exceptions\ValidationException) : ?>
        <div class="validation-errors">
            <?php if ($data['exception']->hasErrors()) : ?>
                <ul class="error-list">
                    <?php foreach ($data['exception']->getErrors() as $field => $error) : ?>
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