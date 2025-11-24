<?php

declare(strict_types=1);

namespace Core\View;

/**
 * Interface for View factories.
 *
 * Defines the contract for creating View objects using a ViewType.
 */
interface ViewFactoryInterface
{
    /**
     * Create a View object using the provided ViewType and initial data.
     *
     * @param ViewTypeInterface $viewType The View type defining the structure and configuration.
     * @param array<string, mixed> $data Initial data to populate the view.
     * @param array<string, mixed> $options Options for the view.
     * @return ViewInterface The created View object.
     */
    public function create(ViewTypeInterface $viewType, array $data = [], array $options = []): ViewInterface;
}
