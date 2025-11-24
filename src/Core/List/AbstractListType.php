<?php

declare(strict_types=1);

namespace Core\List;

use App\Enums\Url;
use Core\Interfaces\ConfigInterface;
use Core\Services\ListConfigurationService;
use Core\Services\FieldRegistryService;
use Psr\Log\LoggerInterface;

/**
 * Abstract base class for list types.
 *
 * Handles list configuration, rendering options, and column definitions.
 * Uses FieldRegistryService for field/column definitions with fallback logic:
 *   1. Page/view context (set via setFocus)
 *   2. Entity/table context
 *   3. Base/global config
 */
abstract class AbstractListType implements ListTypeInterface
{
    // ✅ Simple, flat property structure
    protected array $options = [];
    protected array $paginationOptions = [];
    protected array $renderOptions = [];
    protected array $fields = [];

    public readonly string $pageKey;
    public readonly string $pageName;
    public readonly string $pageAction;
    public readonly string $pageFeature;
    public readonly string $pageEntity;

    /**
     * Constructor.
     *
     * @param FieldRegistryService $fieldRegistryService Service for field/column definitions
     * @param ConfigInterface $configService Configuration service
     * @param ListConfigurationService $listConfigService List configuration service
     * @param LoggerInterface $logger Logger instance
     */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
        protected ListConfigurationService $listConfigService,
        protected LoggerInterface $logger,
    ) {
        // No manual property assignments - handled by 'protected' promotion
    }

    /** {@inheritdoc} */
    public function setFocus(
        string $pageKey,
        string $pageName,
        string $pageAction,
        string $pageFeature,
        string $pageEntity,
    ): void {
        $this->pageKey     = $pageKey;
        $this->pageName    = $pageName;
        $this->pageAction  = $pageAction;
        $this->pageFeature = $pageFeature;
        $this->pageEntity  = $pageEntity;

        // ✅ Delegate configuration loading to service
        $config = $this->listConfigService->loadConfiguration(
            $pageKey,
            $pageFeature,
            $pageEntity,
        );

        // ✅ Apply loaded configuration to flat properties
        // Use ?? [] to ensure it's an array if not defined in config
        $this->options = $config['options'] ?? [];
        $this->paginationOptions = $config['pagination'] ?? [];
        $this->renderOptions = $config['render_options'] ?? [];
        $this->setFields($config['list_fields'] ?? []);
    }

    /** {@inheritdoc} */
    public function getOptions(): array
    {
        return $this->options;
    }

    /** {@inheritdoc} */
    public function getRenderOptions(): array
    {
        return $this->renderOptions;
    }

    /** {@inheritdoc} */
    public function setRenderOptions(array $renderOptions): void
    {
        $this->renderOptions = $renderOptions;
    }

    /** {@inheritdoc} */
    public function mergeRenderOptions(array $renderOptions): void
    {
        $this->renderOptions = array_merge($this->renderOptions, $renderOptions);
    }

    /** {@inheritdoc} */
    public function getFields(): array
    {
        return $this->fields;
    }

    /** {@inheritdoc} */
    public function setFields(array $fields): void
    {
        $validFields = $this->fieldRegistryService->filterAndValidateFields(
            $fields,
            $this->pageKey,
            $this->pageEntity
        );
        $this->fields = $validFields;
    }

    /** {@inheritdoc} */
    public function getPaginationOptions(): array
    {
        return $this->paginationOptions;
    }

    /** {@inheritdoc} */
    public function setPaginationOptions(array $paginationOptions): void
    {
        $this->paginationOptions = $paginationOptions;
    }


    /** {@inheritdoc} */
    public function buildList(ListBuilderInterface $builder): void
    {
        $builder->setListTitle($this->renderOptions['title'] ?? 'List');
        $builder->setOptions($this->options);
        $builder->setRenderOptions($this->renderOptions);

        // Add columns from field definitions
        foreach ($this->fields as $fieldName) {
            $columnDef = $this->fieldRegistryService->getFieldWithFallbacks(
                $fieldName,
                $this->pageKey,
                $this->pageEntity
            );

            if ($columnDef && isset($columnDef['list'])) {
                $listOptions = $columnDef['list'];
                $listOptions['formatters'] = $columnDef['formatters'] ?? [];
                $builder->addColumn($fieldName, $listOptions);
            } else {
                $this->logger->warning('AbstractListType: Field definition not found', [
                    'fieldName' => $fieldName,
                    'pageKey' => $this->pageKey,
                    'pageEntity' => $this->pageEntity,
                ]);
            }
        }

        $this->addActions($builder);
    }

    /** {@inheritdoc} */
    public function overrideConfig(array $options): void
    {
        if (isset($options['options']) && is_array($options['options'])) {
            $this->options = array_merge($this->options, $options['options']);
        }
        if (isset($options['pagination']) && is_array($options['pagination'])) {
            $this->paginationOptions = array_merge($this->paginationOptions, $options['pagination']);
        }
        if (isset($options['render_options']) && is_array($options['render_options'])) {
            $this->renderOptions = array_merge($this->renderOptions, $options['render_options']);
        }
        if (isset($options['list_fields']) && is_array($options['list_fields'])) {
            // Overriding fields directly usually means replacing them
            $this->setFields($options['list_fields']);
        }

        // Re-validate fields if any field-related config was overridden
        // Note: setFields already handles validation, so this is primarily for layout/render updates
        // If layout or render options influence field validity, a re-validation might be needed.
        // For simplicity and to match FormType, we can run a minimal validation or rely on setFields().
        // If list_fields were directly set, setFields() would have validated.
        // If a separate validation is needed for options/render_options, it would go here.
        // For now, assume setFields() is sufficient if 'list_fields' were directly provided.
        // If options or render_options changed something that could affect fields
        // that are NOT in list_fields, then a re-run of fieldRegistryService->filterAndValidateFields()
        // on the current $this->fields would be appropriate, but setFields already covers it.
        // Let's ensure this is robust by re-validating if fields were overridden.
        if (isset($options['list_fields']) && is_array($options['list_fields'])) {
            $this->setFields($this->fields); // Re-run setFields to re-validate against registry
        }
    }



    ////////////////////////////////////////////////////////////////////////////////////
    // Private / Protected  Methods ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////
    /**
     * Add action columns (edit, delete, view) based on render options and Url enums
     *
     * @param ListBuilderInterface $builder
     */
    private function addActions(ListBuilderInterface $builder): void
    {
        $renderOptions = $this->getRenderOptions();

        // ✅ Get URL enums and route type from render_options (set by controller)
        $urlEnums = $renderOptions['url_enums'] ?? [];
        $routeType = $renderOptions['route_type'] ?? 'core';

        // ✅ DEBUG: Log what we extracted
        // $this->logger->debug('AbstractListType::addActions() URL enums extracted', [
        //     'url_enums_count' => count($urlEnums),
        //     'url_enums_keys' => array_keys($urlEnums),
        //     'route_type' => $routeType,
        // ]);

        if (empty($urlEnums)) {
            $this->logger->warning('AbstractListType: No URL enums configured, skipping action columns', [
                'all_render_options' => $renderOptions,
            ]);
            return;
        }

        // ✅ Track number of actions added
        $actionsAdded = 0;

        // ✅ Edit action
        $showEdit = $renderOptions['show_action_edit'] ?? false;
        $hasEditEnum = isset($urlEnums['edit']);

        // $this->logger->debug('AbstractListType::addActions() Checking edit action', [
        //     'show_action_edit' => $showEdit,
        //     'has_edit_url_enum' => $hasEditEnum,
        //     'will_add' => $showEdit && $hasEditEnum,
        // ]);

        if ($showEdit && $hasEditEnum) {
            /** @var Url $editUrl */
            $editUrl = $urlEnums['edit'];
            $generatedUrl = $editUrl->url(['id' => '{id}'], $routeType);
            $label        = $editUrl->label();

            // $this->logger->debug('AbstractListType::addActions() Adding edit action', [
            //     'enum_name' => $editUrl->name,
            //     'generated_url' => $generatedUrl,
            // ]);

            $builder->addAction('edit', [
                'url' => $generatedUrl,
                'label' => $label,
            ]);
            $actionsAdded++;
        }

        // ✅ Delete action
        $showDel = $renderOptions['show_action_del'] ?? false;
        $hasDelEnum = isset($urlEnums['delete']);

        // $this->logger->debug('AbstractListType::addActions() Checking delete action', [
        //     'show_action_del' => $showDel,
        //     'has_delete_url_enum' => $hasDelEnum,
        //     'will_add' => $showDel && $hasDelEnum,
        // ]);

        if ($showDel && $hasDelEnum) {
            /** @var Url $deleteUrl */
            $deleteUrl    = $urlEnums['delete'];
            $generatedUrl = $deleteUrl->url(['id' => '{id}'], $routeType);
            $label        = $deleteUrl->label();
            // $this->logger->debug('AbstractListType::addActions() Adding delete action', [
            //     'enum_name' => $deleteUrl->name,
            //     'generated_url' => $generatedUrl,
            // ]);

            $builder->addAction('delete', [
                'url' => $generatedUrl,
                'label' => $label,
                'attributes' => $this->getDeleteActionAttributes(),
            ]);
            $actionsAdded++;
        }

        // ✅ View action
        $showView    = $renderOptions['show_action_view'] ?? false;
        $hasViewEnum = isset($urlEnums['view']);

        // $this->logger->debug('AbstractListType::addActions() Checking view action', [
        //     'show_action_view' => $showView,
        //     'has_view_url_enum' => $hasViewEnum,
        //     'will_add' => $showView && $hasViewEnum,
        // ]);

        if ($showView && $hasViewEnum) {
            /** @var Url $viewUrl */
            $viewUrl      = $urlEnums['view'];
            $generatedUrl = $viewUrl->url(['id' => '{id}'], $routeType);
            $label        = $viewUrl->label();

            // $this->logger->debug('AbstractListType::addActions() Adding view action', [
            //     'enum_name' => $viewUrl->name,
            //     'generated_url' => $generatedUrl,
            // ]);

            $builder->addAction('view', [
                'url' => $generatedUrl,
                'label' => $label ,
            ]);
            $actionsAdded++;
        }
    }


    /**
     * Get delete action attributes (e.g., data attributes for JS confirmation)
     *
     * Override in child classes to customize delete behavior
     *
     * @return array<string, mixed>
     */
    abstract protected function getDeleteActionAttributes(): array;
}
