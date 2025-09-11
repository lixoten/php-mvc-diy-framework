<?php

declare(strict_types=1);

namespace Core\List;

/**
 * Interface for list factories
 */
interface ListFactoryInterface
{
    /**
     * Create a list from a list type
     *
     * @param ListTypeInterface $listType The list type to create
     * @param array $data The data for the list
     * @param array $options Options for the list
     * @return ListInterface The created list
     */
    public function create(ListTypeInterface $listType, array $data = [], array $options = []): ListInterface;

    // /**
    //  * Set the default renderer
    //  *
    //  * @param ListRendererInterface $renderer The renderer to use
    //  * @return self
    //  */
    // public function setDefaultRenderer(ListRendererInterface $renderer): self;

    // /**
    //  * Get the default renderer
    //  *
    //  * @return ListRendererInterface
    //  */
    // public function getDefaultRenderer(): ListRendererInterface;
}
