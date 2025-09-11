<?php

declare(strict_types=1);

// Dynamic-me 
?>
<div class="dynamic-page">
    <h1><?= htmlspecialchars($title) ?></h1>

    <div class="page-content">
        <?= $content ?>
    </div>

    <?php if (!empty($last_updated)): ?>
    <div class="last-updated">
        Last updated: <?= htmlspecialchars($last_updated) ?>
    </div>
    <?php endif; ?>
</div>