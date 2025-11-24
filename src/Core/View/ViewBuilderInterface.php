<?php

declare(strict_types=1);

namespace Core\View;

use Core\Form\Field\FieldInterface;

/**
 * Interface for View builders.
 *
 * Defines the contract for constructing a View object by adding fields,
 * setting properties, and applying rendering options.
 */
interface ViewBuilderInterface
{
    /**
     * Set the title for the view.
     *
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * Add a field to the view.
     *
     * @param string $name Field name
     * @param array<string, mixed> $options Field options
     * @return self
     */
    public function add(string $name, array $options = []): self;

    /**
     * Add an existing field to the view.
     *
     * @param FieldInterface $field
     * @return self
     */
    public function addField(FieldInterface $field): self;

    /**
     * Set rendering options for the view.
     *
     * @param array<string, mixed> $renderOptions
     * @return self
     */
    public function setRenderOptions(array $renderOptions): self;

    /**
     * Set the layout configuration for the view.
     *
     * @param array<string, mixed> $layout Layout configuration
     * @return self
     */
    public function setLayout(array $layout): self;

    /**
     * Get the built View object.
     *
     * @return ViewInterface
     */
    public function getView(): ViewInterface;
}
