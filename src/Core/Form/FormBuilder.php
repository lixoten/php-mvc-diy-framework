<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Form\CSRF\CSRFToken;

/**
 * Default form builder implementation
 */
class FormBuilder implements FormBuilderInterface
{
    private string $name;
    private array $fields = [];
    private CSRFToken $csrf;
    private array $attributes = [
        'method' => 'POST',
        'action' => '',
    ];

    /**
     * Constructor
     *
     * @param CSRFToken $csrf
     * @param string $name Form name
     */
    public function __construct(CSRFToken $csrf, string $name = 'form')
    {
        $this->csrf = $csrf;
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $name, array $options): self
    {
        $this->fields[$name] = $options;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm(): FormInterface
    {
        $form = new Form($this->name, $this->csrf, $this->fields);

        foreach ($this->attributes as $name => $value) {
            $form->setAttribute($name, $value);
        }

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function setAction(string $action): self
    {
        $this->attributes['action'] = $action;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod(string $method): self
    {
        $this->attributes['method'] = $method;
        return $this;
    }
}
