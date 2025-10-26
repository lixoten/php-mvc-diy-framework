<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

/**
 * Material Design framework layout template
 *
 * @var string $title Page title
 * @var string $content Main content to display
 * @var \Core\Services\ThemeServiceInterface $theme Active theme service
 * @var \Core\Services\ThemeAssetService $themeAssets Theme asset service
 * @var \Core\Services\ThemePreviewService $themePreview Theme preview service
 * @var \Core\Form\FormInterface $form Form instance if available
 * @var \Core\Context\CurrentContext $scrapInfo Context information
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="content-language" content="en">
    <meta name="robots" content="index, follow">
    <title><?= $title ?? 'MVC LIXO' ?></title>

    <!-- Theme CSS -->
    <?= $themeAssets->renderCssLinks() ?>

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Theme JS (head) -->
    <?= $themeAssets->renderJsScripts('default', 'head') ?>

    <?php if ($themePreview->isPreviewModeActive()) : ?>
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
    <?php if ($themePreview->isPreviewModeActive()) : ?>
    <div class="theme-preview-bar">
        <div>
            <strong>Preview Mode:</strong> <?= htmlspecialchars(ucfirst($themePreview->getPreviewTheme()
                                                                                                      ?? 'Unknown')) ?>
        </div>
        <div>
            <a href="<?= '/theme/apply/'
                                        . htmlspecialchars($themePreview->getPreviewTheme() ?? '') ?>">Apply Theme</a> |
            <a href="<?= '/theme/exit-preview?return_url='
                                        . urlencode($_SERVER['REQUEST_URI'] ?? '/') ?>">Exit Preview</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="mdc-layout-grid">
        <header class="header mdc-layout-grid__inner">
            <div class="mdc-layout-grid__cell--span-12">
                <?php require(dirname(__DIR__) . '/menu.php'); ?>
            </div>
        </header>

        <div class="mdc-layout-grid__inner">


            <section class="mdc-layout-grid__cell mdc-layout-grid__cell--span-4">
                <div class="mdc-card mdc-card--outlined mb-3">
                    <aside class="left-sidebar mdc-card__content">
                        <h2 class="mdc-typography--headline6">Left sidebar</h2>
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
                            require(dirname(__DIR__) . '/scrapinfo_material.php');
                        }
                        ?>
                    </aside>
                </div>
            </section>

            <main class="main-content mdc-layout-grid__cell mdc-layout-grid__cell--span-8">
                <?php if (isset($flashRenderer)) : ?>
                    <div class="mdc-card mdc-card--outlined mb-3">
                        <div class="mdc-card__content">
                            <?= $flashRenderer->render() ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?= $content ?>
            </main>

        </div>

        <footer class="footer mdc-layout-grid__inner mt-4">
            <div class="mdc-layout-grid__cell--span-12 text-center">
                <div class="mdc-card mdc-card--outlined">
                    <div class="mdc-card__content">
                        <p>&copy; <?= date('Y') ?> MVC LIXO Framework</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- jQuery (for legacy support) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Theme JS (footer) -->
    <?= $themeAssets->renderJsScripts('default', 'footer') ?>
</body>
</html>