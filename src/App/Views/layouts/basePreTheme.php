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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19/build/css/intlTelInput.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/menu.css" />
    <title>xxxxxx</title>


    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="content-language" content="en">
    <meta name="robots" content="index, follow">
    <!-- Include Bootstrap CSS -->
    <!--
        <link rel="stylesheet" type="text/css"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    -->

    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">


    <!-- Main CSS Framework (Bootstrap/Tailwind) -->
    <?php $defaultFramework = $this->config->getConfigValue('view', 'css_frameworks.default', 'bootstrap'); ?>
    <link rel="stylesheet" type="text/css"
          href="<?= $this->config->getConfigValue('view', "css_frameworks.available.{$defaultFramework}.css", '') ?>">

    <!-- Form CSS Framework (if different from default) -->
    <?php $formFramework = $this->config->getConfigValue('view', 'form.css_framework', ''); ?>
    <?php if ($formFramework && $formFramework !== $defaultFramework) : ?>
        <link rel="stylesheet" type="text/css"
              href="<?= $this->config->getConfigValue('view', "css_frameworks.available.{$formFramework}.css", '') ?>">
    <?php endif; ?>

    <!-- Visual Theme (if specified) -->
    <?php $visualTheme = $this->config->getConfigValue('view', 'visual_themes.active', ''); ?>
    <?php if ($visualTheme && $visualTheme !== 'standard') : ?>
        <?php $themeCSS = $this->config->getConfigValue('view', "visual_themes.available.{$visualTheme}.css", ''); ?>
        <?php if ($themeCSS) : ?>
            <link rel="stylesheet" type="text/css" href="<?= $themeCSS ?>">
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($formTheme)) : ?>
        <?php $formThemeCss = $this->config->getConfigValue('view', "form.themes.{$formTheme}.css", ''); ?>
        <?php if (!empty($formThemeCss)) : ?>
            <link rel="stylesheet" href="<?= $formThemeCss ?>">
        <?php endif; ?>
    <?php endif; ?>
    <style>

    </style>
</head>
<body>

<div class="containerLayout">
    <header class="header">
        <!--<h1>Responsive (CSS Grid Demo)</h1>-->
        <!--<p>By foo</p>-->

        <?php
            // require ('../App/Views/menu.php');
            require(dirname(__DIR__) . '/menu.php');
        ?>
    </header>
    <main class="main-content">
        <?= $flashRenderer->render() ?>
        <!-- <h2>Main contents</h2> -->
        <?php
        //print "aaaaaaaaaaaa";
        //Debug::p($data);
        ?>


        <?= $content ?>

    </main>
    <section class="left-sidebar">
        <h2>Left xsidebar</h2>
        <?php
        // TODO - Removeme
        if (isset($form)) {
            DebugRt::j('0', 'Data', $form->getData(), false);

            DebugRt::j('0', 'Render Options', $form->getRenderOptions(), false);
            foreach ($form->getFields() as $field) {
                DebugRt::j('0', '', $field->getName(), false);
                DebugRt::j('0', '', $field->getAttributes(), false);
            }
            //DebugRt::j('0', 'Form Fields', $form->getFields(), false);
            DebugRt::j('0', 'Form Layout', $form->getLayout(), false);
        }

        if (isset($scrapInfo)) {
            require(dirname(__DIR__) . '/scrapinfo.php');
            //DebugRt::j('1', 'ScrapInfo', $scrapInfo, false);
        }

        ?>
    </section>
    <footer class="footer">
        <h2>Footer</h2>
    </footer>
</div>

<!-- Include Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->

<!-- JavaScript support for main CSS framework -->
<?php
$defaultFrameworkJS = $this->config->getConfigValue(
    'view',
    "css_frameworks.available.{$defaultFramework}.js",
    ''
);
?>
<?php if ($defaultFrameworkJS) : ?>
    <script src="<?= $defaultFrameworkJS ?>"></script>
<?php endif; ?>

</body>
</html>
