<?php

// INCLUDE Also Review these files:
// INCLUDE src\App\Features\Store\Generic\List\GenericColumnRegistry.php
// INCLUDE src\App\Features\Store\Generic\GenericController.php

declare(strict_types=1);

// namespace App\Features\Admin\Generic\List;
namespace App\Features\Generic\List;

use App\Enums\Url;
use Core\Context\CurrentContext;
use Core\Interfaces\ConfigInterface;
use Core\List\AbstractListType;
use Core\List\ListBuilderInterface;
use InvalidArgumentException; // ADD

/**
 * Generic list type definition
 */
class GenericListType extends AbstractListType
{
    private GenericColumnRegistry $columnRegistry;
    private ConfigInterface $config;
    private CurrentContext $scrap;

    /**
     * Constructor
     */
    public function __construct(
        GenericColumnRegistry $columnRegistry,
        ConfigInterface $config,
        CurrentContext $scrap
    ) {
        $this->columnRegistry = $columnRegistry;
        $this->config = $config;
        $this->scrap = $scrap;

        // Default options
        $this->options = [
            'default_sort_key' => 'created_at',
            'default_sort_direction' => 'DESC',
        ];

        $urlType = $this->scrap->getRouteTypePath();

        // Default render options
        $this->listRenderOptions = [
            // 'add_url' => $urlType . 'generic/add',
            'add_url' => Url::GENERIC_CREATE->url(),
            'show_actions' => true,
        ];
    }

    /**
     * Get list name
     */
    public function getName(): string
    {
        // Could potentially make this generic too based on entityType from options if needed later
        return 'generic_list';
    }


    /**
     * Build the list
     */
    public function buildList(ListBuilderInterface $builder, array $options = []): void
    {
        // --- Get entityType from options ---
        $entityType = $options['entity_type'] ?? null;
        // $entityType = ''; // Dynamic-me Test


        if (!$entityType) {
            // Use InvalidArgumentException for bad input
            throw new InvalidArgumentException('entity_type must be provided in options for GenericListType');
        }

        // --- Set Title (Get from options passed by controller) ---
        $pageTitle = $options['page_title'] ?? ('Manage ' . ucfirst($entityType));
        $builder->setTitle($pageTitle);

        // --- Dynamically set Add URL and Label ---
        // This assumes a pattern, adjust if needed or load from config
        $this->listRenderOptions['add_url'] = '/admin/' . $entityType . '/add';


        // --- Add columns based on config ---
        // Get the columns configured for this entity type
        $displayColumns = $this->columnRegistry->getDisplayColumnsForEntity($entityType);

        foreach ($displayColumns as $columnName) {
            // FIX: Use getForEntity, passing the entityType
            $columnDef = $this->columnRegistry->getForEntity($columnName, $entityType);
            if ($columnDef) {
                $builder->addColumn($columnName, $columnDef['label'], $columnDef);
            } else {
                 // Log or handle columns defined in 'display' but missing a definition
                 trigger_error("Column definition missing for '$columnName' in entity '$entityType'", E_USER_WARNING);
            }
        }

        $urlType = $this->scrap->getRouteTypePath();

        // --- Add actions (Make URLs dynamic) ---
        $builder->addAction('view', [
            'url' => $urlType . $entityType . '/view/{id}', // Dynamic URL
            'title' => 'View ' . ucfirst($entityType),
            // 'icon' => '<i class="fas fa-eye"></i>',
            // 'class' => 'btn btn-info'
            'label' => Url::GENERIC_VIEW->label(),
            // 'icon' => Url::GENERIC_VIEW->icon(),
            // 'class' => Url::GENERIC_VIEW->class()
        ]);

        $builder->addAction('edit', [
            'url' => $urlType . $entityType . '/edit/{id}', // Dynamic URL
            'title' => 'Edit ' . ucfirst($entityType),
            // 'icon' => '<i class="fas fa-edit"></i>',
            // 'class' => 'btn btn-primary'
            'label' => Url::GENERIC_EDIT->label(),
            // 'icon' => Url::GENERIC_EDIT->icon(),
            // 'class' => Url::GENERIC_EDIT->class()
        ]);

        $builder->addAction('delete', [
            'url' => $urlType . $entityType . '/delete/{id}', // Dynamic URL
            'title' => 'Delete ' . ucfirst($entityType),
            // 'icon' => '<i class="fas fa-trash"></i>',
            // 'class' => 'btn btn-danger delete-generic-btn', // Keep class generic or make dynamic
            'label' => Url::GENERIC_DELETE->label(),
            // 'icon' => Url::GENERIC_DELETE->icon(),
            // 'class' => Url::GENERIC_DELETE->class(),
            'data-attributes' => [ //dangerdanger missing data attributes in generic..works on non generic
                // Make data attributes more generic or load keys from config
                'entity-id' => '{id}',
                // 'entity-title' => '{title}' // Only works if 'title' column exists and is passed in data
            ]
        ]);
    }
}
