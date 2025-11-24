<?php

declare(strict_types=1);

namespace Core\View;

/**
 * Interface for View types.
 *
 * Defines the contract for all View type classes, providing methods for
 * configuration, and building of View objects.
 */
interface ViewTypeInterface
{
    /**
     * Get the render options array for this View type.
     *
     * @return array<string, mixed> Associative array of render options.
     */
    public function getRenderOptions(): array;

    /**
     * Set the render options for the View.
     *
     * @param array<string, mixed> $renderOptions Associative array of render options.
     * @return void
     */
    public function setRenderOptions(array $renderOptions): void;

    /**
     * Merge the given rendering options into the current View's rendering options.
     *
     * @param array<string, mixed> $renderOptions Associative array of options to merge.
     * @return void
     */
    public function mergeRenderOptions(array $renderOptions): void;

    /**
     * Set the focus for the ViewType, which also sets the context for field resolution.
     *
     * @param string $pageKey The unique key for the page (e.g., 'testy_view').
     * @param string $pageName The name of the page (e.g., 'testy').
     * @param string $pageAction The current action (e.g., 'view').
     * @param string $pageFeature The feature name (e.g., 'Testy').
     * @param string $pageEntity The entity name (e.g., 'testy').
     * @return void
     */
    public function setFocus(
        string $pageKey,
        string $pageName,
        string $pageAction,
        string $pageFeature,
        string $pageEntity,
    ): void;

    /**
     * Get the fields configured for this View type.
     *
     * @return array<string> An array of field names.
     */
    public function getFields(): array;

    /**
     * Set the fields for this View type.
     *
     * Any invalid field names will be removed during validation.
     *
     * @param array<string> $fields An array of field names.
     * @return void
     */
    public function setFields(array $fields): void;

    /**
     * Validate an array of field names against the known schema for this View type.
     *
     * Sets the entity and page context on the FieldRegistryService before validation.
     * Returns only valid field names, while logging and triggering warnings for any invalid ones.
     *
     * @param array<string> $fields Array of field names to validate.
     * @return array<string> Array of valid field names.
     */
    public function validateFields(array $fields): array;

    /**
     * Get the layout configuration for this View type.
     *
     * @return array<string, mixed>
     */
    public function getLayout(): array;

    /**
     * Set the layout configuration for this View type.
     *
     * @param array<string, mixed> $layout
     * @return void
     */
    public function setLayout(array $layout): void;

    /**
     * Build the View using the provided builder.
     * Adds validated fields and layout, applies render options.
     *
     * @param ViewBuilderInterface $builder The View builder instance.
     * @return void
     */
    public function buildView(ViewBuilderInterface $builder): void;
}
