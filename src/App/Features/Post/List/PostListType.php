<?php

declare(strict_types=1);

namespace App\Features\Post\List;

use Core\Interfaces\ConfigInterface;
use Core\List\AbstractListType;
use Core\Services\ConfigService;
use Core\Services\FieldRegistryService;

/**
 * Post list type definition
 */
class PostListType extends AbstractListType
{
    protected const VIEW_FOCUS = 'POST';
    protected const VIEW_NAME = 'post_list';

    protected array $options = [];

    // protected array $options = [
    //     // 'default_sort_key'          => TestyFields2::ID->value,
    //     // 'default_sort_direction'    => SortDirection::ASC->value,//'DESC',
    //     'pagination' => [
    //         // 'per_page' => 2,
    //     ],
    //     'render_options' => [
    //         'title' => 'list.testy.title 222',
    //         'list_columns' => [
    //             'id', 'title', 'generic_text', 'username', 'status', 'created_at'
    //         ],
    //     ],
    // ];


    /** {@inheritdoc} */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
    ) {
        $this->fieldRegistryService = $fieldRegistryService;
        $this->configService        = $configService;

        parent::__construct(
            fieldRegistryService: $this->fieldRegistryService,
            configService: $this->configService,
            viewFocus: static::VIEW_FOCUS,
            viewName: static::VIEW_NAME,
        );
        //parent::__construct(fieldRegistryService: $this->fieldRegistryService);
    }



    /** {@inheritdoc} */
    protected function getDeleteActionAttributes(): array
    {
        return [
                'post-id' => '{id}',
                'post-title' => '{title}',
                // 'testy-created_at' => '{created_at}',
                // 'testy-foofoo' => 'shit',
        ];
    }
}
