<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use Core\I18n\I18nTranslator;
use Core\List\ListInterface;
use Core\Services\FormatterService;
use Core\Services\ThemeServiceInterface;
use App\Enums\Url;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract list renderer with framework-agnostic rendering logic
 */
abstract class AbstractListRenderer implements ListRendererInterface
{
    /**
     * View type constants
     */
    public const VIEW_TABLE = 'table';
    public const VIEW_GRID  = 'grid';
    public const VIEW_LIST  = 'list';

    /**
     * Default options
     */
    protected array $defaultOptions = [
        'from'                  => 'AbstractListRenderer-defaultOptions',
        'css_framework'         => '',
        'show_title_heading'    => false,
        'title_heading_level'   => 'h2',
        'title_heading_class'   => null,

        'show_actions_label'    => true, // Table-view: Show "Actions" column header for this entity

        'show_actions'          => true,
        'show_action_add'       => false,
        'show_action_edit'      => false,
        'show_action_del'       => false,
        'show_action_view'      => false,
        'show_action_status'    => false,

        'show_pagination'       => true,
        'show_view_toggle'      => true,
        'view_type'             => 'table',
    ];

    /**
     * Enable strict mode for formatter validation.
     * When true, invalid formatter chains will throw an exception instead of just logging a warning.
     * Override this in child classes or set via environment variable for per-environment control.
     *
     * @var bool
     */
    protected bool $strictFormatterValidation = false; // Default: false (warnings only)


