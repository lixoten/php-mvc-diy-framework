<?php

declare(strict_types=1);

use App\Helpers\MyLinkHelper as LinkHelper;
use App\Helpers\UiHelper;

/**
 * @var string $title
 * @var array $paginationData
 * @var array $windowedData
 * @var int $currentPage
 * @var string $title - Page title
 * @var array $actionLinks - Array of action links with 'url' and 'text' keys
 */
?>
<h1><?= $title ?></h1>
<?= UiHelper::tableLinks($actionLinks, 4) ?>
<h2>Pagination Service Test</h2>

<!-- Regular Pagination Section -->
<div class="mb-5">
    <h3>Regular Pagination</h3>
    <p>Current Page: <?= $currentPage ?></p>
    <p>Total Pages: <?= $paginationData['total'] ?></p>

    <?php if ($paginationData['showPagination']) : ?>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <!-- Previous Link -->
                <?php if ($paginationData['previous']) : ?>
                    <li class="page-item">
                        <?= LinkHelper::renderUsingEnumUrl($paginationData['previous'], ['class' => 'page-link']) ?>
                    </li>
                <?php else : ?>
                    <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                    </li>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php foreach ($paginationData['pages'] as $page) : ?>
                    <li class="page-item <?= $page['active'] ? 'active' : '' ?>">
                        <?php if ($page['active']) : ?>
                            <span class="page-link"><?= $page['text'] ?></span>
                        <?php else : ?>
                            <?= LinkHelper::renderUsingEnumUrl($page, ['class' => 'page-link']) ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>

                <!-- Next Link -->
                <?php if ($paginationData['next']) : ?>
                    <li class="page-item">
                        <?= LinkHelper::renderUsingEnumUrl($paginationData['next'], ['class' => 'page-link']) ?>
                    </li>
                <?php else : ?>
                    <li class="page-item disabled">
                        <span class="page-link">Next</span>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php else : ?>
        <p>No pagination needed.</p>
    <?php endif; ?>
</div>

<!-- Windowed Pagination Section -->
<div class="mb-5">
    <h3>Windowed Pagination (Window: <?= $windowedData['window'] ?? 'N/A' ?>)</h3>
    <p>Current Page: <?= $currentPage ?></p>
    <p>Showing Pages: <?= $windowedData['windowStart'] ?? 'N/A' ?> - <?= $windowedData['windowEnd'] ?? 'N/A' ?></p>

    <?php if ($windowedData['showPagination']) : ?>
        <nav aria-label="Windowed pagination">
            <ul class="pagination">
                <!-- Previous -->
                <?php if ($windowedData['previous']) : ?>
                    <li class="page-item">
                        <?= LinkHelper::renderUsingEnumUrl($windowedData['previous'], ['class' => 'page-link']) ?>
                    </li>
                <?php endif; ?>

                <!-- First page if not in window -->
                <?php if ($windowedData['showFirstPage']) : ?>
                    <li class="page-item">
                        <a class="page-link"
                           href="<?= $windowedData['pages'][0]['url']->url(['page' => 1]) ?>">
                            1
                        </a>
                    </li>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>

                <!-- Window pages -->
                <?php foreach ($windowedData['pages'] as $page) : ?>
                    <li class="page-item <?= $page['active'] ? 'active' : '' ?>">
                        <?php if ($page['active']) : ?>
                            <span class="page-link"><?= $page['text'] ?></span>
                        <?php else : ?>
                            <?= LinkHelper::renderUsingEnumUrl($page, ['class' => 'page-link']) ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>

                <!-- Last page if not in window -->
                <?php if ($windowedData['showLastPage']) : ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <li class="page-item">
                        <a class="page-link"
                           href="<?= $windowedData['pages'][0]['url']->url(['page' => $windowedData['total']]) ?>">
                            <?= $windowedData['total'] ?>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Next -->
                <?php if ($windowedData['next']) : ?>
                    <li class="page-item">
                        <?= LinkHelper::renderUsingEnumUrl($windowedData['next'], ['class' => 'page-link']) ?>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<hr>
<h3>Debug Data</h3>
<details>
    <summary>Regular Pagination Data</summary>
    <pre><?= print_r($paginationData, true) ?></pre>
</details>

<details>
    <summary>Windowed Pagination Data</summary>
    <pre><?= print_r($windowedData, true) ?></pre>
</details>