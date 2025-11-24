<?php

declare(strict_types=1);

namespace Core\View;

use Core\Form\Field\FieldInterface;

/**
 * Default view implementation.
 *
 * This class acts as a data container for displaying a single entity.
 * It holds the view's structure (fields, layout) and the data to be displayed,
 * along with rendering options. It contains no rendering logic itself.
 */
class View implements ViewInterface
{
    private string $pageKey;
    private string $pageName;
    private string $title = '';
    /** @var array<string, FieldInterface> */
    private array $fields = [];
    /** @var array<string, mixed> */
    private array $data = [];
    /** @var array<string, mixed> */
    private array $renderOptions = [];
    /** @var array<string, mixed> */
    private array $layout = [];

    /**
     * Constructor.
     *
     * @param string $pageKey Unique key for this view page (e.g., 'testy_view').
     * @param string $pageName Name of the page (e.g., 'testy').
     */
    public function __construct(
        string $pageKey,
        string $pageName
    ) {
        $this->pageKey = $pageKey;
        $this->pageName = $pageName;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageKey(): string
    {
        return $this->pageKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageName(): string
    {
        return $this->pageName;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(FieldInterface $field): self
    {
        $this->fields[$field->getName()] = $field;

        // If data is already set, apply it to the new field
        if (isset($this->data[$field->getName()])) {
            $field->setValue($this->data[$field->getName()]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getField(string $fieldName): FieldInterface
    {
        if (!isset($this->fields[$fieldName])) {
            throw new \OutOfBoundsException("Field '{$fieldName}' not found in view '{$this->pageKey}'.");
        }
        return $this->fields[$fieldName];
    }

    /**
     * {@inheritdoc}
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        // Apply data to existing fields
        foreach ($this->fields as $name => $field) {
            if (array_key_exists($name, $data)) {
                $field->setValue($data[$name]);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        // For View, getData usually means the initial record data,
        // but if fields have been manipulated, we might want their current values.
        // For simplicity, we return the base data.
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setRenderOptions(array $options): self
    {
        $this->renderOptions = $options;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderOptions(): array
    {
        return $this->renderOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderOption(string $key, mixed $default = null): mixed
    {
        return $this->renderOptions[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setLayout(array $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLayout(): array
    {
        return $this->layout;
    }
}
