<?php

declare(strict_types=1);

use App\Enums\Url;
use App\Helpers\MenuBuilder;
use App\Services\NavigationService;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get container instance
global $container;

// Get navigation service and build navigation data
$navigationService = $container->get(NavigationService::class);
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$navigation = $navigationService->buildNavigation($currentPath);

?>

<!-- Main Navigation -->
<nav><ul>
    <?= MenuBuilder::renderItems($navigation->getPublicItems(), $currentPath) ?>

    <?php if (!empty($navigation->getAccountItems())) : ?>
        <?= MenuBuilder::renderCategory('My Account') ?>
        <?= MenuBuilder::renderItems($navigation->getAccountItems(), $currentPath) ?>
    <?php endif; ?>

    <?php if (!empty($navigation->getStoreItems())) : ?>
        <?= MenuBuilder::renderCategory('My Store') ?>
        <?= MenuBuilder::renderItems($navigation->getStoreItems(), $currentPath) ?>
    <?php endif; ?>

    <?php if (!empty($navigation->getAdminItems())) : ?>
        <?= MenuBuilder::renderCategory('Admin') ?>
        <?= MenuBuilder::renderItems($navigation->getAdminItems(), $currentPath) ?>
    <?php endif; ?>

    <!-- Last items container -->
    <div class="last-items-container">
        <?php if ($storeInfo = $navigation->getStoreInfo()) : ?>
            <li class="view-store">
                <?= \App\Helpers\LinkBuilder::generateButtonLink(
                    Url::STORE_VIEW_PUBLIC,
                    ['slug' => $storeInfo['slug']],
                    $storeInfo['name'],
                    ['target' => '_blank']
                ) ?>
            </li>
        <?php endif; ?>

        <?php if (!empty($navigation->getGuestItems())) : ?>
            <?= MenuBuilder::renderItems($navigation->getGuestItems(), $currentPath) ?>
        <?php else : ?>
            <li><?= \App\Helpers\LinkBuilder::generateButtonLink(Url::LOGOUT) ?></li>
        <?php endif; ?>
    </div>
</ul></nav>

<!-- Sub Navigation -->
<?php if ($navigation->shouldShowSubNav()) : ?>
    <div class="store-subnav">
        <nav class="<?= $navigation->getSubNavClass() ?>">
            <ul>
                <?= MenuBuilder::renderItems(
                    $navigation->getSubNavItems(),
                    $currentPath,
                    ['show_icons' => true, 'match_prefix' => true]
                ) ?>
            </ul>
        </nav>
    </div>
    <script>
        document.querySelector(".main-content").style.marginLeft = "220px";
    </script>
<?php endif; ?>

<!-- Debug Bar -->
<div class="debugbar">
    <?php $debug = $navigation->getDebugInfo(); ?>
    Debug| Current Role: <?= $debug['role'] ?>
    <?php if ($debug['user_id']) : ?>
        | Current User: <?= $debug['user_id'] ?> - <?= htmlspecialchars($debug['username']) ?>
        <?php if (isset($debug['active_store_id']) && $debug['active_store_id']) : ?>
            | Active Store: <?= $debug['active_store_id'] ?> - <?= htmlspecialchars($debug['active_store_name']) ?>
        <?php endif; ?>
    <?php else : ?>
        | Current User: none
    <?php endif; ?>
    <br />
    Page: <?= $debug['namespace'] ?> -
    <?= $debug['controller'] ?> -
    <?= $debug['action'] ?> -
    <?= $debug['route_id'] ?>
</div>