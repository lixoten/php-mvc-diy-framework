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
        // $finalRenderOptions     = array_merge($formType->getRenderOptions(), $options['render_options'] ?? []);
        // $fields =  $options['form_hidden_fields'] ?? [];
        // if (!isset($fields) || !is_array($fields) || empty($fields)) {
        //     $finalFormFields    = $formType->getFormFields();
        // } else {
        //     $finalFormFields    = $options['form_hidden_fields'];
        // }

        // $layout =  $options['layout'] ?? [];
        // if (!isset($layout) || !is_array($layout) || empty($layout)) {
        //     $finalLayout    = $formType->getFormLayout();
        // } else {
        //     $finalLayout    =  $options['layout'];
        // }


        // $formType->revisitShit();



        // DebugRt::j('0', 'finalRenderOptions', $finalRenderOptions);
        // DebugRt::j('0', 'fields', $fields);
        // DebugRt::j('1', 'validatedLayout', $formType);


        /*
            1. Controller via DI creates Form Type
                1. We create `TestyFormType` on load
                    - it reads config files 'view.form' to get `Default_render_options`
                    - it reads config files `view_options/testy_edit` a page specific form config for:
                        - `render_options`
                        - `layout`
                        - `hidden_fields`
                    - It merges `Default_render_options` with `render_options`
                    - It creates a new `field` array from merging `layout` fields and `hidden_field`
                    - Cleans:
                        - `layout` - it removes invalid fields from `layout`
                        - `field` - it removes invalid fields from `field` array
                        - unset `hidden_fields` array - we no longer need it
                    - final result `options`
                        - `render_options` array
                        - `layout` array
                        - `field` array
            2. EditAction
                1. it calls a helper `Testy_Controller->overrideFormTypeRenderOptions(): void`
                    1 overrideFormTypeRenderOptions() takes the options and calls TestyFormType->overrideConfig($options):void
                    2, TestyFormType->overrideConfigPasses it Merges, Validates, filters out duplicate and invalid fields                    it merges
                2. Get Data using those form `fields` (`$this->formType->getFields();`)
                3. Call `formFactory->create(` to build the form

            - What i want when we call http://mvclixo.tv/testy/edit/14
                TestyController DI create the initial TestyFormType
                - This here reads configs and merges defaults to testy specific configs for that formType to produce options
                - TestyController can override some of these formType Options
                    - So it to d the work itself and update the FormType
                    - or pass these new options to FormType so it can do the work and override
                - TestyController needs $options['form_fields'] because it uses that to get columns needed Data
                - TestyController calls FormFactory->create that takes FormType and Data


                Somewhere along the line we need to clean up formType options... We filter out duplicate fields, remove invalid fields







        */

        // $formType->setRenderOptions($finalRenderOptions);
        // $formType->setFormFields($finalFormFields);

        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        //$formType->revisitShit();

        // Create form instance
        $form = new Form($formType->pageConfigKey, $this->csrf);
        //$form = new Form($formType->viewName, $this->csrf);

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
            $renderOptions = $formType->getRenderOptions();
            $rendererName = $renderOptions['renderer'] ?? 'bootstrap';
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
