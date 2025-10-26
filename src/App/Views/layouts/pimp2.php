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
<html lang="en"> <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'MVC1 LIXO' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">


    <!-- Theme CSS -->
    <?= $themeAssets->renderCssLinks() ?>

    <!-- Theme JS (head) -->
    <?= $themeAssets->renderJsScripts('default', 'head') ?>

    <style>
        /* CSS is crucial for the sticky footer effect */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Essential for pushing the footer to the bottom */
        .main-content-row {
            flex-grow: 1;
        }

        /* Visual placeholders */
        .lx-header, .lx-footer, .lx-sidebar, .lx-main-content { padding: 1rem; border: 1px dashed #ccc; }
        .lx-header { background-color: #e9ecef; }
        .lx-footer { background-color: #343a40; color: white; }
        .lx-main-content { background-color: #fff; }
        .vsidebar { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <header class="lx-header text-left">
        <?php require(dirname(__DIR__) . '/menu2.php'); ?>
    </header>

    <div class="lx-container container-fluid main-content-row d-flex flex-column">
        <div class="row flex-grow-1">

            <aside class="lx-left-sidebar col-12 col-lg-3 order-last order-lg-0">
                <h2>Left Sidebar</h2>
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

            <main class="lx-main-content col-12 col-lg-8"> <!-- // dangerdanger the 8 should be a 9 -->
                <h2>Main Content</h2>
                <!-- <div style="height: 100vh;">*Simulated long content for scrolling demo*</div> -->
                <?php if (isset($flashRenderer)) : ?>
                    <div class="message-container mb-3">
                        <?= $flashRenderer->render() ?>
                    </div>
                <?php endif; ?>
                <?= $content ?>
            </main>
<!--
            <aside class="lx-sidebar col-12 col-lg-3">
                <h2>Right Sidebar</h2>
                <p>Fixed width on desktop.</p>
                <p>&copy; <?= date('Y') ?> MVC LIXO Framework</p>
            </aside> -->

        </div>
    </div>

    <footer class="lx-footer text-center">
        <p class="m-0">&copy; 2024 Holy Grail Layout</p>
        <p class="m-0"><?= date('Y') ?> MVC LIXO Framework</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eB5LgN0T/2E0YFmX2jL" crossorigin="anonymous"></script>
</body>
</html>