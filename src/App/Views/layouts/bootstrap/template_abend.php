<?php

declare(strict_types=1);

/**
 * Minimal Bootstrap error layout for critical errors (500, etc.)
 * @var string $content
 * @var string $title
 */
$rrr = 4;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title ?? 'Error') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/themes/bootstrap/css/bootstrap-core.css?v=1.0">
</head>
<body>
    <div class="container py-5">
        <?= $content ?>
    </div>
</body>
</html>
