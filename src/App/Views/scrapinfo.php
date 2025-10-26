<?php

declare(strict_types=1);

/**
 * @var array $data
 * @var array $scrapInfo
 * @var \Core\Context\CurrentContext $scrap
 * @var string $title - Page title
 */

// $rr = $this->scrap();
// $rr = $scrap();
// $rr = 33;
?>

<div class="scrap-container card mb-4 table-responsive">
    <div class="scrap-head card-header bg-primary text-white">
        Debug Info
    </div>
    <div class="scrap-body card-body">
        <table class="scrap-table table table-bordered table-striped mb-0">
            <tbody>
                <tr>
                    <th scope="row">Namespace</th>
                    <?php
                        //$temp = substr($scrap->getNamespaceName(), 13 ?? '');
                        $namespace = $scrap->getNamespaceName();
                        $temp = $namespace !== null ? substr($namespace, 13) : '';
                    ?>
                    <td><?= htmlspecialchars($temp) ?></td>
                </tr>
                <tr>
                    <th scope="row">Controller</th>
                    <td><?= htmlspecialchars($scrapInfo['controller'] ?? '') ?></td>
                </tr>
                <?php if ($scrap->getActionName()): ?>
                <tr>
                    <th scope="row">Action</th>
                    <td><?= htmlspecialchars($scrap->getActionName()) ?></td>
                </tr>
                <?php endif; ?>

                <?php if ($scrap->getRouteId()): ?>
                <tr>
                    <th scope="row">RouteId</th>
                    <td><?= htmlspecialchars($scrap->getRouteId()) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($scrap->getRouteId()): ?>
                <tr>
                    <th scope="row">routeParams</th>
                    <td><pre><?= print_r($scrapInfo['routeParams']) ?></pre></td>
                </tr>
                <?php endif; ?>

                <tr>
                    <th scope="row">Page Name</th>
                    <td><?= htmlspecialchars($scrapInfo['page_name'] ?? '') ?></td>
                </tr>
                <!-- Add more rows as needed for other properties -->
            </tbody>
        </table>
    </div>
</div>


