<?php

declare(strict_types=1);

/**
 * @var array $data
 * @var array $scrapInfo
 * @var \Core\Context\CurrentContext $scrap
 * @var string $title - Page title
 */
?>

<div class="mdc-card mdc-card--outlined mdc-margin-bottom-4">
    <div class="mdc-card__header mdc-theme--primary-bg mdc-theme--on-primary">
        Debug Info
    </div>
    <div class="mdc-card__content">
        <table class="mdc-data-table__table mdc-typography scrapinfo-table" style="width:100%">
            <tbody>
                <tr>
                    <th scope="row" class="mdc-data-table__header-cell">Namespace</th>
                    <?php
                        $namespace = $scrap->getNamespaceName();
                        $temp = $namespace !== null ? substr($namespace, 13) : '';
                    ?>
                    <td class="mdc-data-table__cell"><?= htmlspecialchars($temp) ?></td>
                </tr>
                <tr>
                    <th scope="row" class="mdc-data-table__header-cell">Controller</th>
                    <td class="mdc-data-table__cell"><?= htmlspecialchars($scrapInfo['controller'] ?? '') ?></td>
                </tr>
                <?php if ($scrap->getActionName()): ?>
                <tr>
                    <th scope="row" class="mdc-data-table__header-cell">Action</th>
                    <td class="mdc-data-table__cell"><?= htmlspecialchars($scrap->getActionName()) ?></td>
                </tr>
                <?php endif; ?>

                <?php if ($scrap->getRouteId()): ?>
                <tr>
                    <th scope="row" class="mdc-data-table__header-cell">RouteId</th>
                    <td class="mdc-data-table__cell"><?= htmlspecialchars($scrap->getRouteId()) ?></td>
                </tr>
                <tr>
                    <th scope="row" class="mdc-data-table__header-cell">routeParams</th>
                    <td class="mdc-data-table__cell"><pre><?= print_r($scrapInfo['routeParams'], true) ?></pre></td>
                </tr>
                <?php endif; ?>

                <tr>
                    <th scope="row" class="mdc-data-table__header-cell">Page Name</th>
                    <td class="mdc-data-table__cell"><?= htmlspecialchars($scrapInfo['page_name'] ?? '') ?></td>
                </tr>
                <!-- Add more rows as needed for other properties -->
            </tbody>
        </table>
    </div>
</div>