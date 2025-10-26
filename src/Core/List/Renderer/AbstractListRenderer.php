<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use Core\List\ListInterface;
use Core\Services\ThemeServiceInterface;

/**
 * Abstract list renderer with framework-agnostic rendering logic
 */
abstract class AbstractListRenderer implements ListRendererInterface
{
    /**
     * View type constants
     */
    public const VIEW_TABLE = 'table';
    public const VIEW_GRID = 'grid';
    public const VIEW_LIST = 'list';

    /**
     * Theme service
     */
    protected ThemeServiceInterface $themeService;

    /**
     * Default options
     */
    protected array $defaultOptions = [
        'show_actions' => true,
        'show_pagination' => true,
        'show_view_toggle' => true,
        'view_type' => self::VIEW_TABLE,
        'add_button_label' => 'Add New',
    ];

    /**
     * Constructor
     */
    public function __construct(ThemeServiceInterface $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * Render a full list
     */
    public function renderList(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $cardClass = $this->themeService->getElementClass('card');

        $output = '<div class="' . $cardClass . '">';

        // Render header with title and add button
        $output .= $this->renderHeader($list, $options);

        // Render view toggle if enabled
        if ($options['show_view_toggle']) {
            $output .= $this->renderViewToggle($list, $options);
        }

        // Render body based on view type
        switch ($options['view_type']) {
            case self::VIEW_GRID:
                $output .= $this->renderGridView($list, $options);
                break;
            case self::VIEW_LIST:
                $output .= $this->renderListView($list, $options);
                break;
            default:
                $output .= $this->renderBody($list, $options);
                break;
        }

        // Render pagination if enabled
        if ($options['show_pagination'] && !empty($list->getPagination())) {
            $output .= $this->renderPagination($list, $options);
        }

        $output .= '</div>';

        return $output;
    }


    /**
     * Render column value with appropriate formatting
     *
     * @param string $column The column name
     * @param mixed $value The value to render
     * @param array<string, mixed> $record The complete record data
     * @param array<string, mixed> $columns Column definitions
     * @return string The formatted value as HTML
     */
    public function renderValue(string $column, $value, array $record, array $columns = []): string
    {
        if ($value === null) {
            return '';
        }

        $columnOptions = $columns[$column]['options'] ?? [];

        // Apply any custom formatters defined in options
        if (isset($columnOptions['formatter']) && is_callable($columnOptions['formatter'])) {
            return $columnOptions['formatter']($value, $record);
        }

        // Default formatting
        return is_string($value) ? htmlspecialchars($value) : (string)$value;
    }


    /**
     * Helper to find the first field of a specific type
     *
     * @param array<string, mixed> $columns Column definitions
     * @param string $type The field type to look for
     * @return string|null The field name if found, null otherwise
     */
    protected function findFirstFieldOfType(array $columns, string $type): ?string
    {
        foreach ($columns as $name => $column) {
            $options = $column['options'] ?? [];
            $fieldType = $options['type'] ?? '';

            if ($fieldType === $type) {
                return $name;
            }

            // Check field name for common patterns
            if (strpos($name, $type) !== false || strpos($name, 'image') !== false) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Render view toggle buttons
     */
    abstract protected function renderViewToggle(ListInterface $list, array $options): string;

    /**
     * Render grid view with cards
     */
    abstract protected function renderGridView(ListInterface $list, array $options): string;

    /**
     * Render list view with full-width items
     */
    abstract protected function renderListView(ListInterface $list, array $options): string;
}