    /**
     * Constructor
     *
     * @param ThemeServiceInterface $themeService
     * @param FormatterService $formatterService
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected ThemeServiceInterface $themeService,
        protected I18nTranslator $translator,
        protected FormatterService $formatterService,
        protected LoggerInterface $logger,
        protected ContainerInterface $container

    ) {
        // Todo Change it to use configService  (single source of truth)
        // Use $_ENV instead of getenv() (Dotenv populates $_ENV by default)
        $env = $_ENV['STRICT_FORMATTER_VALIDATION'] ?? $_SERVER['STRICT_FORMATTER_VALIDATION'] ?? null;

        if ($env !== null) {
            $this->strictFormatterValidation = ($env === '1' || strtolower($env) === 'true');
        }
        // Todo use : $this->strictFormatterValidation = $this->config->get('app.strict_formatter_validation', false);
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
     * Render list header
     */
    public function renderHeader(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $output = '';

        $headerClass = $this->themeService->getElementClass('card.header');
        $output .= '<div class="' . $headerClass . '">';

        // Render the form heading if configured
        $showTitleHeading = !empty($options['show_title_heading']);
        if ($showTitleHeading) {
            // Resolve title heading level, defaulting to 'h2'
            $headingLevelCandidate = $options['title_heading_level'] ?? 'h2';
            $headingLevel = (is_string($headingLevelCandidate) && preg_match('/^h[1-6]$/i', $headingLevelCandidate))
                            ? $headingLevelCandidate
                            : 'h2';

            $headingClass = $options['title_heading_class'] ?? $this->themeService->getElementClass('title.heading');
            $headingText  = $this->translator->get('list.title', pageName: $list->getPageName());

            $output .= "<{$headingLevel} class=\"{$headingClass}\">" .
                       $headingText .
                       "</{$headingLevel}>";
        }

        // Add "Add New" button if URL is provided
        if (($options['show_action_add'] ?? false) && !empty($options['add_url'])) {
            $addButtonLabel = $this->translator->get('button.add', pageName: $list->getPageName());

            $baseButtonClass = $this->themeService->getElementClass('button.add');
            $marginStartAutoClass = $this->themeService->getElementClass('layout.margin_start_auto');

            $buttonClass = trim($baseButtonClass . ' ' . ($marginStartAutoClass ?? ''));

            $output .= '<a href="' . htmlspecialchars($options['add_url'] ?? '') . '" class="' . $buttonClass . '">';
            $output .= $this->themeService->getIconHtml('add') . ' ' . $addButtonLabel;
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
        $currentQueryParams = $options['current_query_params'] ?? []; // Get ALL current query parameters

        $cardBodyClass = $this->themeService->getElementClass('card.body');
                $paddingTopZeroClass = $this->themeService->getElementClass('layout.padding_top_zero') ?? null;
        $toggleClass = $this->themeService->getElementClass('view.toggle');
        $viewToggleButtonClass = $this->themeService->getElementClass('view.toggle_button');
        $activeClass = $this->themeService->getElementClass('view.toggle_button.active');

        $output = '<div class="' . $cardBodyClass . ($paddingTopZeroClass ? ' ' .  $paddingTopZeroClass : '') . '">';
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

        // Get the list URL enum and route type from options
        $listUrlEnum = $options['url_enums']['list'] ?? null;
        $routeType = $options['route_type'] ?? 'core';
        // $routeType = 'store';

        if (!$listUrlEnum instanceof Url) {
            // Log an error or throw an exception if the listUrlEnum is not properly set
            // $this->logger->error('BootstrapListRenderer: listUrlEnum not provided or invalid for view toggle.');
            // FUCK no logger you idiot.
            $this->logger->warning('AbstractListRenderer: listUrlEnum not provided or invalid for view toggle.');
            //DebugRt::j('0', '$listUrlEnum', $listUrlEnum);
            return ''; // Return empty string to prevent rendering broken links
        }

        foreach ($viewTypes as $viewType) {
            $isActive = ($currentView === $viewType) ? ' ' . $activeClass : '';

            // Start with all current query parameters
            $toggleParams = $currentQueryParams;
            // Override/set the 'view' parameter for this specific toggle button
            $toggleParams['view'] = $viewType;

            // Generate the URL using the Url enum, which handles path and query parameters
            $toggleUrl = $listUrlEnum->url($toggleParams, $routeType);

            $output .= '<a href="' . htmlspecialchars($toggleUrl) . '" ';
            $output .= 'class="' . $viewToggleButtonClass . $isActive . '" title="' .
                                  $this->translator->get($viewTitles[$viewType], pageName: $list->getPageName()) . '">';
            $output .= $this->themeService->getIconHtml($viewIcons[$viewType]) . '</a>';
        }

        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }



    /**
     * Render table view
     */
    public function renderTableView(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $cardBodyClass = $this->themeService->getElementClass('card.body');
        $tableClass = $this->themeService->getElementClass('table');
        $actionsHeaderAlignmentClass = $this->themeService->getElementClass('table.header.actions_alignment');
        $visuallyHiddenClass = $this->themeService->getElementClass('visually_hidden');

        $output = '<div class="' . $cardBodyClass . '">';
        $output .= '<table class="' . $tableClass . '">';

        // Render table header
        $output .= '<thead><tr>';
        foreach ($list->getColumns() as $name => $column) {
            // findme column head text
            $label = $this->translator->get($name.'.list.label', pageName: $list->getPageName());
            $output .= "<th>{$label}</th>";
        }

        // Add actions column if needed
        if ($options['show_actions'] && !empty($list->getActions())) {
            $actionsLabel = $this->translator->get('list.actions', pageName: $list->getPageName());
            if ($options['show_actions_label'] ?? false) {

                $output .= <<<HTML
                <th scope="col" class="{$actionsHeaderAlignmentClass}">{$actionsLabel}</th>
                HTML;

            } else {
                // Default: Hidden label for accessibility, empty visual header
                $output .= <<<HTML
                <th scope="col" class="{$actionsHeaderAlignmentClass}">
                    <span class="{$visuallyHiddenClass}">{$actionsLabel}</span>
                </th>
                HTML;
            }
        }

        $output .= '</tr></thead>';

        // Render table body
        $output .= '<tbody>';
        foreach ($list->getData() as $record) {
            $output .= '<tr>';

            foreach (array_keys($list->getColumns()) as $columnName) {
                $columns = $list->getColumns();
                $value = $record[$columnName] ?? null;
                $output .= '<td>' .
                                $this->renderValue($list->getPageName(), $columnName, $value, $record, $columns) .
                            '</td>';
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
        $imageThumbnailClass = $this->themeService->getElementClass('image.thumbnail');
        $imageThumbnailStyle = $this->themeService->getElementClass('image.thumbnail_style');
        $flexGapClass = $this->themeService->getElementClass('layout.flex_gap');

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
            $output .= '<div class="' . $viewLayout['item'] . '">'; // beg col
            $output .= '<div class="' . $viewLayout['card'] . '">'; // beg card

            // Render image if we have an image field defined
            if ($imageField && !empty($record[$imageField])) {
                //$imageValue = $record[$imageField];
                // $imageUrl = $this->getImageUrl($imageField, $imageValue, $record, $columns);
                $imageUrl = $this->renderValue($list->getPageName(), $imageField, $record[$imageField], $record, $columns);
                if ($imageUrl) {
                    $output .= '<img src="' . htmlspecialchars($imageUrl) . '" class="' .
                        htmlspecialchars($viewLayout['image'] . ' ' . $imageThumbnailClass) . '" style="' .
                        $imageThumbnailStyle . '" alt="' .
                        htmlspecialchars((string)($record[$titleField] ?? 'Item image')) . '">';
                }
            }

            // Title
            if (isset($record[$titleField])) {
                $fieldLabel = $this->translator->get($titleField.'.list.label', pageName: $list->getPageName());
                $fieldValue = $this->renderValue(
                    $list->getPageName(),
                    $titleField,
                    $record[$titleField],
                    $record,
                    $columns
                );

                $output .= '<h5 class="' . $viewLayout['title'] . '">';
                      $output .= $fieldLabel . ': ' . $fieldValue ;
                $output .= '</h5>';
                unset($record['title']); // Consider if this unset is intentional and desired for grid/list views
            }

            // Description fields
            foreach ($descFields as $field) {
                // if (isset($record[$field]) && $field !== $titleField && $field !== $imageField) {
                if ($field !== $titleField && $field !== $imageField) {
                    // $fieldLabel = $columns[$field]['label'];
                    // $name = $columns[$field];
                    $fieldLabel = $this->translator->get($field.'.list.label', pageName: $list->getPageName());
                    $output .= '<p class="' . $viewLayout['text'] . '">';
                    $output .= '<strong>' . htmlspecialchars($fieldLabel) . ':</strong> ';
                    $output .= $this->renderValue($list->getPageName(), $field, $record[$field], $record, $columns);
                    $output .= '</p>';
                }
            }



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
        $imageThumbnailClass = $this->themeService->getElementClass('image.thumbnail');
        $imageThumbnailStyle = $this->themeService->getElementClass('image.thumbnail_style');
        $flexGapClass = $this->themeService->getElementClass('layout.flex_row_gap');
        $marginStartAutoClass = $this->themeService->getElementClass('layout.margin_start_auto');

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
                $imageUrl = $this->renderValue(
                    $list->getPageName(),
                    $imageField,
                    $record[$imageField],
                    $record, $columns
                );
                if ($imageUrl) {
                    $output .= '<img src="' . htmlspecialchars($imageUrl) . '"
                        class="' . $imageThumbnailClass . '" style="' . $imageThumbnailStyle . '"
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
            $output .= '<div class="' . $flexGapClass . '">';
            foreach ($displayFields as $field) {
                if (isset($record[$field]) && $field !== $titleField && $field !== $imageField) {
                    $output .= '<div><strong>' .
                        htmlspecialchars($columns[$field]['label'] ?? ucfirst(str_replace('_', ' ', $field))) .
                        ':</strong> ' .
                        $this->renderValue($list->getPageName(), $field, $record[$field], $record, $columns) . '</div>';
                }
            }
            $output .= '</div>';

            $output .= '</div>'; // End content

            // Actions area
            if ($options['show_actions'] && !empty($list->getActions())) {
                $output .= '<div class="' . $marginStartAutoClass . '">';
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

        // Define dynamic modal trigger attributes
        $modalId = '#deleteItemModal'; // This ID is still specific, but consistent across frameworks for now.

        $modalToggleAttribute = $this->themeService->getElementClass('modal.toggle_attribute');
        $modalToggleValue = $this->themeService->getElementClass('modal.toggle_value') ?? '';
        $modalTargetAttribute = $this->themeService->getElementClass('modal.target_attribute');



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

            // findme - button text
            $title = $this->translator->get('button.' . $name, pageName: $list->getPageName());

            if ($name === 'delete') {
                // Delete button code with modal trigger
                $output .= '<button type="button" ';
                $output .= 'class="' . htmlspecialchars($class) . ' ' .
                                       $this->themeService->getElementClass('button.delete_trigger') . '" ';

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

                $output .= 'data-confirm="' . htmlspecialchars($confirmMsg) . '" ';
                $output .= $modalToggleAttribute . '="' . $modalToggleValue . '" ' .
                           $modalTargetAttribute . '="' . $modalId . '" ';
                $output .= 'title="' . htmlspecialchars((string)$title) . '">';

                $output .= $iconHtml;
                $output .= '</button>';
            } else {
                // Regular link for other actions
                $output .= '<a href="' . htmlspecialchars($url) . '" ';
                $output .= 'class="' . htmlspecialchars($class) . '" ';
                $output .= 'title="' . htmlspecialchars((string)$title) . '">';
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


        $pageItemClass = $this->themeService->getElementClass('pagination.item');
        $pageLinkClass = $this->themeService->getElementClass('pagination.link');
        $disabledClass = $this->themeService->getElementClass('pagination.disabled');


        // Render "Previous" button
        if ($paginationData['hasPrevious'] ?? false) {
            $output .= '<li class="' . $pageItemClass . '">';
            $output .= '<a class="' . $pageLinkClass . '" href="' .
                                  htmlspecialchars($paginationData['previous']['href']) . '">' .
                                  htmlspecialchars($paginationData['previous']['text'] ?? 'Previous') . '</a>';
            $output .= '</li>';
        } else {
            $output .= '<li class="' . $pageItemClass . ' ' . $disabledClass . '">';
            $output .= '<span class="' . $pageLinkClass . '">Previous</span>';
            $output .= '</li>';
        }

        // Render first page if not in window
        if (isset($paginationData['showFirstPage']) && $paginationData['showFirstPage']) {
            $firstPageLink = $paginationData['firstPageLink'] ?? null;
            if ($firstPageLink) {
                $output .= '<li class="' . $pageItemClass . '">';
                $output .= '<a class="' . $pageLinkClass . '" href="' .
                          htmlspecialchars($firstPageLink['href']) . '">' .
                          htmlspecialchars($firstPageLink['text'] ?? '1') . '</a>';
                $output .= '</li>';
            }
            if (($paginationData['windowStart'] ?? 1) > 2) { // Add ellipsis if there's a gap between 1 and window start
                                $output .= '<li class="' . $pageItemClass . ' ' . $disabledClass . '"><span class="' .
                                $pageLinkClass . '">...</span></li>';
            }
        }

        // Render page numbers from the structured data (windowed pages)
        foreach ($paginationData['pages'] as $page) {
            $active = ($page['active'] ?? false) ? ' active' : '';
            $disabled = ($page['disabled'] ?? false) ? ' disabled' : '';
            $output .= '<li class="' . $pageItemClass . $active . $disabled . '">';
            $output .= '<a class="' . $pageLinkClass . '" href="' . htmlspecialchars($page['href']) . '">' .
                    htmlspecialchars($page['text'] ?? (string)($page['number'])) . '</a>';
            $output .= '</li>';
        }


        // Render last page if not in window
        if (isset($paginationData['showLastPage']) && $paginationData['showLastPage']) {
            if (($paginationData['windowEnd'] ?? 0) < ($paginationData['total'] ?? 0) - 1) {
                $output .= '<li class="' . $pageItemClass . ' ' . $disabledClass . '"><span class="' .
                                                                                   $pageLinkClass . '">...</span></li>';
            }
            $lastPageLink = $paginationData['lastPageLink'] ?? null;
            if ($lastPageLink) {
                $output .= '<li class="' . $pageItemClass . '">';
                $output .= '<a class="' . $pageLinkClass . '" href="' . htmlspecialchars($lastPageLink['href']) . '">' .
                        htmlspecialchars($lastPageLink['text'] ?? (string)($paginationData['total'] ?? '')) . '</a>';
                $output .= '</li>';
            }
        }


        // Render "Next" button
        if ($paginationData['hasNext'] ?? false) {
            $output .= '<li class="' . $pageItemClass . '">';
            $output .= '<a class="' . $pageLinkClass . '" href="' .
                                       htmlspecialchars($paginationData['next']['href']) . '">' .
                                       htmlspecialchars($paginationData['next']['text'] ?? 'Next') . '</a>';
            $output .= '</li>';
        } else {
            $output .= '<li class="' . $pageItemClass . ' ' . $disabledClass . '">';
            $output .= '<span class="' . $pageLinkClass . '">Next</span>';
            $output .= '</li>';
        }

        $output .= '</ul></nav>';

        return $output;
    }




    /** {@inheritdoc} */
    public function renderValue(
        string $pageName,
        string $column,
        $value,
        array $record,
        array $columns = []
    ): string {
        if ($value === null) {
            return '';
        }

        // We need to preserve original value for Enum Classes, Since they use translationKey. We need the
        // original value(code) to find variant, but we apply valiant to value(translated value)
        $originalValue = $value;


        // findme - read formatter from list
        $columnConfig = $columns[$column] ?? [];
        // $formattersConfig = $columnConfig['formatters'] ?? [];
        // $listConfig = $columnConfig['list'] ?? [];
        //$listConfig = $columnConfig ?? [];
        $formattersConfig = $columnConfig['formatters'] ?? [];

        // Validate formatter chains to prevent accidental incompatibilities
        if (is_array($formattersConfig) && count($formattersConfig) > 1) {
            $this->validateFormatterChain($column, $formattersConfig);
        }

        // Apply class-based formatters via FormatterService directly in the abstract class.
        // This logic is generic and not Bootstrap-specific.
        if (is_array($formattersConfig)) {
            foreach ($formattersConfig as $formatterName => $formatterOptions) {
                try {
                    // ✅ Always inject common context and services into formatter options
                    $formatterOptions['page_name'] = $pageName;




                    // ✅ NEW/IMPROVED LOGIC: Enhance image_link formatter options with runtime data
                    // This block populates formatter options that are dynamic, based on the current record.
                    if ($formatterName === 'image_link') {
                        $formatterOptions['record'] = $record; // Pass the entire record for URL placeholders ({id})
                        $formatterOptions['store_id'] = 6; // $record['store_id'] ?? $this->currentContext->getStoreId(); // Resolve store_id

                        // Resolve alt_text from alt_field if specified in config, otherwise fall back
                        $altFieldName = $formatterOptions['alt_field'] ?? 'title'; // Default alt_field from config
                        $formatterOptions['alt_text'] = $record[$altFieldName] ?? 'Image'; // Dynamically set alt_text
                    }




                    // // This is a hack
                    // // This was the only way to be about to apply BadgeCollectionFormatter on top of ArrayFormatter
                    // if ($formatterName === 'badge_collection') {
                    //     $value = $originalValue;
                    // }
                    // Apply each formatter in sequence with the (now fully resolved) options
                    $value = $this->formatterService->format($formatterName, $value, $formatterOptions, $originalValue);

                } catch (\Core\Exceptions\FormatterNotFoundException $e) {
                    $this->logger->warning(sprintf(
                        'Formatter "%s" not found for column "%s". Error: %s',
                        $formatterName,
                        $column,
                        $e->getMessage()
                    ));
                    // Continue with the unformatted value or apply default HTML escaping
                } catch (\Throwable $e) {
                    $this->logger->error(sprintf(
                        'Error applying formatter "%s" to column "%s": %s',
                        $formatterName,
                        $column,
                        $e->getMessage()
                    ));
                    // Continue with the unformatted value or apply default HTML escaping
                }
            }
            // After applying all formatters, return the final value.
            // We trust the formatters have handled HTML safety.
            return (string)$value;
        }
        ///////////////////////////////////


        // ⚠️ LEGACY: Support old-style single 'formatter' closure (deprecated pattern)
        $columnOptions = $columnConfig['options'] ?? [];
        if (isset($columnOptions['formatter']) && is_callable($columnOptions['formatter'])) {
            return $columnOptions['formatter']($value, $record);
        }

        // Apply default 'text' formatter for columns without explicit formatters
        try {
            return $this->formatterService->format('text', $value);
        } catch (\Core\Exceptions\FormatterNotFoundException $e) {
            $this->logger->error('Default "text" formatter not found! This is a critical configuration error.');
            // Fallback to raw htmlspecialchars if text formatter is missing
            return is_string($value) ? htmlspecialchars($value) : (string)$value;
        }
    }


    /**
     * ✅ ENHANCED: Validate a formatter chain to prevent common configuration mistakes.
     *
     * This method checks if HTML-producing formatters (like 'badge', 'image_link')
     * are followed by HTML-escaping formatters (like 'text'), which would break the output.
     *
     * @param string $column The column name being validated
     * @param array<string, mixed> $formattersConfig The formatters configuration for this column
     * @return void
     */
    protected function validateFormatterChain(string $column, array $formattersConfig): void
    {
        $formatterNames = array_keys($formattersConfig);

        // ✅ NEW: Define formatters that produce safe HTML (isSafeHtml = true)
        // As you add more HTML-producing formatters, add them to this list.
        $htmlProducingFormatters = [
            'badge',       // Produces <span class="badge">
            'image_link',  // Produces <a><img></a>
            'link',        // Produces <a href="">
            // Add future HTML formatters here as you create them
        ];

        // ✅ NEW: Define formatters that escape HTML (isSafeHtml = false)
        // These formatters will break any HTML from previous formatters in the chain.
        $htmlEscapingFormatters = [
            'text',        // Escapes all HTML via htmlspecialchars()
            'truncate',    // May escape if it inherits from TextFormatter behavior
            // Add future escaping formatters here
        ];

        // Check if we have any HTML-producing formatter
        $htmlFormattersInChain = array_intersect($htmlProducingFormatters, $formatterNames);
        $escapingFormattersInChain = array_intersect($htmlEscapingFormatters, $formatterNames);

        // ✅ NEW: If both types exist in the chain, determine the order and warn
        if (!empty($htmlFormattersInChain) && !empty($escapingFormattersInChain)) {
            // Get the first occurrence of each type
            $firstHtmlFormatterIndex = null;
            $firstEscapingFormatterIndex = null;

            foreach ($formatterNames as $index => $name) {
                if (in_array($name, $htmlProducingFormatters, true) && $firstHtmlFormatterIndex === null) {
                    $firstHtmlFormatterIndex = $index;
                }
                if (in_array($name, $htmlEscapingFormatters, true) && $firstEscapingFormatterIndex === null) {
                    $firstEscapingFormatterIndex = $index;
                }
            }


            // ⚠️ PROBLEM: HTML formatter comes BEFORE escaping formatter
            if ($firstHtmlFormatterIndex < $firstEscapingFormatterIndex) {
                $htmlFormatterName = $formatterNames[$firstHtmlFormatterIndex];
                $escapingFormatterName = $formatterNames[$firstEscapingFormatterIndex];

                $errorMessage = sprintf(
                    'Invalid formatter chain for column "%s": ' .
                    'Formatter "%s" (HTML-producing) is followed by "%s" (HTML-escaping). ' .
                    'The "%s" formatter will escape the HTML produced by "%s", causing it to display as plain text. ' .
                    'Solution: Remove "%s" from the formatters array, or move it BEFORE "%s" in your field ' .
                    'config file.',
                    $column,
                    $htmlFormatterName,
                    $escapingFormatterName,
                    $escapingFormatterName,
                    $htmlFormatterName,
                    $escapingFormatterName,
                    $htmlFormatterName
                );

                // ✅ NEW: Strict mode - throw exception to prevent app from running
                if ($this->strictFormatterValidation) {
                    throw new \Core\Exceptions\InvalidFormatterChainException($errorMessage);
                }

                // ⚠️ Non-strict mode - just log a warning
                $this->logger->warning('⚠️ CONFIGURATION WARNING: ' . $errorMessage);
            }
        }
    }


    /**
     * Helper to find the first field of a specific type within the list's column definitions.
     * This logic is framework-agnostic and belongs in the abstract renderer.
     *
     * @param array<string, mixed> $columns Column definitions
     * @param string $type The field type to look for (e.g., 'image', 'title')
     * @return string|null The field name if found, null otherwise
     */
    protected function findFirstFieldOfType(array $columns, string $type): ?string
    {
        foreach ($columns as $name => $column) {
            // Check the explicit 'type' option first
            $options = $column['options'] ?? [];
            $fieldType = $options['type'] ?? '';

            if ($fieldType === $type) {
                return $name;
            }

            // Fallback: Check field name for common patterns (e.g., 'image_url' for 'image' type)
            // This is a heuristic and might need refinement based on your naming conventions.
            if (strpos($name, $type) !== false) {
                return $name;
            }
        }

        return null;
    }


    /**
     * Get action button CSS class from ThemeService.
     * This logic is framework-agnostic as it delegates to the ThemeService abstraction.
     *
     * @param string $actionName The action name (e.g., 'edit', 'delete', 'view')
     * @return string The CSS class for the button
     */
    protected function getActionButtonClass(string $actionName): string
    {
        return $this->themeService->getElementClass('button.' . $actionName);
    }


    // /**
    //  * Render view toggle buttons
    //  */
    // abstract protected function renderViewToggle(ListInterface $list, array $options): string;

    // /**
    //  * Render grid view with cards
    //  */
    // abstract protected function renderGridView(ListInterface $list, array $options): string;

    // /**
    //  * Render list view with full-width items
    //  */
    // abstract protected function renderListView(ListInterface $list, array $options): string;
}
