<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use App\Helpers\DebugRt;
use Core\List\ListInterface;
use Core\List\ListView;
use Core\Services\ThemeServiceInterface;
use Core\I18n\I18nTranslator;
use App\Enums\Url;
use Core\Services\FormatterService;
use Psr\Log\LoggerInterface;

/**
 * Bootstrap list renderer
 */
class BootstrapListRenderer extends AbstractListRenderer
{
    /**
     * Constructor
     */
    public function __construct(
        protected ThemeServiceInterface $themeService,
        private I18nTranslator $translator,
        protected FormatterService $formatterService,
        protected LoggerInterface $logger
    ) {
        parent::__construct($themeService, $formatterService, $logger);
        $this->defaultOptions['view_type'] = self::VIEW_TABLE; // Fik - Override List View Default - GRID TABLE LIST
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

        $output .= '<h2>' . htmlspecialchars($list->getTitle()) . '</h2>';

        // Add "Add New" button if URL is provided
        if (($options['show_action_add'] ?? false) && !empty($options['add_url'])) {
            $output .= '<a href="' . htmlspecialchars($options['add_url'] ?? '') . '" class="' . $addButtonClass . '">';
            $output .= $this->themeService->getIconHtml('add') . ' ' .
                //htmlspecialchars($options['add_button_label'] ?? '');
                htmlspecialchars($this->translator->get($options['add_button_label'], pageName: $list->getPageName()));

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
        $currentView = $options['view_type'] ?? self::VIEW_TABLE; // Default if not set
        $currentQueryParams = $options['current_query_params'] ?? []; // ✅ NEW: Get ALL current query parameters

        $cardBodyClass = $this->themeService->getElementClass('card.body');
        $toggleClass = $this->themeService->getElementClass('view.toggle');

        $output = '<div class="' . $cardBodyClass . ' pt-0">';
        $output .= '<div class="' . $toggleClass . '" role="group" aria-label="View options">';


        $viewTypes = [self::VIEW_TABLE, self::VIEW_GRID, self::VIEW_LIST];
        $viewIcons = [
            self::VIEW_TABLE => 'table',
            self::VIEW_GRID => 'grid',
            self::VIEW_LIST => 'list',
        ];
        $viewTitles = [
            self::VIEW_TABLE => 'button.view_table',
            self::VIEW_GRID  => 'button.view_grid',
            self::VIEW_LIST  => 'button.view_list',
        ];

        // ✅ Get the list URL enum and route type from options
        $listUrlEnum = $options['url_enums']['list'] ?? null;
        $routeType = $options['route_type'] ?? 'core';

        if (!$listUrlEnum instanceof Url) {
            // Log an error or throw an exception if the listUrlEnum is not properly set
            // $this->logger->error('BootstrapListRenderer: listUrlEnum not provided or invalid for view toggle.');
            // FUCK no logger you idiot.
            DebugRt::j('0', '$listUrlEnum', $listUrlEnum);
            return ''; // Return empty string to prevent rendering broken links
        }

        foreach ($viewTypes as $viewType) {
            $activeClass = ($currentView === $viewType) ? ' active' : '';

            // Start with all current query parameters
            $toggleParams = $currentQueryParams;
            // Override/set the 'view' parameter for this specific toggle button
            $toggleParams['view'] = $viewType;

            // ✅ Generate the URL using the Url enum, which handles path and query parameters
            $toggleUrl = $listUrlEnum->url($toggleParams, $routeType);

            $output .= '<a href="' . htmlspecialchars($toggleUrl) . '" ';
            $output .= 'class="btn btn-outline-secondary' . $activeClass . '" title="' .
                htmlspecialchars($this->translator->get($viewTitles[$viewType], pageName: $list->getPageName())) . '">';
                                                        //  htmlspecialchars($viewTitles[$viewType]) . '">';
            $output .= $this->themeService->getIconHtml($viewIcons[$viewType]) . '</a>';
        }

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
            // $output .= '<th>' . htmlspecialchars($column['label']) . '</th>';
            $temp = htmlspecialchars($this->translator->get($column['label'], pageName: $list->getPageName()));
            $output .= '<th>' . $temp . '</th>';
        }

        // Add actions column if needed
        if ($options['show_actions'] && !empty($list->getActions())) {
            // $actionsLabel = $this->translator->get($list->getPageName() . '.actions', pageName: $list->getPageName());
            $actionsLabel = htmlspecialchars($this->translator->get('actions', pageName: $list->getPageName()));
            $output .= "<th>$actionsLabel</th>";
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
        $descFields = $options['grid_description_fields'] ?? array_keys($columns);

        // Render each record as a card
        foreach ($list->getData() as $record) {
            $output .= '<div class="' . $viewLayout['item'] . '">';
            $output .= '<div class="' . $viewLayout['card'] . '">';

            // Render image if we have an image field defined
            if ($imageField && !empty($record[$imageField])) {
                //$imageValue = $record[$imageField];
                // $imageUrl = $this->getImageUrl($imageField, $imageValue, $record, $columns);
                $imageUrl = $this->renderValue($imageField, $record[$imageField], $record, $columns);
                if ($imageUrl) {
                    $output .= '<img src="' . htmlspecialchars($imageUrl) . '" class="' .
                        $viewLayout['image'] . '" alt="' .
                        htmlspecialchars((string)($record[$titleField] ?? 'Item image')) . '">';
                }
            }

            // Title
            if (isset($record[$titleField])) {
                $output .= '<h5 class="' . $viewLayout['title'] . '">' .
                    $this->renderValue($titleField, $record[$titleField], $record, $columns) . '</h5>';
                    // htmlspecialchars((string)$record[$titleField]) . '</h5>';
                unset($record['title']);
            }

            // Description fields
            foreach ($descFields as $field) {
                // if (isset($record[$field]) && $field !== $titleField && $field !== $imageField) {
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
                //$imageValue = $record[$imageField];
                // $imageUrl = $this->getImageUrl($imageField, $imageValue, $record, $columns);
                $imageUrl = $this->renderValue($imageField, $record[$imageField], $record, $columns);
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

            $class = $this->getActionButtonClass($name);

            $iconHtml = $this->themeService->getIconHtml($name);


            $title = $actionOptions['label'] ?? ucfirst($name);
            $title = htmlspecialchars($this->translator->get($title, pageName: $list->getPageName()));


            if ($name === 'deleteXxx') {
                // Delete button code with modal trigger
                $output .= '<button type="button" ';
                $output .= 'class="' . htmlspecialchars($class) . ' delete-item-btn" '; // CRITICAL: Add defensive check

                // Add data attributes for the delete confirmation modal
                if (isset($actionOptions['attributes']) && is_array($actionOptions['attributes'])) {
                    foreach ($actionOptions['attributes'] as $attr => $val) {
                        // Replace placeholders with record values
                        foreach ($record as $key => $value) {
                            if (is_scalar($value)) {
                                $val = str_replace('{' . $key . '}', (string)$value, $val);
                            }
                        }
                        $output .= ' data-' . htmlspecialchars($attr ?? '') . '="' . htmlspecialchars($val ?? '') . '"';
                    }
                }

                $confirmMsg = $actionOptions['confirm'] ?? "Are you sure you want to delete this item?";
                $titleValue = $record['title'] ?? ($record['name'] ?? 'this item');
                $confirmMsg = str_replace('{title}', htmlspecialchars((string)$titleValue), $confirmMsg);

                $output .= 'data-confirm="' . htmlspecialchars($confirmMsg) . '" '; // CRITICAL: Add defensive check
                $output .= 'data-bs-toggle="modal" data-bs-target="#deleteItemModal" ';
                $output .= 'title="' . htmlspecialchars((string)$title) . '">';

                // CRITICAL: Use the HTML directly (not escaping)
                $output .= $iconHtml;
                $output .= '</button>';
            } else {
                // Regular link for other actions
                $output .= '<a href="' . htmlspecialchars($url) . '" '; // CRITICAL: Add defensive check
                $output .= 'class="' . htmlspecialchars($class) . '" '; // CRITICAL: Add defensive check
                $output .= 'title="' . htmlspecialchars((string)$title) . '">'; // CRITICAL: Add defensive check
                $output .= $iconHtml;
                $output .= '</a>';
            }
        }

        $output .= '</div>';

        return $output;
    }


    /**
     * Render pagination
     */
    public function renderPagination(ListInterface $list, array $options = []): string
    {
        $paginationData = $list->getPagination();

        if (empty($paginationData) || !($paginationData['showPagination'] ?? false)) {
            return '';
        }

        $paginationClass = $this->themeService->getElementClass('pagination');
        $output = '<nav aria-label="Page navigation"><ul class="' . $paginationClass . '">';

        // Render "Previous" button
        if ($paginationData['hasPrevious'] ?? false) {
            $output .= '<li class="page-item">';
            $output .= '<a class="page-link" href="' . htmlspecialchars($paginationData['previous']['href']) . '">' .
                       htmlspecialchars($paginationData['previous']['text'] ?? 'Previous') . '</a>';
            $output .= '</li>';
        } else {
            $output .= '<li class="page-item disabled">';
            $output .= '<span class="page-link">Previous</span>';
            $output .= '</li>';
        }

        // Render first page if not in window
        if (isset($paginationData['showFirstPage']) && $paginationData['showFirstPage']) {
            $firstPageLink = $paginationData['firstPageLink'] ?? null;
            if ($firstPageLink) {
                $output .= '<li class="page-item">';
                $output .= '<a class="page-link" href="' . htmlspecialchars($firstPageLink['href']) . '">' .
                           htmlspecialchars($firstPageLink['text'] ?? '1') . '</a>';
                $output .= '</li>';
            }
            if (($paginationData['windowStart'] ?? 1) > 2) { // Add ellipsis if there's a gap between 1 and window start
                $output .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Render page numbers from the structured data (windowed pages)
        foreach ($paginationData['pages'] as $page) {
            $active = ($page['active'] ?? false) ? ' active' : '';
            $disabled = ($page['disabled'] ?? false) ? ' disabled' : '';
            $output .= '<li class="page-item' . $active . $disabled . '">';
            $output .= '<a class="page-link" href="' . htmlspecialchars($page['href']) . '">' .
                       htmlspecialchars($page['text'] ?? (string)($page['number'])) . '</a>';
            $output .= '</li>';
        }


        // Render last page if not in window
        if (isset($paginationData['showLastPage']) && $paginationData['showLastPage']) {
            if (($paginationData['windowEnd'] ?? 0) < ($paginationData['total'] ?? 0) - 1) { // Add ellipsis if gap
                $output .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $lastPageLink = $paginationData['lastPageLink'] ?? null;
            if ($lastPageLink) {
                $output .= '<li class="page-item">';
                $output .= '<a class="page-link" href="' . htmlspecialchars($lastPageLink['href']) . '">' .
                           htmlspecialchars($lastPageLink['text'] ?? (string)($paginationData['total'] ?? '')) . '</a>';
                $output .= '</li>';
            }
        }


        // Render "Next" button
        if ($paginationData['hasNext'] ?? false) {
            $output .= '<li class="page-item">';
            $output .= '<a class="page-link" href="' . htmlspecialchars($paginationData['next']['href']) . '">' .
                       htmlspecialchars($paginationData['next']['text'] ?? 'Next') . '</a>';
            $output .= '</li>';
        } else {
            $output .= '<li class="page-item disabled">';
            $output .= '<span class="page-link">Next</span>';
            $output .= '</li>';
        }

        $output .= '</ul></nav>';

        return $output;
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
        $title = htmlspecialchars($options['modal_title'] ?? 'Confirm Delete');
        $formAction = htmlspecialchars($options['form_action'] ?? '');

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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CancelFook</button>
                            <button type="submit" class="btn btn-danger">DeleteFook3</button>
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

    // 642 585
}
