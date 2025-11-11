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
    // // public function getCreateUrlEnum(): Url;

    // /**
    //  * Set default options, URLs, and render options for the list.
    //  *
    //  * Should be called to initialize or reset the list's configuration.
    //  *
    //  * @return void
    //  */
    // public function setUrlDependentRenderOptions(): void;


    // /**
    //  * Merge the given options into the current list options.
    //  *
    //  * @param array $options Associative array of options to merge.
    //  * @return void
    //  */
    // public function mergeOptions(array $options): void;


    // /**
    //  * Get the unique name for this list type.
    //  *
    //  * Child classes must override this to provide a unique name
    //  *
    //  * @return string The list name.
    //  */
    // public function getListName(): string;


    /**
     * Get the options array for this list type.
     *
     * @return array Associative array of options.
     */
    public function getOptions(): array;

    /**
     * Set the options array for this list type.
     *
     * @param array $options Associative array of options.
     * @return void
     */
    public function setOptions(array $options): void;

    public function setFocus(
        string $pageName,
        string $pageFeature,
        string $pageEntity,
    ): void;


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

    public function mergeRenderOptions(array $renderOptions): void;


    /**
     * Set pagination options for the list.
     *
     * @param array<string, mixed> $options Associative array of pagination options.
     * @return void
     */
    public function setPaginationOptions(array $options): void;

    /**
     * Get pagination options for the list.
     *
     * @return array<string, mixed> Associative array of pagination options.
     */
    public function getPaginationOptions(): array;



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
     * Validate an array of column field names against the known schema for this list type.
     *
     * Sets the entity and page context on the FieldRegistryService before validation.
     * Returns only valid field names, while logging and triggering warnings for any invalid ones.
     *
     * @param array $fields Array of field names to validate.
     * @return array Array of valid field names.
     */
    public function validateFields(array $fields): array;

    /**
     * Build the list using field definitions from FieldRegistryService.
     *
     * Applies fallback logic: page context → entity → base.
     *
     * @param ListBuilderInterface $builder The list builder instance.
     * @return void
     */
    public function buildList(ListBuilderInterface $builder): void;
}
