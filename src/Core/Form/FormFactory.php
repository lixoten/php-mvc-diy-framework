<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Form\CSRF\CSRFToken;
use Core\Form\Field\Type\FieldTypeRegistry;
use Core\Form\Validation\Validator;
use Core\Form\FormTypeInterface;
use Core\Form\FormInterface;
use App\Helpers\DebugRt as Debug;
use Core\Form\Renderer\RendererRegistry;

/**
 * Form factory implementation
 */
class FormFactory implements FormFactoryInterface
{
    private CSRFToken $csrf;
    private FieldTypeRegistry $fieldTypeRegistry;
    private ?Validator $validator;
    private ?RendererRegistry $rendererRegistry = null;

    /**
     * Constructor
     *
     * @param CSRFToken $csrf
     * @param FieldTypeRegistry $fieldTypeRegistry
     * @param Validator|null $validator
     * @param RendererRegistry|null $rendererRegistry
     */
    public function __construct(
        CSRFToken $csrf,
        FieldTypeRegistry $fieldTypeRegistry,
        ?Validator $validator = null,
        ?RendererRegistry $rendererRegistry = null
    ) {
        $this->csrf = $csrf;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->validator = $validator;
        $this->rendererRegistry = $rendererRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function create(FormTypeInterface $formType, array $data = [], array $options = []): FormInterface
    {
        // Create form instance
        $form = new Form($formType->getName(), $this->csrf);

        // Set validator if available
        if ($this->validator) {
            $form->setValidator($this->validator);
        }

        // Create form builder
        $builder = new FormBuilder($form, $this->fieldTypeRegistry);

        // Build form using type
        $formType->buildForm($builder, $options);

        // Set form renderer if available
        if ($this->rendererRegistry) {
            // Get renderer name from options, fall back to css_framework from config
            $rendererName = $options['renderer'] ?? 'bootstrap';

            // Get the renderer
            $renderer = $this->rendererRegistry->getRenderer($rendererName);
            $form->setRenderer($renderer);
        }

        // Set initial data if provided
        if (!empty($data)) {
            $form->setData($data);
        }

        // Add before returning the form
        $form->setRenderOptions($options);

        return $form;
    }
}
