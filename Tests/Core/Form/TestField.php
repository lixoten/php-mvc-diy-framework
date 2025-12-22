<?php

declare(strict_types=1);

namespace Tests\Core\Form;

use Core\Form\Field\FieldInterface;

class TestField implements FieldInterface
{
    public function __construct(
        private string $name,
        private string $type,
        private array $options = []
    ) {}

    public function getName(): string { return $this->name; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function getLabel(): string { return $this->options['label'] ?? ''; }
    public function getFormatters(): null|callable|string|array { return $this->options['formatters'] ?? null; }
    public function setFormatters(null|callable|string|array $formatters): self { $this->options['formatters'] = $formatters; return $this; }
    public function getValidators(): null|callable|string|array { return $this->options['validators'] ?? null; }
    public function setValidators(null|callable|string|array $validators): self { $this->options['validators'] = $validators; return $this; }
    public function getAttributes(): array { return $this->options['attributes'] ?? []; }
    public function getAttribute(string $name, $default = null) { return $this->options['attributes'][$name] ?? $default; }
    public function getValue() { return $this->options['value'] ?? null; }
    public function setValue($value): self { $this->options['value'] = $value; return $this; }
    public function getErrors(): array { return $this->options['errors'] ?? []; }
    public function getOptions(): array { return $this->options; }
    public function isRequired(): bool { return $this->options['required'] ?? false; }
    public function showLabel(): bool { return $this->options['show_label'] ?? true; }
}