<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Material Design theme service implementation
 */
class MaterialThemeService implements ThemeServiceInterface
{
    /**
     * Element class mappings for Material Design
     *
     * @var array<string, string>
     */
    protected array $elementClasses = [
        'card' => 'mdc-card mdc-card--outlined',
        'card.header' => 'mdc-card__header',
        'card.body' => 'mdc-card__content',
        'card.footer' => 'mdc-card__actions',
        'button.group' => 'mdc-button-group',
        'button.add' => 'mdc-button mdc-button--raised mdc-theme--primary',
        'button.view' => 'mdc-icon-button mdc-theme--secondary',
        'button.edit' => 'mdc-icon-button mdc-theme--primary',
        'button.delete' => 'mdc-icon-button mdc-theme--error',
        'table' => 'mdc-data-table__table',
        'pagination' => 'mdc-pagination',
        'pagination.item' => 'mdc-pagination__item',
        'pagination.link' => 'mdc-pagination__link',
    ];

    /**
     * Icon HTML mappings with Material Design styling
     *
     * @var array<string, string>
     */
    protected array $icons = [];

    /**
     * View layouts
     *
     * @var array<string, string>
     */
    protected array $viewLayouts = [
        'default' => 'layouts/material_default',
        'minimal' => 'layouts/material_minimal',
        'admin' => 'layouts/material_admin',
    ];

    /**
     * Get CSS class for element type
     *
     * @param string $elementType The type of element
     * @param array<string, mixed> $context Additional context for determining the class
     * @return string The CSS class(es)
     */
    public function getElementClass(string $elementType, array $context = []): string
    {
        // Context-specific class variations
        if ($elementType === 'button.action') {
            $action = $context['action'] ?? 'default';
            $elementType = 'button.' . $action;
        }

        // Handle element types with context-specific variations
        if (isset($context['variant'])) {
            $variantKey = $elementType . '.' . $context['variant'];
            if (isset($this->elementClasses[$variantKey])) {
                return $this->elementClasses[$variantKey];
            }
        }

        // Return the default class for the element type or empty string if not found
        return $this->elementClasses[$elementType] ?? '';
    }

    /**
     * Get HTML for an icon
     *
     * @param string $iconName The name of the icon
     * @return string The HTML markup for the icon
     */
    public function getIconHtml(string $iconName): string
    {
        // Map common action names to Font Awesome icon classes
        $iconMap = [
            'view' => 'eye',
            'edit' => 'pencil-alt',
            'delete' => 'trash',
            'add' => 'plus',
            'table' => 'table',
            'grid' => 'th',
            'list' => 'list',
            'search' => 'search',
            'filter' => 'filter',
            'sort' => 'sort',
            'sort-asc' => 'sort-alpha-down',
            'sort-desc' => 'sort-alpha-up',
            'download' => 'download',
            'upload' => 'upload',
            'refresh' => 'sync-alt',
            'settings' => 'cog',
            'user' => 'user',
            'login' => 'sign-in-alt',
            'logout' => 'sign-out-alt',
        ];

        // Use the mapping or fallback to the icon name itself
        $iconClass = $iconMap[$iconName] ?? $iconName;

        // If we have a pre-defined HTML for this icon, use that
        if (isset($this->icons[$iconName])) {
            return $this->icons[$iconName];
        }

        // Generate Material Design styled icon HTML
        return '<i class="fas fa-' . htmlspecialchars($iconClass) . '"></i>';
    }

    /**
     * Get view layout for a specific context
     *
     * @param string $context The context name (default, minimal, admin, etc.)
     * @return string The layout template path
     */
    public function getViewLayout(string $context = 'default'): string
    {
        return $this->viewLayouts[$context] ?? $this->viewLayouts['default'];
    }

    /**
     * Set a custom icon HTML
     *
     * @param string $iconName The name of the icon
     * @param string $html The HTML markup for the icon
     * @return void
     */
    public function setIconHtml(string $iconName, string $html): void
    {
        $this->icons[$iconName] = $html;
    }

    /**
     * Set a custom element class
     *
     * @param string $elementType The type of element
     * @param string $class The CSS class(es)
     * @return void
     */
    public function setElementClass(string $elementType, string $class): void
    {
        $this->elementClasses[$elementType] = $class;
    }

    /**
     * Set a custom view layout
     *
     * @param string $context The context name
     * @param string $layout The layout template path
     * @return void
     */
    public function setViewLayout(string $context, string $layout): void
    {
        $this->viewLayouts[$context] = $layout;
    }

    /**
     * Get view layout class configuration
     */
    public function getViewLayoutClasses(string $viewType, array $options = []): array
    {
        return $this->viewLayouts[$viewType] ?? [];
    }

    /**
     * Set view layout classes for a specific layout type
     *
     * @param string $layoutName The layout name
     * @param array<string, string> $classes The CSS classes
     * @return void
     */
    public function setViewLayoutClasses(string $layoutName, array $classes): void
    {
        $this->viewLayouts[$layoutName] = $classes;
    }

    /**
     * Get badge CSS class for a semantic variant in Material Design.
     *
     * @param string $variant Semantic variant (success, danger, warning, info, secondary, etc.)
     * @return string Full CSS class string for the badge
     */
    public function getBadgeClass(string $variant): string
    {
        // Material Design often uses "chips" for badge-like elements.
        // You might define custom classes for these variants in your Material CSS.
        $baseClass = 'mdc-chip'; // Base Material Design Chip class
        $variantClass = match ($variant) {
            'success'   => 'mdc-chip--success', // Custom class you'd define for success
            'danger'    => 'mdc-chip--error',   // Custom class you'd define for error
            'warning'   => 'mdc-chip--warning', // Custom class you'd define for warning
            'info'      => 'mdc-chip--info',    // Custom class you'd define for info
            'primary'   => 'mdc-chip--primary', // Primary color from Material theme
            'secondary' => 'mdc-chip--secondary', // Secondary color from Material theme
            'light'     => 'mdc-chip--light',   // Custom class for light variant
            'dark'      => 'mdc-chip--dark',    // Custom class for dark variant
            default     => 'mdc-chip--default', // Default chip style
        };

        return $baseClass . ' ' . $variantClass;
    }
}
