<?php

declare(strict_types=1);

namespace Core\List;

/**
 * Interface for list builders
 */
interface ListBuilderInterface
{
    /**
     * Set options
     */
    public function setOptions(array $options): void;
    /**
     * Set options
     */
    public function setRenderOptions(array $renderOptions): void;

    /**
     * Add a column to the list
     */
    public function addColumn(string $name, array $options = []): self;

    /**
     * Add an action to the list
     */
    public function addAction(string $name, array $options = []): self;

    /**
     * Configure pagination
     */
    // public function setPagination(int $currentPage, int $totalPages, int $totalItems, int $perPage): self;
    public function setPagination(array $pagination): self;

    /**
     * Set the title of the list
     */
    public function setListTitle(string $listTitle): self;

    /**
     * Set the data for the list
     */
    public function setListData(array $listData): self;

    /**
     * Get the built list
     */
    public function getList(): ListInterface;
}
