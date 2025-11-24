<?php

declare(strict_types=1);

namespace Core\View\Renderer;

use Core\View\ViewInterface;
use Core\Form\Field\FieldInterface;

/**
 * Interface for View renderers.
 *
 * Defines the contract for rendering a View object, its fields, and layout
 * into HTML, separating presentation logic from the View data structure.
 */
interface ViewRendererInterface
{
    /**
     * Render a complete View object.
     *
     * @param ViewInterface $view The View data container.
     * @param array<string, mixed> $options Rendering options.
     * @return string The rendered HTML for the entire view.
     */
    public function renderView(ViewInterface $view, array $options = []): string;

    /**
     * Render a single field within the context of a view.
     *
     * @param ViewInterface $view The parent View object.
     * @param FieldInterface $field The field to render.
     * @param array<string, mixed> $options Rendering options specific to the field.
     * @return string The rendered HTML for the single field.
     */
    public function renderField(ViewInterface $view, FieldInterface $field, array $options = []): string;

    /**
     * Render a layout section of the view.
     *
     * @param ViewInterface $view The parent View object.
     * @param array<string, mixed> $section The layout section definition.
     * @param array<string, mixed> $options Rendering options for the section.
     * @return string The rendered HTML for the layout section.
     */
    public function renderLayoutSection(ViewInterface $view, array $section, array $options = []): string;

    /**
     * Render a field's value with appropriate formatting.
     *
     * @param string $fieldName The name of the field.
     * @param mixed $value The raw value to render.
     * @param array<string, mixed> $recordData The complete record data.
     * @param array<string, mixed> $fieldDef The field's definition.
     * @return string The formatted value as HTML.
     */
    public function renderValue(string $fieldName, mixed $value, array $recordData, array $fieldDef): string;
}
