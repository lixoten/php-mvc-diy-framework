<?php

declare(strict_types=1);

namespace Core\View;

use Core\Form\Field\Type\FieldTypeRegistry;

/**
 * View factory implementation.
 *
 * This factory is responsible for creating a fully configured View object
 * by orchestrating the ViewType and ViewBuilder, without involving rendering logic.
 */
class ViewFactory implements ViewFactoryInterface
{
    private FieldTypeRegistry $fieldTypeRegistry;

    /**
     * Constructor.
     *
     * @param FieldTypeRegistry $fieldTypeRegistry The registry for creating field types.
     */
    public function __construct(
        FieldTypeRegistry $fieldTypeRegistry
    ) {
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        ViewTypeInterface $viewType,
        array $data = [],
        array $options = []
    ): ViewInterface {
        // Create view instance
        $view = new View($viewType->pageKey, $viewType->pageName);

        // Create view builder
        $builderView = new ViewBuilder($view, $this->fieldTypeRegistry);

        // Build it using the ViewType
        $viewType->buildView($builderView);

        // Set initial data if provided
        if (!empty($data)) {
            $view->setData($data);
        }

        // Apply any top-level options directly to the view if needed (e.g., overriding title)
        if (isset($options['title'])) {
            $view->setTitle($options['title']);
        }
        if (isset($options['render_options'])) {
            $view->setRenderOptions(array_merge($view->getRenderOptions(), $options['render_options']));
        }
        if (isset($options['layout'])) {
            $view->setLayout(array_merge($view->getLayout(), $options['layout']));
        }

        return $view;
    }
}
