<?php

declare(strict_types=1);

namespace Core\Form;

use App\Helpers\DebugRt;
use Core\Form\CSRF\CSRFToken;
use Core\Form\Field\Type\FieldTypeRegistry;
use Core\Form\Renderer\FormRendererRegistry;
use Core\Form\Validation\Validator;


/**
 * Form factory implementation
 */
class FormFactory implements FormFactoryInterface
{
    private CSRFToken $csrf;
    private FieldTypeRegistry $fieldTypeRegistry;
    private ?FormRendererRegistry $formRendererRegistry = null;
    private ?Validator $validator;

    /**
     * Constructor
     *
     * @param CSRFToken $csrf
     * @param FieldTypeRegistry $fieldTypeRegistry
     * @param FormRendererRegistry|null $formRendererRegistry
     * @param Validator|null $validator
     */
    public function __construct(
        CSRFToken $csrf,
        FieldTypeRegistry $fieldTypeRegistry,
        ?FormRendererRegistry $formRendererRegistry = null,
        ?Validator $validator = null,
    ) {
        $this->csrf = $csrf;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->formRendererRegistry = $formRendererRegistry;
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
        // Merge Options - List options and options set in controller
        // We should have everything we need,
        // but in case the Controller wants to override anything we check here
        $finalRenderOptions     = array_merge($formType->getRenderOptions(), $options['render_options'] ?? []);
        $fields =  $options['list_fields'] ?? [];
        if (!isset($fields) || !is_array($fields) || empty($fields)) {
            $finalFormFields    = $formType->getFormFields();
        } else {
            $finalFormFields    =  $options['list_fields'];
        }
        $formType->setRenderOptions($finalRenderOptions);
        $formType->setFormFields($finalFormFields);

        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////

        // Create form instance
        $form = new Form($formType->viewName, $this->csrf);

        // Create form builder
        $builderForm = new FormBuilder($form, $this->fieldTypeRegistry);

        // Built it
        $formType->buildForm($builderForm);

        // Set validator if available
        if ($this->validator) {
            $form->setValidator($this->validator);
        }

        // Set form renderer if available
        if ($this->formRendererRegistry) {
            $rendererName = $finalOptions['renderer'] ?? 'bootstrap';
            $renderer = $this->formRendererRegistry->getRenderer($rendererName);
            $form->setRenderer($renderer);
        }

        // Set initial data if provided
        if (!empty($data)) {
            $form->setData($data);
        }

        return $form;
    }
}
