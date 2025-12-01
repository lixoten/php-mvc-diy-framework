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
     *
     * @param string $pageName The page name as in 'Testy'
     * @param string $column The column name
     * @param mixed $value The value to render
     * @param array<string, mixed> $record The complete record data
     * @param array<string, mixed> $columns Column definitions
     * @return string The formatted value as HTML
     */
    public function renderValue(string $pageName, string $column, $value, array $record, array $columns = []): string;

    /**
     * Render actions for a record
     */
    public function renderActions(ListInterface $list, array $record, array $options = []): string;
}
