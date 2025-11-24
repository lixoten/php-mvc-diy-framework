<?php

declare(strict_types=1);

namespace Core\List;

use App\Enums\Url;

/**
 * Interface for list types.
 *
 * Defines the contract for all list type classes, including
 * configuration, rendering, and building of lists.
 */
interface ListTypeInterface
{
    /**
     * Set the focus for the ListType, which also sets the context for field resolution.
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
     * Get the options array for this list type.
     *
     * @return array Associative array of options.
     */
    public function getOptions(): array;



    /**
     * Get the render options array for this list type.
     *
     * @return array Associative array of render options.
     */
    public function getRenderOptions(): array;

    /**
     * Set the render options for the list.
     *
     * @param array $renderOptions Associative array of render options.
     * @return void
     */
    public function setRenderOptions(array $renderOptions): void;

    /**
     * Merge additional render options with the existing ones.
     *
     * This method allows for incrementally adding or overriding rendering options,
     * typically for specific display adjustments.
     *
     * @param array<string, mixed> $renderOptions Associative array of render options to merge.
     * @return void
     */
    public function mergeRenderOptions(array $renderOptions): void;

    /**
     * Get the columns for this list type.
     *
     * Should return an array of column keys (field names) to be displayed.
     *
     * @return array List of default column names.
     */
    public function getFields(): array;

    /**
     * Validates list fields and replaces then with giving set.
     *
     * Any invalid fields names will be removed.
     *
     * @param array $fields Associative array of list fields.
     * @return void
     */
    public function setFields(array $fields): void;

    /**
     * Get pagination options for the list.
     *
     * @return array<string, mixed> Associative array of pagination options.
     */
    public function getPaginationOptions(): array;

    /**
     * Set pagination options for the list.
     *
     * @param array<string, mixed> $options Associative array of pagination options.
     * @return void
     */
    public function setPaginationOptions(array $options): void;



    /**
     * Build the list using field definitions from FieldRegistryService.
     *
     * Applies fallback logic: page context → entity → base.
     *
     * @param ListBuilderInterface $builder The list builder instance.
     * @return void
     */
    public function buildList(ListBuilderInterface $builder): void;

    /**
     * Overrides parts of the list's configuration with new values.
     *
     * This method allows controllers or other components to dynamically adjust
     * list options, pagination options, render options, or fields after initial setup.
     * The provided $options array should contain keys that correspond to the
     * list's configuration segments (e.g., 'options', 'pagination', 'render_options', 'list_fields').
     *
     * @param array<string, mixed> $options An associative array of configuration overrides.
     * @return void
     */
    public function overrideConfig(array $options): void;
}
