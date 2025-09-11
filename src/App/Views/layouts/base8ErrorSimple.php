<?php

use App\Helpers\DebugRt;

// use App\Helpers\HtmlHelper;
//DebugRt::p(111);

//$flash = $data['flash'];
//Debug::p($data['flash']->peek());
//Debug::p($data['flashRenderer']);
//Debug::p($data['flash']->peek());



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/menu.css" />
    <title><?php $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="content-language" content="en">
    <meta name="robots" content="index, follow">
    <!-- Include Bootstrap CSS -->
    <!-- <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet"> -->

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








    <?php
    // Log that the layout template is being executed
    //error_log('Layout template base8ErrorSimple.html is being loaded');
    //echo "Error log path: " . ini_get('error_log');
    // Add at the top of base8ErrorSimple.html to log more info
    //error_log('Layout template is loading with variables: ' . implode(', ', array_keys(get_defined_vars())));

    // Check if content variable exists
    //if (isset($content)) {
    //    error_log('Content variable exists with length: ' . strlen($content));
    //} else {
    //    error_log('Content variable is MISSING');
    //}

    // Check if the view data is available
    //if (isset($message)) {
   //     error_log('Message: ' . $message);
    //} else {
   //     error_log('Message variable is MISSING');
    //}
    //phpinfo();
    //exit();
    ?>
    <style>
    .error-container {
        max-width: 100%;
        margin: 40px auto;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .error-header {
        margin-bottom: 20px;
    }
    .error-message {
        color: #721c24;
        background-color: #f8d7da;
        padding: 15px;
        border-radius: 4px;
        margin-top: 15px;
    }
    .helpful-links {
        background-color: #e9ecef;
        padding: 15px;
        border-radius: 4px;
        margin: 20px 0;
    }
    .helpful-links ul {
        padding-left: 20px;
    }
    .helpful-links a {
        color: #007bff;
    }
    .debug-info {
        margin-top: 30px;
        padding-top: 15px;
        border-top: 1px dashed #ccc;
        font-size: 12px;
        color: #6c757d;
    }
    </style>
</head>
<body>

<div class="containerLayout">
    <header class="header">
        <!--<h1>Responsive (CSS Grid Demo)</h1>-->
        <!--<p>By foo</p>-->
        <?php
            require('../App/Views/menu.php');
        ?>
    </header>
    <main class="main-content">
        <?php if (isset($flashRenderer)) : ?>
            <?= $flashRenderer->render() ?>
        <?php endif; ?>
        <?= $content ?>

    </main>
    <section class="left-sidebar">
        <h2>Left sidebar</h2>
    </section>
    <footer class="footer">
        <h2>Footer</h2>
    </footer>
</div>
<!-- Include Bootstrap JS and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
