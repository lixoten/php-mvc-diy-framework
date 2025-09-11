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
$title = $title ?? 'Home';
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
- By INDIRECT the link information is passed via Controllor->Action.>
<ul>
    <li>
        <?= LinkHelper::fromArray($linkData1);?>
        Text Link: in-controller-action, uses url-enum, all defaults
    </li>
    <li>
        <?php $linkData1['attributes']['style'] = 'color:pink;'; ?>
        <?php $linkData1['icon'] = null; ?>
        <?= LinkHelper::fromArray($linkData1);?>
        Text Link: Not advisable but doable by overriding array. In this canse color, and icon.
    </li>
    <li>
        <?= LinkHelper::fromArray($linkData2);?>
        Text Link: in-controller-action, uses url-enum with param, but overrides label and css style
    </li>
    <li>
        <?= LinkHelper::fromArray($linkData5);?> Enternal like, all set in Controller Action
    </li>
</ul>



<h3>Direct LinkHelper usage for <b>Button</b> button: Button link with and without css(class/style)</h3>
- By Direct we mean it is set in Directly in VIEW.
<ul>
    <li><?= LinkHelper::generateButtonLink(Url::CORE_HOME, [], 'Go Home', ['class' => 'btn btn-outline-primary btn-lg']) ?></li>
    <li><?= LinkHelper::generateButtonLink(Url::ADMIN_DASHBOARD, [], 'Admin Button') ?></li>
    <li><?= LinkHelper::generateButtonLink(Url::ADMIN_DASHBOARD, [], 'Custom Button', ['class' => 'btn btn-success']) ?></li>
    <li><?= LinkHelper::generateButtonLink(Url::STORE_POSTS_EDIT, ['id' => 123], 'Edit Post', ['class' => 'btn btn-warning']) ?></li>
</ul>


<h3>INDIRECT LinkHelper usage for <b>Button</b> button: Button link with and without css(class/style)</h3>
- By INDIRECT the Button information is passed via Controllor->Action.>

<ul>
    <li>
        <?= LinkHelper::buttonFromData($linkDataButton);?>
        Button Link: created indirectly from data, uses default button classes.
    </li>
    <li>
        <?= LinkHelper::buttonFromData($linkDataButton, 'btn btn-outline-danger');?>
        Button Link: created indirectly, but with custom button classes passed from the view.
    </li>
</ul>

<h3>Misclation button created using other methods. Not recomended</h3>

<ul>
  <li><?= LinkHelper::generateTextLink(
      Url::ADMIN_DASHBOARD,
      [],
      'A generateTextLink',
      ['class' => 'btn btn-primary', 'target' => '_blank']
  ) ?></li>
  <li><?= LinkHelper::generateIconLink(
      Url::ADMIN_DASHBOARD,
      [],
      'Admin Button',
      ['class' => 'btn btn-primary']
  ) ?></li>
  <li><?= LinkHelper::generateTextLink(
      Url::ADMIN_DASHBOARD,
      [],
      '<i class="fa fa-tachometer-alt"></i> Admin Button',
      ['class' => 'btn btn-primary']
  ) ?></li>
  <li><?= LinkHelper::generateIconLink(
      Url::ADMIN_DASHBOARD,
      [],
      'Admin Dashboard',
      ['class' => 'btn btn-success', 'target' => '_blank']
  ) ?></li>
  <li><?= LinkHelper::generateIconLink(
      Url::STORE_POSTS,
      [],
      'View Posts',
      ['class' => 'btn btn-outline-info btn-lg']
  ) ?></li>
</ul>