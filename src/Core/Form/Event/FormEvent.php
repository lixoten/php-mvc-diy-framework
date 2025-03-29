<?php

declare(strict_types=1);

namespace Core\Form\Event;

use Core\Form\FormInterface;

/**
 * Form event implementation
 */
class FormEvent implements FormEventInterface
{
    private FormInterface $form;
    private $data;
    private string $name;
    private bool $propagationStopped = false;

    /**
     * Constructor
     *
     * @param string $name Event name
     * @param FormInterface $form
     * @param mixed $data
     */
    public function __construct(string $name, FormInterface $form, $data = null)
    {
        $this->name = $name;
        $this->form = $form;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
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
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * {@inheritdoc}
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}
