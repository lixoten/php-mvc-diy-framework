<?php

declare(strict_types=1);

use App\Helpers\DebugRt;
use App\Helpers\LinkBuilder;

$linkList = '<ul>';
foreach ($actionLinks as $link) {
    $linkList .= '<li>' . LinkBuilder::generateTextLink($link['url'], [], $link['text']) . '</li>';
}
$linkList .= '</ul>';


/**
 * @var array $data
 */
// DebugRt::p($data);
?>
<h1><?= $title ?></h1>
<h1><?= htmlspecialchars($title) ?></h1>

<?= $linkList ?>
