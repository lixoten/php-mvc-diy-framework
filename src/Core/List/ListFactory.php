<?php

declare(strict_types=1);

namespace Core\List;

use Core\Form\CSRF\CSRFToken;
use Core\Form\Field\Type\FieldTypeRegistry;
use App\Services\PaginationService;

/**
 * Factory for creating lists
 */
class ListFactory implements ListFactoryInterface
{
    private CSRFToken $csrfToken;
    private FieldTypeRegistry $fieldTypeRegistry;
    private PaginationService $paginationService;

   /**
     * Constructor
     *
     * @param CSRFToken $csrf
     * @param FieldTypeRegistry $fieldTypeRegistry
     * @param PaginationService $paginationService
     */
    public function __construct(
        CSRFToken $csrfToken,
        FieldTypeRegistry $fieldTypeRegistry,
        PaginationService $paginationService,
    ) {
        $this->csrfToken = $csrfToken;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->paginationService = $paginationService;
    }


    /**
     * {@inheritdoc}
     */
    public function create(
        ListTypeInterface $listType,
        array $data = [],
        array $options = [],
    ): ListInterface {
        // Create list instance
        $list = new ListView($listType->pageKey, $listType->pageName);

        // Create list builder
        $builder = new ListBuilder($list, $this->fieldTypeRegistry);

        // Built it
        $listType->buildList($builder);

        // Beg Process pagination data using PaginationService ---
        $paginationOptions = $listType->getPaginationOptions();

        if (
            $paginationOptions['listUrlEnum'] &&
            !empty($paginationOptions['total_pages']) &&
            $paginationOptions['total_pages'] > 1
        ) {
            $baseUrlEnum = $paginationOptions['listUrlEnum'];
            $currentPage = $paginationOptions['current_page'] ?? 1;
            $totalPages  = (int) $paginationOptions['total_pages'];
            $windowSize  = $paginationOptions['window_size'] ?? 2;
            $urlParams   = $listType->getRenderOptions()['current_query_params'] ?? [];

            // Use the service to get structured pagination data
            $structuredPaginationData = $this->paginationService->getPaginationDataWithWindow(
                $baseUrlEnum,
                $currentPage,
                $totalPages,
                $windowSize,
                $urlParams,
            );
            $list->setPagination($structuredPaginationData);
        }
        // --- Beg Process pagination ---

        // Set data and CSRF
        $builder->setListData($data);
        // Automatically add CSRF token if it has delete actions and CSRF protection is not explicitly disabled
        // fixme never happens
        if (!isset($options['csrf_protection']) || $options['csrf_protection'] !== false) {
            // Check if the list has any delete actions that would need CSRF protection
            $actions = $list->getActions();
            if (isset($actions['delete'])) {
                $list->setCsrfToken($this->csrfToken->getToken());
            }
        }

        return $list;
    }
}
