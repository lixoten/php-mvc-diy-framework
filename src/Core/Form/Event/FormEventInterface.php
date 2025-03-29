<?php

declare(strict_types=1);

namespace Core\Form\Event;

use Core\Form\FormInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * Interface for form events
 */
interface FormEventInterface extends StoppableEventInterface
{
    /**
     * Get the form
     *
     * @return FormInterface
     */
    public function getForm(): FormInterface;

    /**
     * Get event data
     *
     * @return mixed
     */
    public function getData();

    /**
     * Set event data
     *
     * @param mixed $data
     * @return self
     */
    public function setData($data): self;

    /**
     * Get the event name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Stop event propagation
     */
    public function stopPropagation(): void;
}
