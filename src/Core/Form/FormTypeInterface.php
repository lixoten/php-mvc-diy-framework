<?php

declare(strict_types=1);

namespace Core\Form;

/**
 * Interface for form types
 */
interface FormTypeInterface
{
    /**
     * Get all fields with overrides already applied
     *
     * @return array
     */
    public function getFields(): array;
}
