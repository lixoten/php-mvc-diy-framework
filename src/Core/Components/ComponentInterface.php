<?php

declare(strict_types=1);

namespace Core\Components;

/**
 * Interface for all components.
 * Defines the contract for rendering components.
 */
interface ComponentInterface
{
    /**
     * Renders the component as a string.
     *
     * @param array<string, mixed> $options Additional rendering options.
     * @return string The rendered HTML string.
     */
    public function render(array $options = []): string;
}
