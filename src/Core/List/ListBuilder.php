<?php

declare(strict_types=1);

namespace Core\List;

use App\Enums\Url;
use Core\Form\Field\Type\FieldTypeRegistry;
use Core\List\Renderer\ListRendererInterface;

/**
 * List builder
 */
class ListBuilder implements ListBuilderInterface
{
    // private string $name;
    // private ?string $title = null;
    // private array $columns = [];
    // private array $actions = [];
    // private array $data = [];
    // private array $pagination = [];
    // private array $options = [];
    // private array $renderOptions = [];
    // private ListRendererInterface $renderer;
    private ListInterface $list;
    private FieldTypeRegistry $fieldTypeRegistry;

    /**
     * Constructor
     */
    public function __construct(
        ListInterface $list,
        FieldTypeRegistry $fieldTypeRegistry
        // string $name,
        // ListRendererInterface $renderer,
        // array $options = []
    ) {
        // $this->name = $name;
        // $this->renderer = $renderer;
        $this->list = $list;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        // $this->options = $options;
    }

    public function setOptions(array $options): void
    {
        $this->list->setOptions($options);
    }
    public function setRenderOptions(array $renderOptions): void
    {
        $this->list->setRenderOptions($renderOptions);
    }

    /**
     * Add a column to the list
     */
    public function addColumn(string $name, ?string $label = null, array $options = []): self
    {
        // $this->columns[$name] = [
        //     'label' => $label ?? ucfirst(str_replace('_', ' ', $name . "wtf")),
        //     'options' => $options
        // ];

        $label = $label ?? ucfirst(str_replace('_', ' ', $name)); // Removed "wtf" placeholder
        $this->list->addColumn($name, $label, $options);
        return $this;
    }

    /**
     * Add an action to the list
     */
    public function addAction(string $name, array $options = []): self
    {
        // $this->actions[$name] = $options;
        $this->list->addAction($name, $options);
        return $this;
    }

    /**
     * Set the title of the list
     */
    public function setListTitle(string $title): self
    {
        $this->list->setTitle($title);
        return $this;
    }

    /**
     * Set the data for the list
     */
    public function setListData(array $data): self
    {
        $this->list->setData($data);
        return $this;
    }

    /**
     * Configure pagination
     */
    public function setPagination(array $pagination): self
    {
        $this->list->setPagination($pagination);
        return $this;
    }

    /**
     * Get the built list
     */
    public function getList(): ListInterface
    {
        return $this->list; // Return modified list
    }

    // /**
    //  * Configure pagination
    //  */
    // public function setPagination(int $currentPage, int $totalPages, int $totalItems, int $perPage): self
    // {
    //     $this->pagination = [
    //         'current_page' => $currentPage,
    //         'total_pages' => $totalPages,
    //         'total_items' => $totalItems,
    //         'per_page' => $perPage
    //     ];
    //     return $this;
    // }




    // /**
    //  * Get the built list
    //  */
    // public function getList(): ListInterface
    // {
    //     $list = new ListView($this->name, $this->columns);

    //     if ($this->title !== null) {
    //         $list->setTitle($this->title);
    //     }

    //     $list->setData($this->data);

    //     foreach ($this->actions as $name => $options) {
    //         $list->addAction($name, $options);
    //     }

    //     // if (!empty($this->options['render_options'])) {
    //     //     $list->setPagination(
    //     //         $this->pagination['current_page'],
    //     //         $this->pagination['total_pages'],
    //     //         $this->pagination['total_items'],
    //     //         $this->pagination['per_page']
    //     //     );
    //     // }

    //     if (!empty($this->renderOptions)) {
    //         $list->setRenderOptions($this->renderOptions);
    //     }

    //     if (!empty($this->pagination)) {
    //         $list->setPagination($this->pagination);
    //     }

    //     return $list;
    // }
}
