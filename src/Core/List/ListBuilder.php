<?php

declare(strict_types=1);

namespace Core\List;

use App\Enums\Url;
use Core\Form\Field\Type\FieldTypeRegistry;
use Core\I18n\I18nTranslator;

/**
 * List builder
 */
class ListBuilder implements ListBuilderInterface
{
    private ListInterface $list;
    private FieldTypeRegistry $fieldTypeRegistry;

    /**
     * Constructor
     *
     * @param ListInterface $list
     * @param FieldTypeRegistry $fieldTypeRegistry
     */
    public function __construct(
        ListInterface $list,
        FieldTypeRegistry $fieldTypeRegistry,
        private I18nTranslator $translator // php 8.2 promotion
    ) {
        $this->list = $list;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->translator = $translator;
    }

    public function setOptions(array $options): void
    {
        $this->list->setOptions($options);
    }

    public function setRenderOptions(array $renderOptions): void
    {
        if ($this->translator) {
            $translatableKeys = [
                'title',
                'heading',
                'add_button_label',
                'actions_label'
            ];

            foreach ($translatableKeys as $key) {
                if (isset($renderOptions[$key])) {
                    $renderOptions[$key] = $this->translator->get(
                        $renderOptions[$key],
                        pageName: $this->list->getPageName()
                    );
                }
            }
        }

        $this->list->setRenderOptions($renderOptions);
    }

    /**
     * Add a column to the list
     */
    public function addColumn(string $name, array $options = []): self
    {
        $this->list->addColumn($name, $options);
        return $this;
    }

    /**
     * Add an action to the list
     */
    public function addAction(string $name, array $options = []): self
    {
        $this->list->addAction($name, $options);
        return $this;
    }

    /**
     * Set the title of the list
     */
    public function setListTitle(string $title): self
    {
        $this->list->setTitle($title);
        return $this;
    }

    /**
     * Set the data for the list
     */
    public function setListData(array $data): self
    {
        $this->list->setData($data);
        return $this;
    }

    /**
     * Configure pagination
     */
    public function setPagination(array $pagination): self
    {
        $this->list->setPagination($pagination);
        return $this;
    }

    /**
     * Get the built list
     */
    public function getList(): ListInterface
    {
        return $this->list; // Return modified list
    }
}
