<?php

declare(strict_types=1);

namespace Core\Form\Field;

/**
 * Base field implementation
 */
class Field implements FieldInterface
{
    protected string $name;
    protected string $label;
    protected string $type;
    protected mixed $value;

    // /**
    //  * @var callable|null Formatter closure for this field (backward compatibility)
    //  */
    protected $formatters = null;

    // /**
    //  * @var callable|null Formatter closure for this field (backward compatibility)
    //  */
    protected $validators = null;

    /** @var array<string, mixed> */
    protected array $options = [];
    /** @var array<string, mixed> */
    protected array $attributes = [];
    /** @var array<int, string> */
    protected array $errors = [];


    /**
     * Constructor
     *
     * @param string $name
     * @param array<string, mixed> $options
     * @param array<string, mixed> $attributes
     */
    public function __construct(string $name, array $options = [], array $attributes = [])
    {
        $this->name = $name;
        $this->options = $options;
        $this->attributes = $attributes;

        // // Extract common options
        $this->label       = $options['label'] ?? ucfirst($name);
        $this->type        = $options['type'] ?? 'text';
        $this->value       = $options['value'] ?? null;
        $this->formatters  = $options['formatters'] ?? null;
        $this->validators  = $options['validators'] ?? [];

        unset($this->options['label']); // no longer need so we unset
        unset($this->options['type']); // no longer need so we unset
        unset($this->options['formatters']); // no longer need so we unset
        unset($this->options['validators']); // no longer need so we unset
        unset($this->options['value']); // no longer need so we unset


        // // Apply any other options as properties if setter exists
        // foreach ($options as $key => $value) {
        //     $setter = 'set' . ucfirst($key);
        //     if (method_exists($this, $setter)) {
        //         $this->$setter($value);
        //     }
        // }
    }

    //-------------------------------------------------------------------------
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

    //-------------------------------------------------------------------------
    /**
     * {@inheritdoc}
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
    // /**
    //  * Set field type
    //  *
    //  * @param string $type
    //  * @return self
    //  */
    // public function setType(string $type): self
    // {
    //     $this->attributes['type'] = $type;
    //     return $this;
    // }

    //-------------------------------------------------------------------------
    public function getLabel(): string
    {
        return $this->label ?? ucfirst($this->name);
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    //-------------------------------------------------------------------------
    /**
     * {@inheritdoc}
     */
    public function getValue(): mixed
    {
        return $this->value ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    //-------------------------------------------------------------------------
    /**
     * {@inheritdoc}
     */
    public function getFormatters(): null|callable|string|array
    {
        return $this->formatters ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormatters(null|callable|string|array $formatters): self
    {
        $this->formatters = $formatters;
        return $this;
    }

    //-------------------------------------------------------------------------
    /**
     * {@inheritdoc}
     */
    public function getValidators(): null|callable|string|array
    {
        return $this->validators ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function setValidators(null|callable|string|array $validators): self
    {
        $this->validators = $validators;
        return $this;
    }





    //-------------------------------------------------------------------------
    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
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


    //-------------------------------------------------------------------------
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
            if (is_bool($value)) {
                if ($value) {
                    $attributeString .= ' ' . $name;
                }
            } else if ($value !== null) {
                $attributeString .= ' ' . $name . '="' . htmlspecialchars((string)$value) . '"';
            }
        }

        return $attributeString;
    }

    /** // fixme plural.. do we need?
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


    //-------------------------------------------------------------------------
    /**
     * {@inheritdoc}
     */
    public function isRequired(): bool
    {
        return $this->getAttribute('required', false);
    }

    /** //fixme we need?
     * Set field required state
     *
     * @param bool $required
     * @return self
     */
    public function setRequired(bool $required): self
    {
        $this->attributes['required'] = $required;
        return $this;
    }


    //-------------------------------------------------------------------------
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


    //-------------------------------------------------------------------------
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
                $checked = $this->getAttribute('checked', false) ? ' checked' : '';
                return sprintf(
                    '<label><input type="checkbox" name="%s" id="%s"%s%s> %s</label>',
                    $this->getName(),
                    $this->getName(),
                    $attributes,
                    $checked,
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
