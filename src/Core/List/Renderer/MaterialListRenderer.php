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
 * Material Design list renderer
 */
class MaterialListRenderer extends AbstractListRenderer
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

        // Material Design-specific default options
        $this->defaultOptions = array_merge($this->defaultOptions, [
            'view_type'  => self::VIEW_GRID,
            'elevation'  => 2,          // Material elevation level (0-24)
            'card_shape' => 'rounded',  // rounded, rounded-lg, or sharp
            'dense'      => false,      // Use dense layout
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

        $headerClass = $this->themeService->getElementClass('card.header');
        $addButtonClass = $this->themeService->getElementClass('button.add');

        // Material Design specific classes
        $headerClass .= ' d-flex justify-content-between align-items-center py-3';

        $output = '<div class="' . $headerClass . '">';

        // Add title with Material typography class
        $output .= '<h2 class="m-0 text-primary">' . htmlspecialchars($list->getTitle()) . '</h2>';

        // Add "Add New" button if URL is provided
        if (!empty($options['add_url'])) {
            // Material Design floating action button style
            $output .= '<a href="' . $options['add_url'] . '" ';
            $output .= 'class="' . $addButtonClass
                            . ' rounded-circle d-flex align-items-center justify-content-center">';
            $output .= $this->themeService->getIconHtml('add');
            $output .= '<span class="ms-2 d-none d-md-inline">'
                            . htmlspecialchars($options['add_button_label']) . '</span>';
            $output .= '</a>';
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

        $cardBodyClass = $this->themeService->getElementClass('card.body');

        // Material Design toggle button group
        $toggleClass = 'btn-group btn-group-sm rounded-pill bg-light mb-4';

        $output = '<div class="' . $cardBodyClass . ' pt-3 pb-0">';
        $output .= '<div class="' . $toggleClass . '" role="group" aria-label="View options">';

        // Table view button
        $activeClass = ($currentView === self::VIEW_TABLE) ? ' active bg-primary text-white' : '';
        $output .= '<a href="' . $baseUrl . '?view=' . self::VIEW_TABLE . '" ';
        $output .= 'class="btn btn-sm' . $activeClass . '" title="Table View">';
        $output .= $this->themeService->getIconHtml('table') . '</a>';

        // Grid view button
        $activeClass = ($currentView === self::VIEW_GRID) ? ' active bg-primary text-white' : '';
        $output .= '<a href="' . $baseUrl . '?view=' . self::VIEW_GRID . '" ';
        $output .= 'class="btn btn-sm' . $activeClass . '" title="Grid View">';
        $output .= $this->themeService->getIconHtml('grid') . '</a>';

        // List view button
        $activeClass = ($currentView === self::VIEW_LIST) ? ' active bg-primary text-white' : '';
        $output .= '<a href="' . $baseUrl . '?view=' . self::VIEW_LIST . '" ';
        $output .= 'class="btn btn-sm' . $activeClass . '" title="List View">';
        $output .= $this->themeService->getIconHtml('list') . '</a>';

        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render list body
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    public function renderBody(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        // Use the appropriate view based on the view_type option
        switch ($options['view_type']) {
            case self::VIEW_GRID:
                return $this->renderGridView($list, $options);
            case self::VIEW_LIST:
                return $this->renderListView($list, $options);
            case self::VIEW_TABLE:
            default:
                return $this->renderTableView($list, $options);
        }
    }

    /**
     * Render table view
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    public function renderTableView(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $cardBodyClass = $this->themeService->getElementClass('card.body');

        // Material Design table with elevated card effect
        $elevation = (int)$options['elevation'];
        $tableClass = 'table table-hover ' . ($options['dense'] ? 'table-sm' : '');
        $cardClass = 'card shadow-' . $elevation . ' ' . $options['card_shape'];

        $output = '<div class="' . $cardBodyClass . '">';
        $output .= '<div class="' . $cardClass . ' overflow-hidden">';
        $output .= '<div class="table-responsive">';
        $output .= '<table class="' . $tableClass . ' mb-0">';

        // Render table header
        $output .= '<thead class="bg-light">';
        $output .= '<tr>';
        foreach ($list->getColumns() as $name => $column) {
            $output .= '<th class="text-primary">' . htmlspecialchars($column['label']) . '</th>';
        }

        // Add actions column if needed
        if ($options['show_actions'] && !empty($list->getActions())) {
            $output .= '<th class="text-end">Actions</th>';
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
                $output .= '<td class="text-end">' . $this->renderActions($list, $record, $options) . '</td>';
            }

            $output .= '</tr>';
        }
        $output .= '</tbody>';
        $output .= '</table>';
        $output .= '</div>'; // End table-responsive
        $output .= '</div>'; // End card
        $output .= '</div>'; // End card-body

        return $output;
    }

    /**
     * Render grid view with Material Design cards
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    protected function renderGridView(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $cardBodyClass = $this->themeService->getElementClass('card.body');
        $elevation = (int)$options['elevation'];
        $cardShape = $options['card_shape'];

        $output = '<div class="' . $cardBodyClass . '">';
        $output .= '<div class="row g-4">'; // Material Design grid with proper gutters

        // Get columns to display
        $columns = $list->getColumns();

        // Get primary image field, title field and description fields
        $imageField = $options['grid_image_field'] ?? $this->findFirstFieldOfType($columns, 'image');
        $titleField = $options['grid_title_field']
                        ?? $this->findFirstFieldOfType($columns, 'title')
                        ?? array_key_first($columns);
        $descFields = $options['grid_description_fields'] ?? array_slice(array_keys($columns), 1, 2);

        // Render each record as a Material Design card
        foreach ($list->getData() as $record) {
            $output .= '<div class="col-12 col-sm-6 col-md-4 col-lg-3">';
            $output .= '<div class="card shadow-' . $elevation . ' h-100 ' . $cardShape . ' overflow-hidden">';

            // Render image if we have an image field defined - Material Design style
            if ($imageField && !empty($record[$imageField])) {
                $imageValue = $record[$imageField];
                $imageUrl = $this->getImageUrl($imageField, $imageValue, $record, $columns);
                if ($imageUrl) {
                    $output .= '<div class="card-img-top-container" style="height: 180px; overflow: hidden;">';
                    $output .= '<img src="' . htmlspecialchars($imageUrl) . '" class="card-img-top w-100 h-100" ';
                    $output .= 'style="object-fit: cover;" alt="'
                                        . htmlspecialchars((string)($record[$titleField] ?? 'Item image')) . '">';
                    $output .= '</div>';
                }
            }

            // Card body with title and description - Material Design typography
            $output .= '<div class="card-body ' . ($options['dense'] ? 'p-3' : '') . '">';

            // Title
            if (isset($record[$titleField])) {
                $output .= '<h5 class="card-title fw-500 mb-2">' .
                    htmlspecialchars((string)$record[$titleField]) . '</h5>';
            }

            // Description fields
            foreach ($descFields as $field) {
                if (isset($record[$field]) && $field !== $titleField && $field !== $imageField) {
                    $fieldLabel = $columns[$field]['label'] ?? ucfirst(str_replace('_', ' ', $field));
                    $output .= '<p class="card-text mb-2">';
                    $output .= '<small class="text-secondary">' . htmlspecialchars($fieldLabel) . ':</small> ';
                    $output .= $this->renderValue($list->getPageName(), $field, $record[$field], $record, $columns);
                    $output .= '</p>';
                }
            }

            $output .= '</div>'; // End card body

            // Card footer with actions - Material Design button style
            if ($options['show_actions'] && !empty($list->getActions())) {
                $output .= '<div class="card-footer border-top-0 bg-white d-flex justify-content-end gap-2 ' .
                    ($options['dense'] ? 'p-2' : '') . '">';
                $output .= $this->renderActions($list, $record, $options);
                $output .= '</div>';
            }

            $output .= '</div>'; // End card
            $output .= '</div>'; // End col
        }

        $output .= '</div>'; // End row
        $output .= '</div>'; // End card body

        return $output;
    }

    /**
     * Render list view with Material Design list items
     *
     * @param ListInterface $list The list object
     * @param array<string, mixed> $options Rendering options
     * @return string The rendered HTML
     */
    protected function renderListView(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $cardBodyClass = $this->themeService->getElementClass('card.body');
        $elevation = (int)$options['elevation'];
        $cardShape = $options['card_shape'];

        $output = '<div class="' . $cardBodyClass . '">';
        $output .= '<div class="list-group shadow-' . $elevation . ' ' . $cardShape . ' overflow-hidden">';

        // Get columns to display
        $columns = $list->getColumns();
        $displayFields = $options['list_display_fields'] ?? array_keys($columns);

        // Get primary fields
        $imageField = $options['list_image_field'] ?? $this->findFirstFieldOfType($columns, 'image');
        $titleField = $options['list_title_field']
                        ?? $this->findFirstFieldOfType($columns, 'title')
                        ?? array_key_first($columns);

        // Render each record as a Material Design list item
        foreach ($list->getData() as $record) {
            $output .= '<div class="list-group-item list-group-item-action ' .
                ($options['dense'] ? 'py-2' : 'py-3') . ' d-flex align-items-center">';

            // Optional image - Material Design avatar style
            if ($imageField && !empty($record[$imageField])) {
                $imageValue = $record[$imageField];
                $imageUrl = $this->getImageUrl($imageField, $imageValue, $record, $columns);
                if ($imageUrl) {
                    $output .= '<div class="me-3">';
                    $output .= '<img src="' . htmlspecialchars($imageUrl) . '"
                        class="rounded-circle" width="48" height="48" style="object-fit: cover;"
                        alt="' . htmlspecialchars((string)($record[$titleField] ?? 'Item image')) . '">';
                    $output .= '</div>';
                }
            }

            // Main content area - Material Design typography
            $output .= '<div class="flex-grow-1">';

            // Title field
            if ($titleField && isset($record[$titleField])) {
                $output .= '<h6 class="mb-1 fw-500">' .
                    htmlspecialchars((string)$record[$titleField]) . '</h6>';
            }

            // Additional fields
            $output .= '<div class="d-flex flex-wrap gap-3">';
            foreach ($displayFields as $field) {
                if (isset($record[$field]) && $field !== $titleField && $field !== $imageField) {
                    $output .= '<div><small class="text-secondary">' .
                        htmlspecialchars($columns[$field]['label'] ?? ucfirst(str_replace('_', ' ', $field))) .
                        ':</small> ' . $this->renderValue($list->getPageName(), $field, $record[$field], $record, $columns) . '</div>';
                }
            }
            $output .= '</div>';

            $output .= '</div>'; // End content

            // Actions area - Material Design button style
            if ($options['show_actions'] && !empty($list->getActions())) {
                $output .= '<div class="ms-auto">';
                $output .= $this->renderActions($list, $record, $options);
                $output .= '</div>';
            }

            $output .= '</div>'; // End list item
        }

        $output .= '</div>'; // End list-group
        $output .= '</div>'; // End card body

        return $output;
    }

    /**
     * Render actions for a record - Material Design styled buttons
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

        // Material Design button group
        $buttonGroupClass = 'd-flex gap-2';

        $output = '<div class="' . $buttonGroupClass . '">';

        foreach ($actions as $name => $actionOptions) {
            $url = $actionOptions['url'] ?? '#';

            // Replace placeholders in URL
            foreach ($record as $key => $value) {
                if (is_scalar($value)) {
                    $url = str_replace('{' . $key . '}', (string)$value, $url);
                }
            }

            // Material Design button styles
            $materialClass = $this->getMaterialActionButtonClass($name);
            $class = $actionOptions['class'] ?? $materialClass;

            // Get the icon HTML from theme service
            $iconHtml = isset($actionOptions['icon'])
                ? $actionOptions['icon']
                : $this->themeService->getIconHtml($name);

            $title = $actionOptions['title'] ?? ucfirst($name);

            if ($name === 'delete') {
                // Delete button code with modal trigger - Material style
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
                // Use the HTML directly (not escaping)
                $output .= $iconHtml;
                $output .= '</button>';
            } else {
                // Regular link for other actions - Material style
                $output .= '<a href="' . $url . '" ';
                $output .= 'class="' . $class . '" ';
                $output .= 'title="' . htmlspecialchars((string)$title) . '">';
                // Use the HTML directly (not escaping)
                $output .= $iconHtml;
                $output .= '</a>';
            }
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get Material Design action button CSS class
     *
     * @param string $actionName The action name
     * @return string The CSS class for the button
     */
    protected function getMaterialActionButtonClass(string $actionName): string
    {
        // Material Design specific button styles
        $baseClass = 'btn btn-icon btn-sm';

        switch ($actionName) {
            case 'view':
                return $baseClass . ' btn-outline-info rounded-circle';
            case 'edit':
                return $baseClass . ' btn-outline-primary rounded-circle';
            case 'delete':
                return $baseClass . ' btn-outline-danger rounded-circle';
            default:
                return $baseClass . ' btn-outline-secondary rounded-circle';
        }
    }

    /**
     * Render pagination with Material Design styling
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

        // Material Design pagination
        $paginationClass = 'pagination justify-content-center mt-4';

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

        // Previous button - Material style
        $prevDisabled = ($currentPage <= 1) ? ' disabled' : '';
        $prevUrl = str_replace('{page}', (string)($currentPage - 1), $baseUrl);
        $output .= '<li class="page-item' . $prevDisabled . '">';
        $output .= '<a class="page-link rounded-circle mx-1" href="' . $prevUrl . '" aria-label="Previous">';
        $output .= '<span aria-hidden="true">&laquo;</span>';
        $output .= '</a></li>';

        // Page numbers - Material style
        for ($i = 1; $i <= $totalPages; $i++) {
            // Only show a subset of pages for large page counts
            if ($totalPages > 7 && ($i > 2 && $i < $currentPage - 1 || $i > $currentPage + 1 && $i < $totalPages - 1)) {
                if ($i === 3 || $i === $totalPages - 2) {
                    $output .= '<li class="page-item disabled"><span class="page-link border-0">...</span></li>';
                }
                continue;
            }

            $active = ($i === $currentPage) ? ' active' : '';
            $url = str_replace('{page}', (string)$i, $baseUrl);

            // Material Design rounded pill style for page numbers
            $output .= '<li class="page-item' . $active . '">';
            $output .= '<a class="page-link rounded-circle mx-1" href="' . $url . '">' . $i . '</a>';
            $output .= '</li>';
        }

        // Next button - Material style
        $nextDisabled = ($currentPage >= $totalPages) ? ' disabled' : '';
        $nextUrl = str_replace('{page}', (string)($currentPage + 1), $baseUrl);
        $output .= '<li class="page-item' . $nextDisabled . '">';
        $output .= '<a class="page-link rounded-circle mx-1" href="' . $nextUrl . '" aria-label="Next">';
        $output .= '<span aria-hidden="true">&raquo;</span>';
        $output .= '</a></li>';

        $output .= '</ul></nav>';

        return $output;
    }

    /**
     * Render delete modal with Material Design styling
     *
     * @param ListView $list The list object
     * @return string The rendered HTML
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

        // Material Design styled modal
        return <<<HTML
        <div class="modal fade" id="deleteItemModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 shadow">
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title fw-bold">{$title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="deleteItemForm" method="POST" action="{$formAction}">
                        <div class="modal-body py-3">
                            <div class="d-flex">
                                <div class="text-danger me-3">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                                <p class="mb-0">Are you sure you want to delete this item?</p>
                            </div>
                            <input type="hidden" name="id" id="deleteItemId">
                            {$csrfField}
                        </div>
                        <div class="modal-footer flex-column border-top-0">
                            <button type="submit" class="btn btn-lg btn-danger w-100">Delete</button>
                            <button type="button" class="btn btn-link w-100" data-bs-dismiss="modal">Cancel</button>
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
