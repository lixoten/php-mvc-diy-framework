<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use Core\List\ListInterface;
use Core\List\ListView;
use Core\Services\ThemeServiceInterface;

/**
 * Bootstrap list renderer
 */
class BootstrapListRenderer extends AbstractListRenderer
{
    /**
     * Constructor
     */
    public function __construct(
        ThemeServiceInterface $themeService
    ) {
        parent::__construct($themeService);

        // Bootstrap-specific default options
        $this->defaultOptions = array_merge($this->defaultOptions, [
            'view_type' => self::VIEW_GRID,
            'view_type' => self::VIEW_TABLE,
            'view_type' => self::VIEW_LIST,
        ]);
        // Fik - Override List View Default - GRID TABLE LIST
        $this->defaultOptions['view_type'] = self::VIEW_TABLE;
    }

    /**
     * Render list header
     */
    public function renderHeader(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $headerClass = $this->themeService->getElementClass('card.header');
        $addButtonClass = $this->themeService->getElementClass('button.add');

        $output = '<div class="' . $headerClass . '">';

        // Add title
        $output .= '<h2>' . htmlspecialchars($list->getTitle()) . '</h2>';

        // Add "Add New" button if URL is provided
        if (!empty($options['add_url'])) {
            $output .= '<a href="' . $options['add_url'] . '" class="' . $addButtonClass . '">';
            $output .= $this->themeService->getIconHtml('add') . ' ' .
                htmlspecialchars($options['add_button_label']);
            $output .= '</a>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render view toggle buttons
     */
    protected function renderViewToggle(ListInterface $list, array $options = []): string
    {
        $baseUrl = $options['toggle_url_base'] ?? $_SERVER['REQUEST_URI'];
        $baseUrl = strtok($baseUrl, '?'); // Remove existing query string
        $currentView = $options['view_type'];

        $cardBodyClass = $this->themeService->getElementClass('card.body');
        $toggleClass = $this->themeService->getElementClass('view.toggle');

        $output = '<div class="' . $cardBodyClass . ' pt-0">';
        $output .= '<div class="' . $toggleClass . '" role="group" aria-label="View options">';

        // Table view button
        $activeClass = ($currentView === self::VIEW_TABLE) ? ' active' : '';
        $output .= '<a href="' . $baseUrl . '?view=' . self::VIEW_TABLE . '" ';
        $output .= 'class="btn btn-outline-secondary' . $activeClass . '" title="Table View">';
        $output .= $this->themeService->getIconHtml('table') . '</a>';

        // Grid view button
        $activeClass = ($currentView === self::VIEW_GRID) ? ' active' : '';
        $output .= '<a href="' . $baseUrl . '?view=' . self::VIEW_GRID . '" ';
        $output .= 'class="btn btn-outline-secondary' . $activeClass . '" title="Grid View">';
        $output .= $this->themeService->getIconHtml('grid') . '</a>';

        // List view button
        $activeClass = ($currentView === self::VIEW_LIST) ? ' active' : '';
        $output .= '<a href="' . $baseUrl . '?view=' . self::VIEW_LIST . '" ';
        $output .= 'class="btn btn-outline-secondary' . $activeClass . '" title="List View">';
        $output .= $this->themeService->getIconHtml('list') . '</a>';

        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render list body (table view)
     */
    public function renderBody(ListInterface $list, array $options = []): string
    {
        return $this->renderTableView($list, $options);
    }

    /**
     * Render table view
     */
    public function renderTableView(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $cardBodyClass = $this->themeService->getElementClass('card.body');
        $tableClass = $this->themeService->getElementClass('table');

        $output = '<div class="' . $cardBodyClass . '">';
        $output .= '<table class="' . $tableClass . '">';

        // Render table header
        $output .= '<thead><tr>';
        foreach ($list->getColumns() as $name => $column) {
            $output .= '<th>' . htmlspecialchars($column['label']) . '</th>';
        }

        // Add actions column if needed
        if ($options['show_actions'] && !empty($list->getActions())) {
            $output .= '<th>Actions</th>';
        }

        $output .= '</tr></thead>';

        // Render table body
        $output .= '<tbody>';
        foreach ($list->getData() as $record) {
            $output .= '<tr>';

            foreach (array_keys($list->getColumns()) as $columnName) {
                $columns = $list->getColumns();
                $value = $record[$columnName] ?? null;
                $output .= '<td>' . $this->renderValue($columnName, $value, $record, $columns) . '</td>';
            }

            // Render actions
            if ($options['show_actions'] && !empty($list->getActions())) {
                $output .= '<td>' . $this->renderActions($list, $record, $options) . '</td>';
            }

            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render grid view with cards in a grid layout
     */
    protected function renderGridView(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $viewLayout = $this->themeService->getViewLayoutClasses('grid');
        $cardBodyClass = $this->themeService->getElementClass('card.body');

        $output = '<div class="' . $cardBodyClass . '">';
        $output .= '<div class="' . $viewLayout['container'] . '">';

        // Get columns to display
        $columns = $list->getColumns();

        // Get primary image field, title field and description fields
        $imageField = $options['grid_image_field'] ?? $this->findFirstFieldOfType($columns, 'image');
        $titleField = $options['grid_title_field']
            ?? $this->findFirstFieldOfType($columns, 'title')
            ?? array_key_first($columns);
        $descFields = $options['grid_description_fields'] ?? array_slice(array_keys($columns), 1, 2);

        // Render each record as a card
        foreach ($list->getData() as $record) {
            $output .= '<div class="' . $viewLayout['item'] . '">';
            $output .= '<div class="' . $viewLayout['card'] . '">';

            // Render image if we have an image field defined
            if ($imageField && !empty($record[$imageField])) {
                $imageValue = $record[$imageField];
                $imageUrl = $this->getImageUrl($imageField, $imageValue, $record, $columns);
                if ($imageUrl) {
                    $output .= '<img src="' . htmlspecialchars($imageUrl) . '" class="' .
                        $viewLayout['image'] . '" alt="' .
                        htmlspecialchars((string)($record[$titleField] ?? 'Item image')) . '">';
                }
            }

            // Card body with title and description
            $output .= '<div class="' . $viewLayout['body'] . '">';

            // Title
            if (isset($record[$titleField])) {
                $output .= '<h5 class="' . $viewLayout['title'] . '">' .
                    htmlspecialchars((string)$record[$titleField]) . '</h5>';
            }

            // Description fields
            foreach ($descFields as $field) {
                if (isset($record[$field]) && $field !== $titleField && $field !== $imageField) {
                    $fieldLabel = $columns[$field]['label'] ?? ucfirst(str_replace('_', ' ', $field));
                    $output .= '<p class="' . $viewLayout['text'] . '">';
                    $output .= '<strong>' . htmlspecialchars($fieldLabel) . ':</strong> ';
                    $output .= $this->renderValue($field, $record[$field], $record, $columns);
                    $output .= '</p>';
                }
            }

            $output .= '</div>'; // End card body

            // Card footer with actions
            if ($options['show_actions'] && !empty($list->getActions())) {
                $output .= '<div class="' . $viewLayout['footer'] . '">';
                $output .= $this->renderActions($list, $record, $options);
                $output .= '</div>';
            }

            $output .= '</div>'; // End card
            $output .= '</div>'; // End col
        }

        $output .= '</div>'; // End grid
        $output .= '</div>'; // End card body

        return $output;
    }

    /**
     * Render list view with full-width items
     */
    protected function renderListView(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $viewLayout = $this->themeService->getViewLayoutClasses('list');
        $cardBodyClass = $this->themeService->getElementClass('card.body');

        $output = '<div class="' . $cardBodyClass . '">';
        $output .= '<div class="' . $viewLayout['container'] . '">';

        // Get columns to display
        $columns = $list->getColumns();
        $displayFields = $options['list_display_fields'] ?? array_keys($columns);

        // Get primary fields
        $imageField = $options['list_image_field'] ?? $this->findFirstFieldOfType($columns, 'image');
        $titleField = $options['list_title_field']
            ?? $this->findFirstFieldOfType($columns, 'title')
            ?? array_key_first($columns);

        // Render each record as a list item
        foreach ($list->getData() as $record) {
            $output .= '<div class="' . $viewLayout['item'] . '">';

            // Optional image
            if ($imageField && !empty($record[$imageField])) {
                $imageValue = $record[$imageField];
                $imageUrl = $this->getImageUrl($imageField, $imageValue, $record, $columns);
                if ($imageUrl) {
                    $output .= '<img src="' . htmlspecialchars($imageUrl) . '"
                        class="rounded me-3" style="max-width: 64px; max-height: 64px;"
                        alt="' . htmlspecialchars((string)($record[$titleField] ?? 'Item image')) . '">';
                }
            }

            // Main content area
            $output .= '<div class="' . $viewLayout['content'] . '">';

            // Title field
            if ($titleField && isset($record[$titleField])) {
                $output .= '<h5 class="' . $viewLayout['title'] . '">' .
                    htmlspecialchars((string)$record[$titleField]) . '</h5>';
            }

            // Additional fields
            $output .= '<div class="d-flex flex-wrap gap-3 mt-2">';
            foreach ($displayFields as $field) {
                if (isset($record[$field]) && $field !== $titleField && $field !== $imageField) {
                    $output .= '<div><strong>' .
                        htmlspecialchars($columns[$field]['label'] ?? ucfirst(str_replace('_', ' ', $field))) .
                        ':</strong> ' . $this->renderValue($field, $record[$field], $record, $columns) . '</div>';
                }
            }
            $output .= '</div>';

            $output .= '</div>'; // End content

            // Actions area
            if ($options['show_actions'] && !empty($list->getActions())) {
                $output .= '<div class="ms-auto">';
                $output .= $this->renderActions($list, $record, $options);
                $output .= '</div>';
            }

            $output .= '</div>'; // End list item
        }

        $output .= '</div>'; // End list
        $output .= '</div>'; // End card body

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
        // Handle Bootstrap-specific formatting for special columns
        if ($column === 'status' && $value !== null) {
            $statusClass = ((string)$value === 'Published') ? 'success' : 'warning';
            return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars((string)$value) . '</span>';
        }

        // For all other columns, use the parent implementation
        return parent::renderValue($column, $value, $record, $columns);
    }

    /**
     * Render actions for a record
     *
     * @param ListInterface $list The list containing actions configuration
     * @param array<string, mixed> $record The current record data
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML for action buttons
     */
    public function renderActions(ListInterface $list, array $record, array $options = []): string
    {
        $actions = $list->getActions();

        // if (isset($actions['view'])) {
        //     // Debug the first action to see what's happening
        //     $debugIcon = $this->themeService->getIconHtml('view');
        //     error_log('Debug icon HTML: ' . htmlspecialchars($debugIcon));
        // }

        if (empty($actions)) {
            return '';
        }

        $buttonGroupClass = $this->themeService->getElementClass('button.group');

        $output = '<div class="' . $buttonGroupClass . '" role="group">';

        foreach ($actions as $name => $actionOptions) {
            $url = $actionOptions['url'] ?? '#';

            // Replace placeholders in URL
            foreach ($record as $key => $value) {
                if (is_scalar($value)) {
                    $url = str_replace('{' . $key . '}', (string)$value, $url);
                }
            }

            // $class = $actionOptions['class'] ?? $this->getActionButtonClass($name);
            $class = $this->getActionButtonClass($name);



            // Get the icon HTML from theme service
            // $iconHtml = isset($actionOptions['icon'])
            //     ? $actionOptions['icon']
            //     : $this->themeService->getIconHtml($name);

            // $name = 'dd';
            $iconHtml = $this->themeService->getIconHtml($name);


            $title = $actionOptions['title'] ?? ucfirst($name);

            if ($name === 'deletexxx') {
                // Delete button code with modal trigger
                $output .= '<button type="button" ';
                $output .= 'class="' . $class . ' delete-item-btn" ';

                // Add data attributes for the delete confirmation modal
                if (isset($actionOptions['attributes']) && is_array($actionOptions['attributes'])) {
                    foreach ($actionOptions['attributes'] as $attr => $val) {
                        // Replace placeholders with record values
                        foreach ($record as $key => $value) {
                            if (is_scalar($value)) {
                                $val = str_replace('{' . $key . '}', (string)$value, $val);
                            }
                        }
                        $output .= ' data-' . htmlspecialchars($attr) . '="' . htmlspecialchars($val) . '"';
                    }
                }

                $confirmMsg = $actionOptions['confirm'] ?? "Are you sure you want to delete this item?";
                $titleValue = $record['title'] ?? ($record['name'] ?? 'this item');
                $confirmMsg = str_replace('{title}', htmlspecialchars((string)$titleValue), $confirmMsg);

                $output .= 'data-confirm="' . htmlspecialchars($confirmMsg) . '" ';
                $output .= 'data-bs-toggle="modal" data-bs-target="#deleteItemModal" ';
                $output .= 'title="' . htmlspecialchars((string)$title) . '">';
                // CRITICAL: Use the HTML directly (not escaping)
                $output .= $iconHtml;
                $output .= '</button>';
            } else {
                // Regular link for other actions
                $output .= '<a href="' . $url . '" ';
                $output .= 'class="' . $class . '" ';
                $output .= 'title="' . htmlspecialchars((string)$title) . '">';
                // CRITICAL: Use the HTML directly (not escaping)
                $output .= $iconHtml;
                $output .= '</a>';
            }
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get action button CSS class
     *
     * @param string $actionName The action name
     * @return string The CSS class for the button
     */
    protected function getActionButtonClass(string $actionName): string
    {
        return $this->themeService->getElementClass('button.' . $actionName);
    }

    /**
     * Render pagination
     */
    public function renderPagination(ListInterface $list, array $options = []): string
    {
        $pagination = $list->getPagination();

        if (empty($pagination) || $pagination['total_pages'] <= 1) {
            return '';
        }

        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);
        $paginationClass = $this->themeService->getElementClass('pagination');

        $output = '<nav aria-label="Page navigation"><ul class="' . $paginationClass . '">';

        $baseUrl = $options['pagination_url'] ?? '';
        $currentPage = $pagination['current_page'];
        $totalPages = $pagination['total_pages'];

        // Add view type to pagination URLs if it exists
        if (!empty($options['view_type']) && $options['view_type'] !== self::VIEW_TABLE) {
            $viewParam = 'view=' . $options['view_type'];
            if (strpos($baseUrl, '?') !== false) {
                $baseUrl .= '&' . $viewParam;
            } else {
                $baseUrl .= '?' . $viewParam;
            }
        }

        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($i === $currentPage) ? ' active' : '';
            $url = str_replace('{page}', (string)$i, $baseUrl);

            $output .= '<li class="page-item' . $active . '">';
            $output .= '<a class="page-link" href="' . $url . '">' . (string)$i . '</a>';
            $output .= '</li>';
        }

        $output .= '</ul></nav>';

        return $output;
    }

    /**
     * Helper method to get image URL from record
     */
    protected function getImageUrl(string $field, $value, array $record, array $columns): string
    {
        if (empty($value)) {
            return '';
        }

        // Check if we have formatter options for this field
        $columnData = $columns[$field] ?? [];
        $options = $columnData['options'] ?? [];
        $formatters = $options['formatters'] ?? [];

        // If we have image formatter configuration, use its base URL
        if (isset($formatters['image']) && isset($formatters['image']['base_url'])) {
            return $formatters['image']['base_url'] . $value;
        }

        // Default behavior - assume value is already a URL
        return (string)$value;
    }

    /**
     * Helper to find the first field of a specific type
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
     * Render delete modal
     *
     * @param ListInterface $list
     * @return string
     */
    // future maybe once we have js?
    public function renderDeleteModal(ListView $list): string
    {
        if (!$list->hasActions() || !isset($list->getActions()['delete'])) {
            return '';
        }

        $options = $list->getActions()['delete'];
        $title = $options['modal_title'] ?? 'Confirm Delete';
        $formAction = $options['form_action'] ?? '';

        $csrfField = '';
        if ($list->hasCsrfProtection()) {
            $csrfField = '<input type="hidden" name="csrf_token" value="' .
                htmlspecialchars($list->getCsrfToken()) . '">';
        }

        return <<<HTML
        <div class="modal fade" id="deleteItemModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{$title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="deleteItemForm" method="POST" action="{$formAction}">
                        <div class="modal-body">
                            <p>Are you sure you want to delete this item?</p>
                            <input type="hidden" name="id" id="deleteItemId">
                            {$csrfField}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </div>
                   </form>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const deleteModal = document.getElementById('deleteItemModal');
                if (deleteModal) {
                    deleteModal.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;
                        const id = button.getAttribute('data-id');
                        const confirmMsg = button.getAttribute('data-confirm');

                        const modalBody = deleteModal.querySelector('.modal-body p');
                        modalBody.textContent = confirmMsg;

                        const idField = document.getElementById('deleteItemId');
                        idField.value = id;
                    });
                }
            });
        </script>
        HTML;
    }
}
