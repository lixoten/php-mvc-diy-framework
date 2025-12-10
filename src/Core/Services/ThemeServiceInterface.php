<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Interface for theme services - ensures consistent methods across all theme implementations
 */
interface ThemeServiceInterface
{
    /**
     * Get CSS class for a UI element
     *
     * @param string $elementType The type of element
     * @param array<string, mixed> $context Additional context (unused in Bootstrap implementation)
     * @return string|null The CSS class(es), or null if not defined
     */
    public function getElementClass(string $elementType, array $context = []): ?string;

    /**
     * Get HTML for an icon
     *
     * @param string $iconName The name of the icon
     * @return string The HTML markup for the icon
     */
    public function getIconHtml(string $iconName): string;

    /**
     * Get view layout for a specific context
     *
     * @param string $context The context name (default, minimal, admin, etc.)
     * @return string The layout template path
     */
    public function getViewLayout(string $context = 'default'): string;

    /**
     * Set a custom icon HTML
     *
     * @param string $iconName The name of the icon
     * @param string $html The HTML markup for the icon
     * @return void
     */
    public function setIconHtml(string $iconName, string $html): void;

    /**
     * Set a custom element class
     *
     * @param string $elementType The type of element
     * @param string $class The CSS class(es)
     * @return void
     */
    public function setElementClass(string $elementType, string $class): void;


    /**
     * Get view layout class configuration
     */
    public function getViewLayoutClasses(string $viewType): array;


    /**
     * Set view layout classes for a specific layout type
     *
     * @param string $layoutName The layout name
     * @param array<string, string> $classes The CSS classes
     * @return void
     */
    public function setViewLayoutClasses(string $layoutName, array $classes): void;


    /**
     * Get badge CSS class for a semantic variant.
     *
     * @param string $variant Semantic variant (success, danger, warning, info, secondary, etc.)
     * @return string Full CSS class string for the badge (must not be null)
     */
    public function getBadgeClass(string $variant): string;


    /**
     * Resolves a semantic button variant into framework-specific CSS classes.
     *
     * @param string $variant The semantic variant (e.g., 'primary', 'secondary', 'danger').
     * @return string The CSS classes for the specified button variant.
     */
    public function getButtonClass(string $variant): string;

    public function getAjaxSpinnerHtml(string $message): string;
}
