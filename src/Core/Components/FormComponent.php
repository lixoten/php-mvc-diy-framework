<?php
declare(strict_types=1);

namespace Core\Components;

use Core\Form\FormInterface;

/**
 * Component wrapper for forms.
 * Wraps a FormInterface and delegates rendering.
 */
class FormComponent extends AbstractComponent
{
    /**
     * Constructor.
     *
     * @param FormInterface $form The form to wrap.
     * @param array<string, mixed> $options Additional component options.
     */
    public function __construct(
        private readonly FormInterface $form,
        private array $options = [],
    ) {
        parent::__construct(
            // Assuming ConfigService and FieldRegistryService are injected elsewhere or via DI
            // For now, we'll assume they are available; in practice, adjust based on DI setup
        );
    }

    /**
     * Renders the form component.
     *
     * @param array<string, mixed> $options Additional rendering options.
     * @return string The rendered HTML string.
     */
    public function render(array $options = []): string
    {
        // Merge component options with passed options
        $mergedOptions = array_merge($this->options, $options);

        // Delegate rendering to the form
        return $this->form->render($mergedOptions);
    }
}