<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use Core\List\ListInterface;
use Core\List\ListView;

/**
 * Bootstrap list renderer
 */
class BootstrapListRenderer implements ListRendererInterface
{
    /**
     * Default options
     */
    protected array $defaultOptions = [
        'table_class' => 'table table-striped',
        'card_class' => 'card mb-4',
        'card_header_class' => 'card-header d-flex justify-content-between align-items-center',
        'card_body_class' => 'card-body',
        'pagination_class' => 'pagination',
        'show_actions' => true,
        'show_pagination' => true,
        'add_button_label' => 'Add New', // ref-add_button_label
        // 'add_button_class' => 'btn btn-success btn-lg',
        // 'add_button_class' => 'btn btn-outline-primary btn -lg text-primary',
        'add_button_class' => 'btn btn-light btn-sm text-primary border border-primary',
        // 'add_button_class' => 'btn btn-warning btn-lg text-dark',
        'action_group_class' => 'btn-group btn-group-sm',
        'test_value' => 'low',          // RemoveMe remove This was for me later for testing
        'test_value_only_low' => 'low'  // RemoveMe remove This was for me later for testing
    ];

    /**
     * Default action classes
     */
    protected array $actionClasses = [
        'view' => 'btn btn-info',
        'edit' => 'btn btn-primary',
        'delete' => 'btn btn-danger',
    ];

    /**
     * Default action icons
     */
    protected array $actionIcons = [
        'view' => '<i class="fas fa-eye"></i>',
        'edit' => '<i class="fas fa-edit"></i>',
        'delete' => '<i class="fas fa-trash"></i>',
        'add' => '<i class="fas fa-plus"></i>',
    ];

    /**
     * Render a full list
     */
    public function renderList(ListInterface $list, array $options = []): string
    {
        // defaultOptions - Another Level of hardcoded defaults that 
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $output = '<div class="' . $options['card_class'] . '">';

        // Render header with title and add button if provided
        $output .= $this->renderHeader($list, $options);

        // Render body with table
        $output .= $this->renderBody($list, $options);

        // Render pagination if enabled
        if ($options['show_pagination'] && !empty($list->getPagination())) {
            $output .= $this->renderPagination($list, $options);
        }

        $output .= '</div>';

        $output .= $this->renderDeleteModal($list);


        return $output;
    }

