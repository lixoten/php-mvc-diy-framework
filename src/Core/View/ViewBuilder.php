<?php

declare(strict_types=1);

namespace Core\View;

use Core\Form\Field\FieldInterface;
use Core\Form\Field\Type\FieldTypeRegistry;

/**
 * Default view builder implementation.
 *
 * This class is responsible for constructing and configuring a View object,
 * adding fields, setting properties, and applying rendering options.
 */
class ViewBuilder implements ViewBuilderInterface
{
    private ViewInterface $view;
    private FieldTypeRegistry $fieldTypeRegistry;

    /**
     * Constructor.
     *
     * @param ViewInterface $view The View object to build.
     * @param FieldTypeRegistry $fieldTypeRegistry The registry for creating field types.
     */
    public function __construct(
        ViewInterface $view,
        FieldTypeRegistry $fieldTypeRegistry
    ) {
        $this->view = $view;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title): self
    {
        $this->view->setTitle($title);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $name, array $options = []): self
    {
        // Determine field type, defaulting to 'text' if not specified.
        $type = $options['type'] ?? 'text';

        // Create field using the registry.
        $field = $this->fieldTypeRegistry->createField($name, $type, $options);

        // Add field to the view.
        $this->view->addField($field);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(FieldInterface $field): self
    {
        $this->view->addField($field);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRenderOptions(array $renderOptions): self
    {
        $this->view->setRenderOptions($renderOptions);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLayout(array $layout): self
    {
        $this->view->setLayout($layout);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getView(): ViewInterface
    {
        return $this->view;
    }
}
