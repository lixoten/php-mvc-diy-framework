<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Vanilla CSS theme service implementation - pure CSS with no framework
 */
class VanillaThemeService implements ThemeServiceInterface
{
    /**
     * Element class mappings for Vanilla CSS
     *
     * @var array<string, string>
     */
    protected array $elementClasses = [
        'button.group' => 'vanilla-actions-group',
        'button.add' => 'vanilla-button vanilla-button-primary',
        'card' => 'vanilla-card',
        'card.header' => 'vanilla-card-header',
        'card.body' => 'vanilla-card-body',
        'card.footer' => 'vanilla-card-footer',
        'pagination' => 'vanilla-pagination',
    ];

    /**
     * Icon HTML mappings
     *
     * @var array<string, string>
     */
    protected array $icons = [
        'view' => '<i class="fas fa-eye"></i>',
        'edit' => '<i class="fas fa-pencil-alt"></i>',
        'delete' => '<i class="fas fa-trash"></i>',
        'add' => '<i class="fas fa-plus"></i>',
        'table' => '<i class="fas fa-table"></i>',
        'grid' => '<i class="fas fa-th"></i>',
        'list' => '<i class="fas fa-list"></i>',
    ];

    /**
     * View layouts
     *
     * @var array<string, string>
     */
    protected array $viewLayouts = [
        'default' => 'layouts/vanilla_default',
        'minimal' => 'layouts/vanilla_minimal',
        'admin' => 'layouts/vanilla_admin',
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
        // If the icon name exists in our icons array, return that HTML
        if (isset($this->icons[$iconName])) {
            return $this->icons[$iconName];
        }

        // For icons not in the predefined list, generate using the name as class
        return '<i class="fas fa-' . htmlspecialchars($iconName) . '"></i>';
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
