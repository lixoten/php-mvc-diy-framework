<?php

// INCLUDE Also Review these files:
// INCLUDE src\App\Features\Store\Albums\List\AlbumsColumnRegistry.php
// INCLUDE src\App\Features\Store\Albums\AlbumsController.php

declare(strict_types=1);

// namespace App\Features\Albums\List;
namespace App\Features\Albums\List;

use App\Enums\Url;
use App\Helpers\DebugRt;
use Core\Context\CurrentContext;
use Core\List\AbstractListType;
use Core\List\ListBuilderInterface;

/**
 * Albums list type definition
 */
class AlbumsListType extends AbstractListType
{
    private $columns = [];
    private AlbumsColumnRegistry $columnRegistry;
    private CurrentContext $scrap;

    /**
     * Constructor
     */
    public function __construct(
        AlbumsColumnRegistry $columnRegistry,
        CurrentContext $scrap
    ) {
        $this->columnRegistry = $columnRegistry;
        $this->scrap = $scrap;

        // Default options
        $this->options = [
            'default_sort_key' => 'created_at',
            'default_sort_direction' => 'DESC', // TODO test if it works
        ];

        $urlType = $this->scrap->getRouteTypePath();

        // Default render options
        $this->listRenderOptions = [
            'add_button_label' =>  Url::STORE_ALBUMS_CREATE->label(),
            'add_url' => Url::STORE_ALBUMS_CREATE->url(),
            'show_actions' => true,
            'test_value' => 'mid',          // RemoveMe remove This was for me later for testing
            'test_value_only_mid' => 'mid'  // RemoveMe remove This was for me later for testing
        ];
    }

    /**
     * Get list name
     */
    public function getName(): string
    {
        return 'albums_list';
    }

    /**
     * Build the list
     */
    public function buildList(ListBuilderInterface $builder, array $options = []): void
    {
        // Set the list title
        $builder->setTitle('Albums');

        // Add columns
        $this->columns = $this->getColumns();
        // $this->columns = ['id', 'name', 'user_id', 'username', 'status', 'created_at'];

        foreach ($this->columns as $columnName) {
            $columnDef = $this->columnRegistry->get($columnName);
            if ($columnDef) {
                $builder->addColumn($columnName, $columnDef['label'], $columnDef);
            }
        }

        $urlType = $this->scrap->getRouteTypePath();

        // DebugRt::j('1', '', $urlType);
        // Add actions
        // $builder->addAction('view', [
        //     // 'url' => '/account/store/albums/view/{id}',
        //     'url' => $urlType . 'albums/view/{id}',
        //     'title' => 'View Album',
        //     'icon' => '<i class="fas fa-eye"></i>',
        //     'class' => 'btn btn-info'
        // ]);
        $builder->addAction(
            'view',
            Url::STORE_ALBUMS_VIEW->toLinkData(
                ['id' => '{id}'],
                // icon: null
            )
        );

        // $builder->addAction('edit', [
        //     // 'url' => '/account/store/albums/edit/{id}',
        //     'url' => $urlType . 'albums/edit/{id}',
        //     'title' => 'Edit Album',
        //     'icon' => '<i class="fas fa-edit"></i>',
        //     'class' => 'btn btn-primary'
        // ]);
        $builder->addAction(
            'edit',
            Url::STORE_ALBUMS_EDIT->toLinkData(
                ['id' => '{id}'],
            )
        );
        // $builder->addAction('delete', [
        //     // 'url' => '/account/store/albums/delete/{id}',
        //     // 'url' => '/account/store/albums/delete',
        //     'url' => '#', // URL doesn't matter for modal buttons
        //     'title' => 'Delete Album',
        //     'icon' => '<i class="fas fa-trash"></i>',
        //     'class' => 'btn btn-danger delete-album-btn',
        //     // 'class' => 'btn btn-danger',
        //     'confirm' => 'Are you sure you want to delete this album?',
        //     'data-attributes' => [
        //         'album-id' => '{id}',
        //         'album-title' => '{title}'
        //     ],
        //     'modal_title' => 'Delete Album',
        //     'form_action' => '/account/store/albums/delete'
        // ]);
        $builder->addAction(
            'delete',
            Url::STORE_ALBUMS_DELETE->toLinkData(
                ['id' => '{id}'],
                // label: 'Delete Post',
                attributes: [
                    'post-id' => '{id}',
                    'post-title' => '{title}',
                    // 'post-created_at' => '{created_at}',
                    // 'post-foofoo' => 'shit',
                ]
            )
        );
    }


    /**
     * Used/called my Controller
     */
    public function getColumns(): array
    {
        return ['id', 'name', 'user_id', 'username', 'status', 'created_at'];
    }
}
