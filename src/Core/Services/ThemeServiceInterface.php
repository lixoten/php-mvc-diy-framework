<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Interface for theme services - ensures consistent methods across all theme implementations
 */
interface ThemeServiceInterface
{
    /**
     * Get CSS class for element type
     *
     * @param string $elementType The type of element
     * @param array<string, mixed> $context Additional context for determining the class
     * @return string The CSS class(es)
     */
    public function getElementClass(string $elementType, array $context = []): string;

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
}
