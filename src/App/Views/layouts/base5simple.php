<?php

use App\Helpers\DebugRt;

// use App\Helpers\HtmlHelper;
//DebugRt::p(111);

//$flash = $data['flash'];
//Debug::p($data['flash']->peek());
//Debug::p($data['flashRenderer']);
//Debug::p($data['flash']->peek());

/** @var \Core\Form\FormInterface $form */
/** @var \Core\Context\CurrentContext $scrapInfo */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="content-language" content="en">
    <meta name="robots" content="index, follow">

    <title><?= $title ?? 'MVC LIXO' ?></title>

    <!-- Theme CSS - Dynamically loaded based on active theme -->
    <?= $themeAssets->renderCssLinks() ?>

    <!-- Theme JS (head) -->
    <?= $themeAssets->renderJsScripts('default', 'head') ?>

    <!-- Legacy CSS support (can be removed after full theme migration) -->
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/menu.css" />

    <?php if ($themePreview->isPreviewModeActive()): ?>
    <style>
        .theme-preview-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 9999;
        }
        .theme-preview-bar a {
            color: white;
            text-decoration: underline;
        }
    </style>
    <?php endif; ?>
</head>
<body>

<?php if ($themePreview->isPreviewModeActive()): ?>
<div class="theme-preview-bar">
    <div>
        <strong>Preview Mode:</strong> <?= htmlspecialchars(ucfirst($themePreview->getPreviewTheme() ?? 'Unknown')) ?>
    </div>
    <div>
        <a href="<?= '/theme/apply/' . htmlspecialchars($themePreview->getPreviewTheme() ?? '') ?>">Apply Theme</a> |
        <a href="<?= '/theme/exit-preview?return_url=' . urlencode($_SERVER['REQUEST_URI'] ?? '/') ?>">Exit Preview</a>
    </div>
</div>
<?php endif; ?>


<div class="<?= $theme->getElementClass('layout.container') ?>">
    <header class="<?= $theme->getElementClass('layout.header') ?>">
        <?php require(dirname(__DIR__) . '/menu.php'); ?>
    </header>

    <main class="<?= $theme->getElementClass('layout.main-content') ?>">
        <?= $flashRenderer->render() ?>
        <?= $content ?>
    </main>

    <section class="<?= $theme->getElementClass('layout.sidebar') ?>">
        <h2>Left sidebar</h2>
        <?php
        // Debug information (only if needed)
        if (isset($form)) {
            DebugRt::j('0', 'Data', $form->getData(), false);
            DebugRt::j('0', 'Render Options', $form->getRenderOptions(), false);
            foreach ($form->getFields() as $field) {
                DebugRt::j('0', '', $field->getName(), false);
                DebugRt::j('0', '', $field->getAttributes(), false);
            }
            DebugRt::j('0', 'Form Layout', $form->getLayout(), false);
        }

        if (isset($scrapInfo)) {
            require(dirname(__DIR__) . '/scrapinfo.php');
        }
        ?>
    </section>

    <footer class="<?= $theme->getElementClass('layout.footer') ?>">
        <h2>Footer</h2>
    </footer>
</div>

<!-- jQuery (for legacy support) -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<!-- Theme JS (footer) -->
<?= $themeAssets->renderJsScripts('default', 'footer') ?>

</body>
</html>
