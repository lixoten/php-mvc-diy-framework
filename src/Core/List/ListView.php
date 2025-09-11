<?php

declare(strict_types=1);

namespace Core\List;

use Core\List\Renderer\ListRendererInterface;

/**
 * List view
 */
class ListView implements ListInterface
{
    private string $name;
    private string $title;
    private array $data = [];
    private array $columns = [];
    private array $actions = [];
    private array $pagination = [];
    private array $options = [];
    private array $renderOptions = [];
    private ?ListRendererInterface $renderer = null;
    private ?string $csrfToken = null;

    /**
     * Constructor
     */
    public function __construct(
        string $name,
        array $columns = [],
    ) {
        $this->name = $name;
        $this->columns = $columns;
    }

    /**
     * Set list options
     */
    public function setOptions(array $options): self
    {
        // $this->options = array_merge($this->options, $options);
        $this->options = $options;
        return $this;
    }

    /**
     * Add a column to the list
     */
    public function addColumn(string $name, string $label, array $options = []): self
    {
        $this->columns[$name] = ['label' => $label] + $options;
        return $this;
    }



    /**
     * Set CSRF token for delete/action protection
     */
    public function setCsrfToken(string $token): self
    {
        $this->csrfToken = $token;
        return $this;
    }

    /**
     * Get the CSRF token
     */
    public function getCsrfToken(): ?string
    {
        return $this->csrfToken;
    }

    /**
     * Check if CSRF protection is enabled
     */
    public function hasCsrfProtection(): bool
    {
        return $this->csrfToken !== null;
    }

    /**
     * Check if the list has any actions defined
     */
    public function hasActions(): bool
    {
        return !empty($this->actions);
    }


    /**
     * Get the list name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the list title
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the list title
     */
    public function getTitle(): string
    {
        return $this->title ?? $this->name;
    }

    /**
     * Set list data
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get list data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set column definitions
     */
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Get column definitions
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Add an action to the list
     */
    public function addAction(string $name, array $options): self
    {
        $this->actions[$name] = $options;
        return $this;
    }

    /**
     * Get all actions
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    // /**
    //  * Set pagination data
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
    /**
     * Set pagination data
     */
    public function setPagination(array $pagination): self
    {
        $this->pagination = $pagination;
        return $this;
    }

    /**
     * Get pagination data
     */
    public function getPagination(): array
    {
        return $this->pagination;
    }

    /**
     * Set the list renderer
     */
    public function setRenderer(ListRendererInterface $renderer): self
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Get the list renderer
     */
    public function getRenderer(): ListRendererInterface
    {
        return $this->renderer;
    }

    /**
     * Render the list
     */
    public function render(array $options = []): string
    {
        if ($this->renderer === null) {
            throw new \RuntimeException('Renderer not set for list: ' . $this->name);
        }
        $mergedOptions = array_merge($this->renderOptions, $options);
        return $this->renderer->renderList($this, $mergedOptions);
    }

    /**
     * Set list rendering options
     */
    public function setRenderOptions(array $options): self
    {
        $this->renderOptions = $options;
        return $this;
    }

    /**
     * Get list rendering options
     */
    public function getRenderOptions(): array
    {
        return $this->renderOptions;
    }
}
