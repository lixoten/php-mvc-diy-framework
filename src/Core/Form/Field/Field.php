<?php

declare(strict_types=1);

namespace Core\Form\Field;

/**
 * Base field implementation
 */
class Field implements FieldInterface
{
    protected string $name;
    protected string $type = 'text';
    protected $value = null;
    protected string $label;
    protected array $errors = [];
    protected array $options = [];
    protected array $attributes = [];

    /**
     * Constructor
     *
     * @param string $name Field name
     * @param array $options Field options
     */
    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;

        // Extract common options
        $this->type = $options['type'] ?? 'text';
        $this->label = $options['label'] ?? ucfirst($name);
        $this->attributes = $options['attributes'] ?? [];

        // Apply any other options as properties if setter exists
        foreach ($options as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * {@inheritdoc}
     */
    public function addError(string $message): self
    {
        $this->errors[] = $message;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasError(): bool
    {
        return !empty($this->errors);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributesString(): string
    {
        $attributeString = '';

        foreach ($this->attributes as $name => $value) {
            $attributeString .= ' ' . $name . '="' . htmlspecialchars((string)$value) . '"';
        }

        return $attributeString;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired(): bool
    {
        return $this->options['required'] ?? false;
    }


    /**
     * Set field type
     *
     * @param string $type
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set field required state
     *
     * @param bool $required
     * @return self
     */
    public function setRequired(bool $required): self
    {
        $this->options['required'] = $required;
        return $this;
    }

    /**
     * Set field attributes
     *
     * @param array $attributes
     * @return self
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Set field options
     *
     * @param array $options
     * @return self
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Render the field as HTML
     *
     * @return string
     */
    public function render(): string
    {
        $attributes = $this->getAttributesString();
        $label = htmlspecialchars($this->getLabel());
        $value = htmlspecialchars((string)$this->getValue());

        // Render the field based on its type
        switch ($this->getType()) {
            case 'textarea':
                return sprintf(
                    '<label for="%s">%s</label><textarea name="%s" id="%s"%s>%s</textarea>',
                    $this->getName(),
                    $label,
                    $this->getName(),
                    $this->getName(),
                    $attributes,
                    $value
                );

            case 'checkbox':
                return sprintf(
                    '<label><input type="checkbox" name="%s" id="%s"%s %s> %s</label>',
                    $this->getName(),
                    $this->getName(),
                    $attributes,
                    $value ? 'checked' : '',
                    $label
                );

            default:
                return sprintf(
                    '<label for="%s">%s</label><input type="%s" name="%s" id="%s" value="%s"%s>',
                    $this->getName(),
                    $label,
                    $this->getType(),
                    $this->getName(),
                    $this->getName(),
                    $value,
                    $attributes
                );
        }
    }
}
