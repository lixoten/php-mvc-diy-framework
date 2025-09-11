<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use Core\List\ListInterface;

/**
 * Interface for list renderers
 */
interface ListRendererInterface
{
    /**
     * Render a full list
     */
    public function renderList(ListInterface $list, array $options = []): string;

    /**
     * Render just the list header
     */
    public function renderHeader(ListInterface $list, array $options = []): string;

    /**
     * Render list body
     */
    public function renderBody(ListInterface $list, array $options = []): string;

    /**
     * Render pagination controls
     */
    public function renderPagination(ListInterface $list, array $options = []): string;

    /**
     * Render column value with appropriate formatting
     */
    public function renderValue(string $column, $value, array $record, array $options = []): string;

    /**
     * Render actions for a record
     */
    public function renderActions(ListInterface $list, array $record, array $options = []): string;
}
