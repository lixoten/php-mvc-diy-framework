<?php

use App\Helpers\LinkBuilder;

$linkList = '<ul>';
foreach ($actionLinks as $link) {
    $linkList .= '<li>' . LinkBuilder::generateTextLink($link['url'], [], $link['text']) . '</li>';
}
$linkList .= '</ul>';

/**
 * Home page template
 *
 * @var string $title - Page title
 * @var array $actionLinks - Array of action links with 'url' and 'text' keys
 */
?>
<h1><?= $title ?></h1>
<?= $linkList ?>


<?= $additional_content ?>

