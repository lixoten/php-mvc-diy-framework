<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

/**
 * Vanilla CSS framework layout template - now using Bootstrap grid
 *
 * @var string $title
 * @var string $content
 * @var \Core\Services\ThemeAssetService $themeAssets
 * @var \Core\Services\ThemePreviewService $themePreview
 * @var \Core\Form\FormInterface $form
 * @var \Core\Context\CurrentContext $scrapInfo
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'MVC LIXO' ?></title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Theme CSS -->
    <?= $themeAssets->renderCssLinks() ?>

    <!-- Theme JS (head) -->
    <?= $themeAssets->renderJsScripts('default', 'head') ?>
</head>
<body>
    <div class="container-fluid p-0">
        <header class="header py-3">
            <?php require(dirname(__DIR__) . '/menu.php'); ?>
        </header>

        <div class="row gx-0">
            <aside class="left-sidebar col-12 col-md-3 bg-light py-4">
                <h2 class="h5 text-center mb-3">Left sidebar</h2>
                <?php
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
            </aside>
            <main class="main-content col-12 col-md-9 py-4">
                <?php if (isset($flashRenderer)) : ?>
                    <div class="message-container mb-3">
                        <?= $flashRenderer->render() ?>
                    </div>
                <?php endif; ?>
                <?= $content ?>
            </main>
        </div>

        <footer class="footer py-3 text-center">
            <p>&copy; <?= date('Y') ?> MVC LIXO Framework</p>
        </footer>
    </div>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eB5LgN0T/2E0YFmX2jL" crossorigin="anonymous"></script>

    <!-- Theme JS (footer) -->
    <?= $themeAssets->renderJsScripts('default', 'footer') ?>
</body>
</html>