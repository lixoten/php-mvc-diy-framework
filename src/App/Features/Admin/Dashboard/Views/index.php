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
 * @var string $title - Page title
 * @var array $actionLinks - Array of action links with 'url' and 'text' keys
 */
// DebugRt::p($data);
?>
<h1><?= $title ?></h1>
<?= $linkList ?>
