<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Bootstrap-specific theme service implementation
 */
class BootstrapThemeService implements ThemeServiceInterface
{
    /**
     * Default element classes
     *
     * @var array<string, string>
     */
    protected array $elementClasses = [
        'navbar' => 'navbar navbar-expand-lg navbar-light', // singular, matches your markup
        'navbar.brand' => 'navbar-brand',
        'navbar.nav' => 'navbar-nav',
        'navbar.item' => 'nav-item',
        'navbar.link' => 'nav-link',
        'navbar.toggler' => 'navbar-toggler',
        'navbar.collapse' => 'collapse navbar-collapse',
        'navbar.container' => 'container-fluid',

        // ...existing classes...
        'active' => 'active',
        'subnav' => 'bg-light py-2 border-bottom',
        // Optionally add aliases for nav.item and nav.link if needed:
        'nav.item' => 'nav-item',
        'nav.link' => 'nav-link',


        'table' => 'table table-striped',
        'card' => 'card mb-4',
        'card.header' => 'card-header d-flex justify-content-between align-items-center',
        'card.body' => 'card-body',
        'pagination' => 'pagination',
        'button.add' => 'btn btn-light btn-sm text-primary border border-primary',
        'button.view' => 'btn btn-info',
        'button.edit' => 'btn btn-primary',
        'button.delete' => 'btn btn-danger',
        'button.group' => 'btn-group btn-group-sm',
        'view.toggle' => 'btn-group btn-group-sm mb-3',
    ];

    /**
     * Default icon HTML markup
     *
     * @var array<string, string>
     */
    protected array $icons = [
        'view' => '<i class="fas fa-eye"></i>',
        'edit' => '<i class="fas fa-edit"></i>',
        'delete' => '<i class="fas fa-trash"></i>',
        'add' => '<i class="fas fa-plus"></i>',
        'table' => '<i class="fas fa-table"></i>',
        'grid' => '<i class="fas fa-th"></i>',
        'list' => '<i class="fas fa-list"></i>',
    ];

    /**
     * View layout class configurations
     *
     * @var array<string, array<string, string>>
     */
    protected array $viewLayouts = [
        'table' => [
            'container' => 'table table-striped',
            'row' => '',
            'cell' => '',
            'header' => '',
        ],
        'grid' => [
            'container' => 'row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4',
            'item' => 'col',
            'card' => 'card h-100',
            'image' => 'card-img-top img-fluid',
            'body' => 'card-body',
            'title' => 'card-title',
            'text' => 'card-text',
            'footer' => 'card-footer d-flex justify-content-between align-items-center',
        ],
        'list' => [
            'container' => 'list-group',
            'item' => 'list-group-item d-flex justify-content-between align-items-center',
            'content' => 'me-auto',
            'title' => 'mb-1',
        ],
    ];


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
     * Get CSS class for a UI element
     */
    public function getElementClass(string $elementType, array $context = []): string
    {
        return $this->elementClasses[$elementType] ?? '';
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
        ];

        // Use the mapping or fallback to the icon name itself
        $iconClass = $iconMap[$iconName] ?? $iconName;

        // Return complete HTML tag
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
        $layouts = [
            'default' => 'layouts/bootstrap/template_default',
            'minimal' => 'layouts/bootstrap_minimal',
            'admin' => 'layouts/bootstrap_admin',
            'error' => 'layouts/bootstrap/template_error',
            'abend' => 'layouts/bootstrap/template_abend',

        ];

        return $layouts[$context] ?? $layouts['default'];
    }

    /**
     * Get view layout class configuration
     *
     * @param string $viewType The view type
     * @return array<string, string> The layout classes
     */
    public function getViewLayoutClasses(string $viewType): array
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
}
