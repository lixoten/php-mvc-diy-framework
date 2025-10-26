<?php

declare(strict_types=1);

namespace App\Features\Posts\List;

use Core\List\AbstractListType;
use Core\Services\FieldRegistryService;

/**
 * Posts list type definition
 */
class PostsListType extends AbstractListType
{
    protected const LIST_TYPE = 'POSTS';
    protected const LIST_NAME = 'posts_list';
    protected FieldRegistryService $fieldRegistryService;
    protected array $options = [
        // 'default_sort_key'          => PostFields2::ID->value,
        // 'default_sort_direction'    => SortDirection::ASC->value,//'DESC',
        'pagination' => [
            // 'per_page' => 2,
        ],
        'render_options' => [
            'title' => 'list.posts.title 222',
            'list_columns' => [
                'id', 'title', 'username', 'status', 'created_at'
            ],
        ],
    ];


    /** {@inheritdoc} */
    public function __construct(
        FieldRegistryService $fieldRegistryService,
    ) {
        $this->fieldRegistryService = $fieldRegistryService;
        $this->fieldRegistryService->setEntityName(static::LIST_TYPE);
        $this->fieldRegistryService->setPageName(static::LIST_NAME);

        parent::__construct(fieldRegistryService: $this->fieldRegistryService);
    }


    /** {@inheritdoc} */
    protected function getDeleteActionAttributes(): array
    {
        return [
                'post-id' => '{id}',
                'post-title' => '{title}',
                // 'post-created_at' => '{created_at}',
                // 'post-foofoo' => 'shit',
        ];
    }
}
