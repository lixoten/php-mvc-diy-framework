<?php

declare(strict_types=1);

use App\Enums\Url;
use App\Helpers\DebugRt;
use App\Helpers\MyLinkHelper as LinkHelper;

/**
 * @var array $data
 *
 * @var array $paginationData
 * @var array $windowedData
 * @var int $currentPage
 *
 */
// DebugRt::p($data);
?>
<h1><?= $title ?></h1>
<h2>Link Demostrations for LinkHelper Class and  how to use url enum class</h2>

<!-- In FlashMessage: -->
<hr />
<h3>In FlashMessages</h3>
<ul>
    <li>
        SEE ABOVE, the flash message, Added to FlashMessages to include a link helper usage:<br>
        Simple link with and without css
    </li>
    <li>SEE code located in TestyController->linkdemoAction()</li>
</ul>

<hr />
<!-- Test direct LinkHelper usage for text link: -->
<h3>Direct LinkHelper usage for <b>text</b> link: Simple link with and without css(class/style)</h3>
<ul>
    <li><?= LinkHelper::generateTextLink(Url::CORE_HOME, 'Go Home') ?></li>
    <li><?= LinkHelper::generateTextLink(Url::CORE_HOME, 'Go Home', [], ['class' => 'fw-bold']) ?> with css class</li>
    <li>
        <?= LinkHelper::generateTextLink(
            Url::STORE_POSTS,
            'Post List',
            [],
            ['style' => 'font-weight: bold; color: red;']
        ) ?> with css style</li>
    <li>
        <?= LinkHelper::generateTextLink(
            Url::STORE_POSTS_EDIT,
            'PostEdit123',
            ['id' => 123],
            ['style' => 'font-weight: bold; color: red;']
        ) ?> with css style
    </li>
</ul>
<hr />
<h3>Direct LinkHelper usage for <b>icon</b> link: Simple link with and without css(class/style)</h3>
 <ul>
    <li><?= LinkHelper::generateIconLink(Url::ADMIN_DASHBOARD, 'Admin with Icon') ?></li>
    <li>
        <?= LinkHelper::generateIconLink(
            Url::ADMIN_DASHBOARD,
            'Admin with Icon',
            [],
            ['style' => 'font-weight: bold; color: red;']
        ) ?> with css style
    </li>
</ul>


<hr />
<h3>Direct LinkHelper usage for <b>icon</b> link: Simple link with and without css(class/style)</h3>
 <ul>
    <li><?= LinkHelper::generateButtonLink(Url::CORE_HOME, 'Go Home', 'btn btn-outline-primary btn-lg') ?></li>
    <li><?= LinkHelper::generateButtonLink(Url::ADMIN_DASHBOARD, 'Admin Button') ?></li>
    <li><?= LinkHelper::generateButtonLink(Url::ADMIN_DASHBOARD, 'Custom Button', 'btn btn-success') ?></li>
    <li><?= LinkHelper::generateButtonLink(Url::STORE_POSTS_EDIT, 'Edit Post', 'btn btn-warning', ['id' => 123]) ?></li>
    <li><?= LinkHelper::generateTextLink(
        Url::ADMIN_DASHBOARD,
        'A generateTextLink',
        [],
        ['class' => 'btn btn-primary', 'target' => '_blank']
    ) ?></li>
    <li><?= LinkHelper::generateIconLink(
        Url::ADMIN_DASHBOARD,
        'Admin Button',
        [],
        ['class' => 'btn btn-primary']
    ) ?></li>
    <li><?= LinkHelper::generateTextLink(
        Url::ADMIN_DASHBOARD,
        '<i class="fa fa-tachometer-alt"></i> Admin Button',
        [],
        ['class' => 'btn btn-primary']
    ) ?></li>
    <li><?= LinkHelper::generateIconLink(
        Url::ADMIN_DASHBOARD,
        'Admin Dashboard',
        [],
        ['class' => 'btn btn-success', 'target' => '_blank']
    ) ?></li>
    <li><?= LinkHelper::generateIconLink(
        Url::STORE_POSTS,
        'View Posts',
        [],
        ['class' => 'btn btn-outline-info btn-lg']
    ) ?></li>
</ul>



<hr />
<!-- Test with data from controller: -->
<h3>Test with data from controller: Still a Simple link with and without css</h3>
 <ul>
    <li><?= LinkHelper::render($linkData) ?></li>
    <li><?= LinkHelper::render($linkData, ['class' => 'fw-bold']) ?> with css class</li>
</ul>


