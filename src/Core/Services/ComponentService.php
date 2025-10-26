<?php
declare(strict_types=1);

namespace Core\Services;

use Core\Components\FormComponent;
use Core\Components\ListComponent;
use Core\Form\FormInterface;

/**
 * Service for creating and managing components.
 * Handles component instantiation with config-driven options.
 */
class ComponentService
{
    /**
     * Constructor.
     *
     * @param FieldRegistryService $fieldRegistryService The field registry service for field resolution.
     * @param ConfigService $configService The config service for loading options.
     */
    public function __construct(
        private readonly FieldRegistryService $fieldRegistryService,
        private readonly ConfigService $configService,
    ) {}

    /**
     * Creates a form component.
     *
     * @param FormInterface $form The form to wrap.
     * @param array<string, mixed> $options Additional options for the component.
     * @return FormComponent The created form component.
     */
    public function createFormComponent(FormInterface $form, array $options = []): FormComponent
    {
        return new FormComponent($form, $options);
    }

    /**
     * Creates a list component.
     *
     * @param array<array<string, mixed>> $listData The list data to render.
     * @param array<string, mixed> $options Additional options for the component.
     * @return ListComponent The created list component.
     */
    public function createListComponent(array $listData, array $options = []): ListComponent
    {
        return new ListComponent($listData, $options);
    }

    /**
     * Renders a component.
     *
     * @param FormComponent|ListComponent $component The component to render.
     * @param array<string, mixed> $options Additional rendering options.
     * @return string The rendered HTML string.
     */
    public function renderComponent($component, array $options = []): string
    {
        return $component->render($options);
    }
}