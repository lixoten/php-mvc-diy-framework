<?php

declare(strict_types=1);

namespace Core\List;

use App\Helpers\DebugRt;
use Core\Form\CSRF\CSRFToken;
use Core\Form\Field\Type\FieldTypeRegistry;
use Core\List\Renderer\ListRendererRegistry;

//use Core\Form\Validation\Validator;

/**
 * Factory for creating lists
 */
class ListFactory implements ListFactoryInterface
{
    private CSRFToken $csrfToken;
    private FieldTypeRegistry $fieldTypeRegistry;
    private ?ListRendererRegistry $listRendererRegistry = null;
    // private ?Validator $validator;

   /**
     * Constructor
     *
     * @param CSRFToken $csrf
     * @param FieldTypeRegistry $fieldTypeRegistry
     * @param ListRendererRegistry|null $listRendererRegistry
     *
     */
    public function __construct(
        CSRFToken $csrfToken,
        FieldTypeRegistry $fieldTypeRegistry,
        ?ListRendererRegistry $listRendererRegistry = null,
        //?Validator $validator = null,
    ) {
        $this->csrfToken = $csrfToken;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->listRendererRegistry = $listRendererRegistry;
        // $this->validator = $validator;
    }


    /**
     * {@inheritdoc}
     */
    public function create(
        ListTypeInterface $listType,
        array $data = [],
        array $options = [],
    ): ListInterface {
        // // Merge Options - List options and options set in controller
        // $finalOptions           = array_merge($listType->getOptions(), $options['options']);
        // $finalPagination        = array_merge($listType->getPaginationOptions(), $options['pagination']);
        // $finalRenderOptions     = array_merge($listType->getRenderOptions(), $options['render_options']);
        // $fields =  $options['list_fields'];
        // if (!isset($fields) || !is_array($fields) || empty($fields)) {
        //     $finalListFields    = $listType->getFields();
        // } else {
        //     $finalListFields    =  $options['list_fields'];
        // }
        // $listType->setOptions($finalOptions);
        // $listType->setPaginationOptions($finalPagination);
        // $listType->setRenderOptions($finalRenderOptions);
        // $listType->setFields($finalListFields);
        $listType->setPaginationOptions($options['pagination']);

        $listType->setUrlDependentRenderOptions();
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////


        // Create list instance
        $list = new ListView($listType->pageName);

        // Create list builder
        $builder = new ListBuilder($list, $this->fieldTypeRegistry);

        // Built it
        $listType->buildList($builder);


        // Set form renderer if available
        if ($this->listRendererRegistry) {
            $rendererName = $finalOptions['renderer'] ?? 'bootstrap';
            $renderer = $this->listRendererRegistry->getRenderer($rendererName);
            $list->setRenderer($renderer);
        }


        // Set data and CSRF
        $builder->setListData($data);
        // Automatically add CSRF token if it has delete actions and CSRF protection is not explicitly disabled
        // fixme never happens
        if (!isset($options['csrf_protection']) || $options['csrf_protection'] !== false) {
            // Check if the list has any delete actions that would need CSRF protection
            $actions = $list->getActions();
            if (isset($actions['delete'])) {
                $list->setCsrfToken($this->csrfToken->getToken());
            }
        }

        return $list;
    }
}
