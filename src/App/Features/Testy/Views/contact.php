<?php

declare(strict_types=1);

use App\Helpers\DebugRt as Debug;
use App\Helpers\UiHelper;

$helperObj = new UiHelper();


//Debug::p($themeCssPath);

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
                    <?= $form->render() ?>
                </div>
            </div>
        </div>
    </div>
</div>