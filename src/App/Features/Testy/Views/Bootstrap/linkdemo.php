<?php

declare(strict_types=1);

use App\Enums\Url;
use App\Helpers\DebugRt;
use App\Helpers\LinkBuilder as LinkHelper;
use App\Helpers\UiHelper;

/**
 * @var array $data
 *
 * @var array $paginationData
 * @var array $windowedData
 * @var int $currentPage
 * @var array $linkData1
 * @var array $linkData2
 * @var array $linkDataButton
 * @var array $actionLinks - Array of action links with 'url' and 'text' keys
 *
 */
// DebugRt::p($data);
$title = $title ?? 'Home';
?>
<h1><?= $title ?></h1>
<?= UiHelper::tableLinks($actionLinks, 4) ?>

<h2>xxxxxxLink Demostrations for LinkHelper Class and  how to use url enum class</h2>

<!-- In FlashMessage: -->

<hr />
<!-- Test direct LinkHelper usage for text link: -->
<h3>Direct LinkHelper usage for <b>text and icon</b> link: Simple link with and without css(class/style)</h3>
- By Direct we mean it is set in Directly in VIEW.
<ul>
</ul>

<h3>INDIRECT LinkHelper usage for <b>text and icon</b> link: Simple link with and without css(class/style)</h3>
- By INDIRECT the link information is passed via Controllor->Action.>
<ul>
</ul>



<h3>Direct LinkHelper usage for <b>Button</b> button: Button link with and without css(class/style)</h3>
- By Direct we mean it is set in Directly in VIEW.
<ul>
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

<h3>Miscellaneous button created using other methods. Not recommended</h3>


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
      ['class' => 'btn btn-primary'],
      'view'
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
      ['class' => 'btn btn-success', 'target' => '_blank'],
      'view'
  ) ?></li>
  <li><?= LinkHelper::generateIconLink(
      Url::STORE_POST,
      [],
      'View Posts',
      ['class' => 'btn btn-outline-info btn-lg'],
      'view'
  ) ?></li>
</ul>