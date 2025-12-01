<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use Core\I18n\I18nTranslator;
use Core\List\ListInterface;
use Core\List\ListView;
use Core\Services\FormatterService;
use Core\Services\ThemeServiceInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Vanilla CSS list renderer - minimalist approach with pure CSS
 */
class VanillaListRenderer extends AbstractListRenderer
{
    /**
     * Constructor
     *
     * @param ThemeServiceInterface $themeService The theme service
     * @param FormatterService $formatterService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ThemeServiceInterface $themeService,
        protected I18nTranslator $translator,
        FormatterService $formatterService,
        LoggerInterface $logger,
        ContainerInterface $container
    ) {
        parent::__construct(
            $themeService,
            $translator,
            $formatterService,
            $logger,
            $container
        );

        // Vanilla CSS-specific default options
        $this->defaultOptions = array_merge($this->defaultOptions, [
            'view_type' => self::VIEW_TABLE,
            'card_shape' => 'rounded',
            'container_class' => 'vanilla-container',
            'row_class' => 'vanilla-row',
            'card_class' => 'vanilla-card',
        ]);
    }

    /**
     * Render list header
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    public function renderHeader(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $output = '<div class="vanilla-header">';
        $output .= '<h2>' . htmlspecialchars($list->getTitle()) . '</h2>';

        // Add "Add New" button if URL is provided
        if (!empty($options['add_url'])) {
            $output .= '<div class="vanilla-actions">';
            $output .= '<a href="' . $options['add_url'] . '" class="vanilla-button vanilla-button-primary">';
            $output .= $this->themeService->getIconHtml('add') . ' ';
            $output .= htmlspecialchars($options['add_button_label']);
            $output .= '</a>';
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render view toggle buttons
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    protected function renderViewToggle(ListInterface $list, array $options = []): string
    {
        $baseUrl = $options['toggle_url_base'] ?? $_SERVER['REQUEST_URI'];
        $baseUrl = strtok($baseUrl, '?'); // Remove existing query string
        $currentView = $options['view_type'];

        $output = '<div class="vanilla-toggle-container">';
        $output .= '<div class="vanilla-toggle">';

        // Table view button
        $activeClass = ($currentView === self::VIEW_TABLE) ? ' vanilla-active' : '';
        $output .= '<a href="' . $baseUrl . '?view=' . self::VIEW_TABLE . '" ';
        $output .= 'class="vanilla-toggle-button' . $activeClass . '" title="Table View">';
        $output .= $this->themeService->getIconHtml('table') . '</a>';

        // Grid view button
        $activeClass = ($currentView === self::VIEW_GRID) ? ' vanilla-active' : '';
        $output .= '<a href="' . $baseUrl . '?view=' . self::VIEW_GRID . '" ';
        $output .= 'class="vanilla-toggle-button' . $activeClass . '" title="Grid View">';
        $output .= $this->themeService->getIconHtml('grid') . '</a>';

        // List view button
        $activeClass = ($currentView === self::VIEW_LIST) ? ' vanilla-active' : '';
        $output .= '<a href="' . $baseUrl . '?view=' . self::VIEW_LIST . '" ';
        $output .= 'class="vanilla-toggle-button' . $activeClass . '" title="List View">';
        $output .= $this->themeService->getIconHtml('list') . '</a>';

        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render the main body content of the list
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    public function renderBody(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        // Get the view type from options
        $viewType = $options['view_type'] ?? self::VIEW_TABLE;

        // Add view toggle if configured
        $output = '';
        if ($options['show_view_toggle'] ?? false) {
            $output .= $this->renderViewToggle($list, $options);
        }

        // Render the appropriate view based on view_type
        $output .= match ($viewType) {
            self::VIEW_GRID => $this->renderGridView($list, $options),
            self::VIEW_LIST => $this->renderListView($list, $options),
            default => $this->renderTableView($list, $options)
        };

        return $output;
    }


    /**
     * Render table view with pure CSS
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    public function renderTableView(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $output = '<div class="vanilla-table-container">';
        $output .= '<table class="vanilla-table">';

        // Render table header
        $output .= '<thead>';
        $output .= '<tr>';

        foreach ($list->getColumns() as $name => $column) {
            $output .= '<th>' . htmlspecialchars($column['label']) . '</th>';
        }

        // Add actions column if needed
        if ($options['show_actions'] && !empty($list->getActions())) {
            $output .= '<th class="actions-column">Actions</th>';
        }

        $output .= '</tr>';
        $output .= '</thead>';

        // Render table body
        $output .= '<tbody>';

        foreach ($list->getData() as $record) {
            $output .= '<tr>';

            foreach (array_keys($list->getColumns()) as $columnName) {
                $columns = $list->getColumns();
                $value = $record[$columnName] ?? null;
                $output .= '<td>' . $this->renderValue($list->getPageName(), $columnName, $value, $record, $columns) . '</td>';
            }

            // Render actions
            if ($options['show_actions'] && !empty($list->getActions())) {
                $output .= '<td class="actions-cell">' .
                    $this->renderActions($list, $record, $options) . '</td>';
            }

            $output .= '</tr>';
        }

        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render grid view with pure CSS
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    protected function renderGridView(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $containerClass = $options['container_class'];
        $rowClass = $options['row_class'];
        $cardClass = $options['card_class'];

        $output = '<div class="' . $containerClass . '">';

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
            $output .= '<div class="' . $cardClass . '">';

            // Render image if we have an image field defined
            if ($imageField && !empty($record[$imageField])) {
                $imageValue = $record[$imageField];
                $imageUrl = $this->getImageUrl($imageField, $imageValue, $record, $columns);
                if ($imageUrl) {
                    $output .= '<div class="vanilla-card-image">';
                    $output .= '<img src="' . htmlspecialchars($imageUrl) . '" alt="' .
                        htmlspecialchars((string)($record[$titleField] ?? 'Item image')) . '">';
                    $output .= '</div>';
                }
            }

            // Card content with title and description
            $output .= '<div class="vanilla-card-content">';

            // Title
            if (isset($record[$titleField])) {
                $output .= '<h3>' . htmlspecialchars((string)$record[$titleField]) . '</h3>';
            }

            // Description fields
            foreach ($descFields as $field) {
                if (isset($record[$field]) && $field !== $titleField && $field !== $imageField) {
                    $fieldLabel = $columns[$field]['label'] ?? ucfirst(str_replace('_', ' ', $field));
                    $output .= '<p>';
                    $output .= '<span class="field-label">' . htmlspecialchars($fieldLabel) . ':</span> ';
                    $output .= $this->renderValue($list->getPageName(), $field, $record[$field], $record, $columns);
                    $output .= '</p>';
                }
            }

            $output .= '</div>';

            // Card footer with actions
            if ($options['show_actions'] && !empty($list->getActions())) {
                $output .= '<div class="vanilla-card-actions">';
                $output .= $this->renderActions($list, $record, $options);
                $output .= '</div>';
            }

            $output .= '</div>'; // End card
        }

        $output .= '</div>'; // End container

        return $output;
    }

    /**
     * Render list view with pure CSS
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    protected function renderListView(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $output = '<div class="vanilla-list">';

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
            $output .= '<div class="vanilla-list-item">';

            // Optional image
            if ($imageField && !empty($record[$imageField])) {
                $imageValue = $record[$imageField];
                $imageUrl = $this->getImageUrl($imageField, $imageValue, $record, $columns);
                if ($imageUrl) {
                    $output .= '<div class="vanilla-list-image">';
                    $output .= '<img src="' . htmlspecialchars($imageUrl) . '" alt="' .
                        htmlspecialchars((string)($record[$titleField] ?? 'Item image')) . '">';
                    $output .= '</div>';
                }
            }

            // Main content area
            $output .= '<div class="vanilla-list-content">';

            // Title field
            if ($titleField && isset($record[$titleField])) {
                $output .= '<h3>' . htmlspecialchars((string)$record[$titleField]) . '</h3>';
            }

            // Additional fields
            $output .= '<div class="vanilla-list-details">';
            foreach ($displayFields as $field) {
                if (isset($record[$field]) && $field !== $titleField && $field !== $imageField) {
                    $output .= '<div class="vanilla-list-field">';
                    $output .= '<span class="field-label">' .
                        htmlspecialchars($columns[$field]['label'] ?? ucfirst(str_replace('_', ' ', $field))) .
                        ':</span> ';
                    $output .= $this->renderValue($list->getPageName(), $field, $record[$field], $record, $columns);
                    $output .= '</div>';
                }
            }
            $output .= '</div>';

            $output .= '</div>'; // End content

            // Actions area
            if ($options['show_actions'] && !empty($list->getActions())) {
                $output .= '<div class="vanilla-list-actions">';
                $output .= $this->renderActions($list, $record, $options);
                $output .= '</div>';
            }

            $output .= '</div>'; // End list item
        }

        $output .= '</div>'; // End list

        return $output;
    }

    /**
     * Render actions for a record with vanilla CSS styling
     *
     * @param ListInterface $list The list containing actions configuration
     * @param array<string, mixed> $record The current record data
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML for action buttons
     */
    public function renderActions(ListInterface $list, array $record, array $options = []): string
    {
        $actions = $list->getActions();

        if (empty($actions)) {
            return '';
        }

        $output = '<div class="vanilla-actions-group">';

        foreach ($actions as $name => $actionOptions) {
            $url = $actionOptions['url'] ?? '#';

            // Replace placeholders in URL
            foreach ($record as $key => $value) {
                if (is_scalar($value)) {
                    $url = str_replace('{' . $key . '}', (string)$value, $url);
                }
            }

            $class = $actionOptions['class'] ?? $this->getVanillaActionButtonClass($name);

            // Get the icon HTML
            $iconHtml = isset($actionOptions['icon'])
                ? $actionOptions['icon']
                : $this->themeService->getIconHtml($name);

            $title = $actionOptions['title'] ?? ucfirst($name);

            if ($name === 'delete') {
                // Delete button code with modal trigger
                $output .= '<button type="button" ';
                $output .= 'class="' . $class . ' delete-item-btn" ';

                // Add data attributes
                if (isset($actionOptions['attributes']) && is_array($actionOptions['attributes'])) {
                    foreach ($actionOptions['attributes'] as $attr => $val) {
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
                $output .= $iconHtml;
                $output .= '</button>';
            } else {
                // Regular link for other actions
                $output .= '<a href="' . $url . '" ';
                $output .= 'class="' . $class . '" ';
                $output .= 'title="' . htmlspecialchars((string)$title) . '">';
                $output .= $iconHtml;
                $output .= '</a>';
            }
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get Vanilla CSS action button class
     *
     * @param string $actionName The action name
     * @return string The CSS class for the button
     */
    protected function getVanillaActionButtonClass(string $actionName): string
    {
        return match ($actionName) {
            'view' => 'vanilla-button vanilla-button-info',
            'edit' => 'vanilla-button vanilla-button-primary',
            'delete' => 'vanilla-button vanilla-button-danger',
            default => 'vanilla-button vanilla-button-default',
        };
    }

    /**
     * Render pagination with vanilla CSS
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    public function renderPagination(ListInterface $list, array $options = []): string
    {
        $pagination = $list->getPagination();

        if (empty($pagination) || $pagination['total_pages'] <= 1) {
            return '';
        }

        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $output = '<nav class="vanilla-pagination">';
        $output .= '<ul>';

        $baseUrl = $options['pagination_url'] ?? '';
        $currentPage = $pagination['current_page'];
        $totalPages = $pagination['total_pages'];

        // Previous button
        $prevDisabled = ($currentPage <= 1) ? ' vanilla-disabled' : '';
        $prevUrl = ($currentPage > 1) ? str_replace('{page}', (string)($currentPage - 1), $baseUrl) : '#';
        $output .= '<li class="vanilla-page-item' . $prevDisabled . '">';
        $output .= '<a class="vanilla-page-link" href="' . $prevUrl . '" aria-label="Previous">';
        $output .= '<span aria-hidden="true">&laquo;</span>';
        $output .= '</a></li>';

        // Page numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($i === $currentPage) ? ' vanilla-active' : '';
            $url = str_replace('{page}', (string)$i, $baseUrl);

            $output .= '<li class="vanilla-page-item' . $active . '">';
            $output .= '<a class="vanilla-page-link" href="' . $url . '">' . $i . '</a>';
            $output .= '</li>';
        }

        // Next button
        $nextDisabled = ($currentPage >= $totalPages) ? ' vanilla-disabled' : '';
        $nextUrl = ($currentPage < $totalPages) ? str_replace('{page}', (string)($currentPage + 1), $baseUrl) : '#';
        $output .= '<li class="vanilla-page-item' . $nextDisabled . '">';
        $output .= '<a class="vanilla-page-link" href="' . $nextUrl . '" aria-label="Next">';
        $output .= '<span aria-hidden="true">&raquo;</span>';
        $output .= '</a></li>';

        $output .= '</ul>';
        $output .= '</nav>';

        return $output;
    }

    /**
     * Render delete confirmation modal with vanilla CSS
     *
     * @param ListView $list The list view
     * @return string The HTML for the delete confirmation modal
     */
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

        // Simple modal with vanilla CSS
        $html = <<<HTML
        <div class="vanilla-modal" id="deleteItemModal" style="display: none;">
            <div class="vanilla-modal-content">
                <div class="vanilla-modal-header">
                    <h3>{$title}</h3>
                    <button type="button" class="vanilla-modal-close" data-dismiss="modal">&times;</button>
                </div>
                <form id="deleteItemForm" method="POST" action="{$formAction}">
                    <div class="vanilla-modal-body">
                        <p>Are you sure you want to delete this item?</p>
                        <input type="hidden" name="id" id="deleteItemId">
                        {$csrfField}
                    </div>
                    <div class="vanilla-modal-footer">
                        <button type="button" class="vanilla-button" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="vanilla-button vanilla-button-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            // Simple vanilla JavaScript for modal functionality
            document.addEventListener('DOMContentLoaded', function() {
                // Get modal elements
                const modal = document.getElementById('deleteItemModal');
                const closeButtons = modal.querySelectorAll('[data-dismiss="modal"]');
                const deleteButtons = document.querySelectorAll('.delete-item-btn');
                const idInput = document.getElementById('deleteItemId');

                // Setup delete buttons
                deleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const confirmMsg = this.getAttribute('data-confirm');

                        if (idInput) idInput.value = id;

                        const msgElement = modal.querySelector('.vanilla-modal-body p');
                        if (msgElement && confirmMsg) msgElement.textContent = confirmMsg;

                        modal.style.display = 'block';
                    });
                });

                // Setup close buttons
                closeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        modal.style.display = 'none';
                    });
                });

                // Close when clicking outside the modal
                window.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            });
        </script>
        HTML;

        return $html;
    }

    /**
     * Helper method to get image URL from record
     *
     * @param string $field The field name
     * @param mixed $value The field value
     * @param array<string, mixed> $record The record data
     * @param array<string, mixed> $columns The column definitions
     * @return string The image URL
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
}