    /**
     * Render list header
     */
    public function renderHeader(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $output = '<div class="' . $options['card_header_class'] . '">';

        //$renderOptionsxxxx = $list->getRenderOptions();




        // Add title
        $output .= '<h2>' . htmlspecialchars($list->getTitle()) . '</h2>';

        // Add "Add New" button if URL is provided
        if (!empty($options['add_url'])) {
            $output .= '<a href="' . $options['add_url'] . '" class="' . $options['add_button_class'] . '">';
            $output .= $this->actionIcons['add'] . ' ' . htmlspecialchars($options['add_button_label']);
            $output .= '</a>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render list body
     */
    public function renderBody(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $output = '<div class="' . $options['card_body_class'] . '">';
        $output .= '<table class="' . $options['table_class'] . '">';

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
                // TAG: albumtag1
                $columns = $list->getColumns();
                // $options - used to be passes straight

                $value = $record[$columnName] ?? null;
                // $output .= '<td>' . $this->renderValue($columnName, $value, $record, $options) . '</td>';
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
     * Render pagination
     */
    public function renderPagination(ListInterface $list, array $options = []): string
    {
        $pagination = $list->getPagination();

        if (empty($pagination) || $pagination['total_pages'] <= 1) {
            return '';
        }

        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $output = '<nav aria-label="Page navigation"><ul class="' . $options['pagination_class'] . '">';

        $baseUrl = $options['pagination_url'] ?? '';
        $currentPage = $pagination['current_page'];
        $totalPages = $pagination['total_pages'];

        for ($i = 1; $i <= $totalPages; $i++) {
            $active = ($i === $currentPage) ? ' active' : '';
            $url = str_replace('{page}', (string)$i, $baseUrl);

            $output .= '<li class="page-item' . $active . '">';
            // Make sure we convert $i to string before using htmlspecialchars
            $output .= '<a class="page-link" href="' . $url . '">' . (string)$i . '</a>';
            $output .= '</li>';
        }

        $output .= '</ul></nav>';

        return $output;
    }

    /**
     * Render column value
     */
    public function renderValue(string $column, $value, array $record, array $options = []): string
    {
        if ($value === null) {
            return '';
        }

        // TAG: albumtag1 - this options is a bit misleading, it is actually all columns
        $columns = $options[$column] ?? [];
        //$columnOptions = $columns[$column] ?? [];
        $columnOptions = $columns['options'] ?? [];

        // Handle specific column formatters
        switch ($column) {
            case 'status':
                $statusClass = ($value == 'Published') ? 'success' : 'warning';
                return '<span class="badge bg-' . $statusClass . '">' . htmlspecialchars($value) . '</span>';

            default:
                // TAG: albumtag1
                // Apply any custom formatters defined in options
                if (isset($columnOptions['formatter']) && is_callable($columnOptions['formatter'])) {
                    return $columnOptions['formatter']($value, $record);
                }

                // Default formatting
                return is_string($value) ? htmlspecialchars($value) : (string)$value;
        }
    }


    /**
     * Render actions for a record
     */
    public function renderActions(ListInterface $list, array $record, array $options = []): string
    {
        $actions = $list->getActions();

        if (empty($actions)) {
            return '';
        }

        $output = '<div class="' . $options['action_group_class'] . '" role="group">';

        foreach ($actions as $name => $actionOptions) {
            $url = $actionOptions['url'] ?? '#';

            // Replace placeholders in URL
            foreach ($record as $key => $value) {
                if (is_scalar($value)) {
                    $url = str_replace('{' . $key . '}', (string)$value, $url);
                }
            }

            $class = $actionOptions['class'] ?? $this->actionClasses[$name] ?? 'btn btn-secondary';
            $icon = $actionOptions['icon'] ?? $this->actionIcons[$name] ?? '';
            $title = $actionOptions['title'] ?? ucfirst($name);

            if ($name === 'delete') {
                // // For delete actions, we'll use a button with data attributes for confirmation
                // $output .= '<button type="button" ';
                // $output .= 'class="' . $class . ' delete-item-btn" ';
                // // Make sure to cast these values to string explicitly
                // $output .= 'data-id="' . htmlspecialchars((string)($record['id'] ?? '')) . '" ';
                // $output .= 'data-title="' . htmlspecialchars((string)($record['title'] ?? '')) . '" ';
                // $output .= 'title="' . htmlspecialchars((string)$title) . '">';
                // $output .= $icon;
                // $output .= '</button>';

                // $confirmMessage = $actionOptions['confirm'] ?? 'Are you sure you want to delete this item?';
                // $output .= '<a href="' . $url . '" ';
                // $output .= 'class="' . $class . '" ';
                // $output .= 'onclick="return confirm(\'' . htmlspecialchars($confirmMessage) . '\');" ';
                // $output .= 'title="' . htmlspecialchars((string)$title) . '">';
                // $output .= $icon;
                // $output .= '</a>';

                //$id = $record['id'] ?? null;
                $title = $record['name'] ?? ($record['title'] ?? 'this item');
                $confirmMsg = $actionOptions['confirm'] ?? "Are you sure you want to delete {$title}?";

                $output .= '<button type="button" ';
                $output .= 'class="' . $class . ' delete-item-btn" ';
                // $output .= 'data-id="' . htmlspecialchars((string)$id) . '" ';
                // $output .= 'data-title="' . htmlspecialchars((string)$title) . '" ';
                if (isset($actionOptions['attributes']) && is_array($actionOptions['attributes'])) {
                    foreach ($actionOptions['attributes'] as $attr => $val) {
                        // Replace placeholders with actual record values
                        foreach ($record as $key => $value) {
                            if (is_scalar($value)) {
                                $val = str_replace('{' . $key . '}', (string)$value, $val);
                            }
                        }
                        $output .= ' data-' . htmlspecialchars($attr) . '="' . htmlspecialchars($val) . '"';
                    }
                }
                $output .= 'data-confirm="' . htmlspecialchars($confirmMsg) . '" ';
                $output .= 'data-bs-toggle="modal" data-bs-target="#deleteItemModal" ';
                $output .= 'title="' . htmlspecialchars((string)$actionOptions['label']) . '">';
                // $output .= $icon;
                //if (isset($icon)) {
                    $output .= '<i class="' . htmlspecialchars($icon) . '"></i> ';
                //}
                $output .= '</button>';

            } else {
                // Regular link for other actions
                $output .= '<a href="' . $url . '" ';
                $output .= 'class="' . $class . '" ';
                $output .= 'title="' . htmlspecialchars((string)$title) . '">';
                // $output .= $icon;
                //$output .= '<i class="' . $icon . '"</i> ';
                //if (isset($icon)) {
                    $output .= '<i class="' . htmlspecialchars($icon) . '"></i> ';
                //} else {
                //    $output .= htmlspecialchars((string)$actionOptions['label']);
                //}
                $output .= '</a>';
            }
        }

        $output .= '</div>';

        return $output;
    }

    protected function renderActionButtons(ListView $list, array $row): string
    {
        $output = '<div class="btn-group" role="group">';

        foreach ($list->getActions() as $name => $options) {
            if ($name === 'delete') {
                $output .= $this->renderDeleteButton($list, $row, $options);
            } else {
                // Other action buttons
            }
        }

        $output .= '</div>';
        return $output;
    }

    protected function renderDeleteButton(ListView $list, array $row, array $options): string
    {
        $id = $row[$options['id_field'] ?? 'id'];
        $label = $options['label'] ?? 'Delete';
        $confirmMsg = $options['confirm_message'] ?? 'Are you sure?';

        $csrfField = '';
        if ($list->hasCsrfProtection()) {
            $csrfField = '<input type="hidden" name="csrf_token" value="' .
                htmlspecialchars($list->getCsrfToken()) . '">';
        }

        return <<<HTML
        <button type="button" class="btn btn-sm btn-danger delete-item-btn"
            data-id="{$id}"
            data-confirm="{$confirmMsg}"
            data-bs-toggle="modal"
            data-bs-target="#deleteItemModal">
            {$label}
        </button>

        <!-- We'll also need a modal template included once for the whole list -->
        HTML;
    }


    // Add method to render the delete modal once for the whole list
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

        //$formAction = $options['form_action'] ?? '';

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
