<?php

declare(strict_types=1);

use App\Helpers\DebugRt;
use App\Helpers\UiHelper;

$helperObj = new UiHelper();
$linkList = $helperObj->ulLinks($actionLinks);

/**
 * @var array $data
 */
//  DebugRt::p($content2);
?>
<h1><?= $title ?></h1>
<?= $linkList ?>
<hr />
<?= $errorLinks ?>
