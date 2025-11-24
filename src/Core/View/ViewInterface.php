<?php

declare(strict_types=1);

namespace Core\View;

use Core\Form\Field\FieldInterface;

/**
 * Interface for View objects.
 *
 * Defines the contract for a View data container, which holds the
 * structure, data, and rendering options for displaying a single entity.
 * It does not contain any rendering logic itself.
 */
interface ViewInterface
{
    /**
     * Get the unique key for this view page (e.g., 'testy_view').
     *
     * @return string
     */
    public function getPageKey(): string;

    /**
     * Get the name of the page (e.g., 'testy').
     *
     * @return string
     */
    public function getPageName(): string;

    /**
     * Set the title for the view.
     *
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * Get the title of the view.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Add a field to the view.
     *
     * @param FieldInterface $field
     * @return self
     */
    public function addField(FieldInterface $field): self;

    /**
     * Get a specific field by its name.
     *
     * @param string $fieldName
     * @return FieldInterface
     * @throws \OutOfBoundsException If the field does not exist.
     */
    public function getField(string $fieldName): FieldInterface;

    /**
     * Get all fields configured for this view.
     *
     * @return array<string, FieldInterface> An associative array of fields by name.
     */
    public function getFields(): array;

    /**
     * Set the data for the view (e.g., a single record's values).
     *
     * @param array<string, mixed> $data An associative array of field values.
     * @return self
     */
    public function setData(array $data): self;

    /**
     * Get the data associated with the view.
     *
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Set the rendering options for the view.
     *
     * @param array<string, mixed> $options
     * @return self
     */
    public function setRenderOptions(array $options): self;

    /**
     * Get the rendering options for the view.
     *
     * @return array<string, mixed>
     */
    public function getRenderOptions(): array;

    /**
     * Get a specific rendering option by key, with an optional default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getRenderOption(string $key, mixed $default = null): mixed;

    /**
     * Set the layout configuration for the view.
     *
     * @param array<string, mixed> $layout
     * @return self
     */
    public function setLayout(array $layout): self;

    /**
     * Get the layout configuration for the view.
     *
     * @return array<string, mixed>
     */
    public function getLayout(): array;
}
