<?php

declare(strict_types=1);

use App\Enums\Url;
use App\Helpers\DebugRt;
use App\Helpers\LinkBuilder as LinkHelper;

/**
 * @var array $data
 *
 * @var array $paginationData
 * @var array $windowedData
 * @var int $currentPage
 * @var array $linkData1
 * @var array $linkData2
 *
 */
// DebugRt::p($data);
?>
<h1><?= $title ?></h1>
<h2>xxxxxxLink Demostrations for LinkHelper Class and  how to use url enum class</h2>

<!-- In FlashMessage: -->

<hr />
<!-- Test direct LinkHelper usage for text link: -->
<h3>Direct LinkHelper usage for <b>text and icon</b> link: Simple link with and without css(class/style)</h3>
- By Direct we mean it is set in Directly in VIEW.
<ul>
    <li><?= LinkHelper::generateTextLink(Url::STORE_POSTS) ?> Text Link: Simple, uses url-enum. All defaults</li>
    <li>
        <?= LinkHelper::generateTextLink(
            Url::STORE_POSTS_EDIT,
            ['id' => 123],
            'My Fav Post > 123',
            ['class' => 'fw-bold', 'style' => 'color: red;']
        ) ?> Text Link: uses url-enum with param, but overrides label and css style
    </li>
    <li>
        <?= LinkHelper::generateIconLink(
            Url::STORE_POSTS_EDIT,
            ['id' => 123],
            null,
            ['class' => 'fw-bold', 'style' => 'color: red;']
        ) ?> Icon Link: uses url-enum with param, but overrides css style, defaults label. <br />Note: If u wanted a
        different color for icon you could create a custom class for the `<i>` and add a second class attribute
    </li>
</ul>

<h3>INDIRECT LinkHelper usage for <b>text and icon</b> link: Simple link with and without css(class/style)</h3>
- By INDIRECT the link information is passed via Controllor->Action.
<ul>
    <li>
        <?= LinkHelper::fromArray($linkData1);?>
        Text Link: in-controller-action, uses url-enum, all defaults
    </li>
    <li>
        <?= LinkHelper::fromArray($linkData2);?>
        Text Link: in-controller-action, uses url-enum with param, but overrides label and css style
    </li>
    <li>
        <?= LinkHelper::fromArray($linkData5);?> Enternal like, all set in Controller Action
    </li>
</ul>