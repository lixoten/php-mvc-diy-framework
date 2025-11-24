<?php

declare(strict_types=1);

namespace Core\Form;

use App\Helpers\DebugRt;
use Core\Form\CSRF\CSRFToken;
use Core\Form\Field\Type\FieldTypeRegistry;
// use Core\Form\Renderer\FormRendererRegistry;
use Core\Form\Validation\Validator;

/**
 * Form factory implementation
 */
class FormFactory implements FormFactoryInterface
{
    private CSRFToken $csrf;
    private FieldTypeRegistry $fieldTypeRegistry;
    // private ?FormRendererRegistry $formRendererRegistry = null;
    private ?Validator $validator;

    /**
     * Constructor
     *
     * @param CSRFToken $csrf
     * @param FieldTypeRegistry $fieldTypeRegistry
     * @param Validator|null $validator
     */
    public function __construct(
        CSRFToken $csrf,
        FieldTypeRegistry $fieldTypeRegistry,
        // ?FormRendererRegistry $formRendererRegistry = null,
        ?Validator $validator = null,
    ) {
        $this->csrf = $csrf;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        // $this->formRendererRegistry = $formRendererRegistry;
        $this->validator = $validator;
    }


    /**
     * {@inheritdoc}
     */
    public function create(
        FormTypeInterface $formType,
        array $data = [],
        array $options = []
    ): FormInterface {
        // Create form instance
        $form = new Form($formType->pageKey, $formType->pageName, $this->csrf);

        // Create form builder
        $builderForm = new FormBuilder($form, $this->fieldTypeRegistry);

        // Built it
        $formType->buildForm($builderForm);

        // Set validator if available
        if ($this->validator) {
            $form->setValidator($this->validator);
        }

        // // Set form renderer if available
        // if ($this->formRendererRegistry) {
        //     $renderOptions = $formType->getRenderOptions();
        //     $rendererName = $renderOptions['renderer'] ?? 'bootstrap';
        //     $renderer = $this->formRendererRegistry->getRenderer($rendererName);
        //     $form->setRenderer($renderer);
        // }

        // Set initial data if provided
        if (!empty($data)) {
            $form->setData($data);
        }

        return $form;
    }
}
