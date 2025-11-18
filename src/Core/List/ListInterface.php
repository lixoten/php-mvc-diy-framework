<?php

declare(strict_types=1);

namespace Core\List;

// use Core\List\Renderer\ListRendererInterface;

/**
 * Interface for lists
 */
interface ListInterface
{
    /**
     * Get the list pageKey
     */
    public function getPageKey(): string;

    /**
     * Get the list pageName
     */
    public function getPageName(): string;

    /**
     * Set the list title
     */
    public function setTitle(string $title): self;

    /**
     * Get the list title
     */
    public function getTitle(): string;


    /**
     * Set list data
     */
    public function setData(array $data): self;

    /**
     * Get list data
     */
    public function getData(): array;

    /**
     * Set column definitions
     */
    public function setColumns(array $columns): self;

    /**
     * Get column definitions
     */
    public function getColumns(): array;

    /**
     * Add an action to the list
     */
    public function addColumn(string $name, array $options = []): self;

    /**
     * Get all actions
     */
    public function getActions(): array;

    /**
     * Check if the list has any actions defined
     */
    public function hasActions(): bool;


    public function addAction(string $name, array $options): self;


    /**
     * Set pagination data
     */
    // public function setPagination(int $currentPage, int $totalPages, int $totalItems, int $perPage): self;
    public function setPagination(array $pagination): self;



    /**
     * Get pagination data
     */
    public function getPagination(): array;


    public function setOptions(array $options): self;

    // /**
    //  * Set the list renderer
    //  */
    // public function setRenderer(ListRendererInterface $renderer): self;

    // /**
    //  * Get the list renderer
    //  */
    // public function getRenderer(): ListRendererInterface;

    // /**
    //  * Render the list
    //  */
    // public function render(array $options = []): string;

    /**
     * Set list rendering options
     */
    public function setRenderOptions(array $options): self;

    /**
     * Get list rendering options
     */
    public function getRenderOptions(): array;

    /**
     * Set CSRF token for delete/action protection
     *
     * @param string $token The CSRF token
     * @return self
     */
    public function setCsrfToken(string $token): self;

    /**
     * Get the CSRF token
     *
     * @return string|null
     */
    public function getCsrfToken(): ?string;

    /**
     * Check if CSRF protection is enabled
     *
     * @return bool
     */
    public function hasCsrfProtection(): bool;
}
