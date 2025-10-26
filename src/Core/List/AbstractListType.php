<?php

declare(strict_types=1);

namespace Core\List;

use App\Enums\Url;
use App\Helpers\DebugRt;
use Core\Interfaces\ConfigInterface;
use Core\Services\ConfigService;
use Core\Services\FieldRegistryService;

/**
 * Abstract base class for list types.
 *
 * Handles list configuration, rendering options, and column definitions.
 * Uses FieldRegistryService for field/column definitions with fallback logic:
 *   1. Page/view context (set via setPageConfigKey)
 *   2. Entity/table context (set via setEntityName)
 *   3. Base/global config
 */
abstract class AbstractListType implements ListTypeInterface
{
    protected array $options = [];
    public string $routeType = 'root';
    protected array $urlEnumArray;

    public readonly string $pageConfigKey;
    public readonly string $entityName;

    // protected FieldRegistryService $fieldRegistryService;


    // /**
    //  * Parent options that can be overridden by child classes.
    //  * @var array
    //  */
    // private const PARENT_OPTIONS = [
    //     'default_sort_key' => 'created_at',
    //     'default_sort_direction' => 'DESC',
    //     'pagination' => [
    //         'per_page' => 3
    //     ],
    //     'render_options' => [
    //         'title' => 'list.posts.title 333',
    //         'show_actions' => true,
    //         'show_action_add' => true,
    //         'show_action_edit' => true,
    //         'show_action_del' => true,
    //         'show_action_status' => false,
    //         'list_columns' => [
    //             'id', 'title', 'status', 'created_at'
    //         ]
    //     ]
    // ];


    /**
     * Constructor.
     *
     * @param FieldRegistryService $fieldRegistryService Service for field/column definitions.
     */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
    ) {
        $this->fieldRegistryService = $fieldRegistryService;
        $this->configService = $configService;

        // $this->init();

        //$this->options = array_replace_recursive(self::PARENT_OPTIONS, $this->options);
        // Merge parent and child render options
        //$this->initializeOptions($this->options);
    }



    /** {@inheritdoc} */
    public function setFocus(string $pageConfigKey, string $entityName ) : void
    {
        $this->pageConfigKey = $pageConfigKey;
        $this->entityName = $entityName;

        $this->init();
    }


    /** {@inheritdoc} */
    public function getOptions(): array
    {
        return $this->options['options'] ?? [];
    }
    /** {@inheritdoc} */
    public function setOptions(array $options): void
    {
        $this->options['options'] = $options;
    }



    /** {@inheritdoc} */
    public function getRenderOptions(): array
    {
        return $this->options['render_options'];
    }
    /** {@inheritdoc} */
    public function setRenderOptions(array $renderOptions): void
    {
        $this->options['render_options'] = $renderOptions;
    }


    /** {@inheritdoc} */
    public function getFields(): array
    {
        return $this->options['fields'];
    }

    /** {@inheritdoc} */
    public function setFields(array $fields): void
    {
        $validFields = $this->fieldRegistryService->filterAndValidateFields(
            $fields,
            $this->pageConfigKey,
            $this->entityName
        );
        $this->options['fields'] = $validFields;
    }


    /** {@inheritdoc} */
    public function getPaginationOptions(): array
    {
        return $this->options['pagination'];
    }
    /** {@inheritdoc} */
    public function setPaginationOptions(array $paginationOptions): void
    {
        $this->options['pagination'] = $paginationOptions;
    }


    /** {@inheritdoc} */
    public function setUrlDependentRenderOptions(): void
    {
        if ($this->routeType === 'public') {
            $prefix = strtoupper('core' . '_' . $this->entityName);
        } else {
            $prefix = strtoupper($this->routeType . '_' . $this->entityName);
        }
        $this->urlEnumArray = Url::getSection($prefix);
        // if ($this->routeType === 'account') {
            // $this->urlEnumArray = Url::getSection('ACCOUNT_' . $rrr);
        // } elseif ($this->routeType === 'store') {
            // $this->urlEnumArray = Url::getSection('STORE_POST');
        // } else {
            // $this->urlEnumArray = Url::getSection('CORE_POST');
        // }

        $this->options['render_options']['add_button_label']    = $this->urlEnumArray['create']->label();
        //$this->options['render_options']['add_button_icon']     = $this->urlEnumArray['create']->icon();
        $this->options['render_options']['add_url']             = $this->urlEnumArray['create']->url();
        $this->options['render_options']['pagination_url']      = $this->urlEnumArray['index']->paginationUrl();
    }







    /**
     * Get additional attributes for the delete action.
     *
     * Can be overridden by child classes to provide custom attributes.
     *
     * @return array Associative array of attributes.
     */
    protected function getDeleteActionAttributes(): array
    {
        return [];
    }



    /**
     * Add action columns (view, edit, delete) to the list builder.
     *
     * @param ListBuilderInterface $builder The list builder instance.
     * @return void
     */
    private function addActions(ListBuilderInterface $builder): void
    {
        // Todo this is where the logic for Toggle Status button add goes
        // Add actions
        $builder->addAction('view', $this->urlEnumArray['view']->toLinkData(['id' => '{id}']));
        if ($this->options['render_options']['show_action_edit']) {
            $builder->addAction('edit', $this->urlEnumArray['edit']->toLinkData(['id' => '{id}']));
        }
        $builder->addAction('delete', $this->urlEnumArray['delete']->toLinkData(
            ['id' => '{id}'],
            // icon: null
            // label: 'Delete Post',
            attributes: $this->getDeleteActionAttributes()
        ));
    }


    public function validateFields(array $fields): array
    {
        if (!isset($fields) || !is_array($fields) || empty($fields)) {
            $this->logWarning("No Fields/Columns found. - ERR-DEV85");
        }

        $validFields = $this->fieldRegistryService->filterAndValidateFields(
            $fields,
            $this->pageConfigKey,
            $this->entityName
        );

        return $validFields;
    }


    /** {@inheritdoc} */
    public function buildList(ListBuilderInterface $builder): void
    {
        $builder->setListTitle($this->options['render_options']['title'] ?? 'list.posts.title');

        $flattenOptions = $this->options;
        unset($flattenOptions['pagination']);
        unset($flattenOptions['render_options']);
        $builder->setOptions($flattenOptions);

        $builder->setRenderOptions($this->getRenderOptions());
        $builder->setPagination($this->getPaginationOptions() ?? []);

        $columns = $this->getFields();
        foreach ($columns as $name) {
            //$columnDef = $this->fieldRegistryService->getFieldWithFallbacks($columnName, $this);
            $columnDef = $this->fieldRegistryService->getFieldWithFallbacks(
                $name,
                $this->pageConfigKey,
                $this->entityName
            );
            if ($columnDef && isset($columnDef['list'])) {
                $builder->addColumn($name, $columnDef['label'], $columnDef['list']);
            }
        }

        if (!empty($this->options['render_options']['show_actions'])) {
            $this->addActions($builder);
        }
    }


    /**
     * Log a warning message in development mode
     */
    private function logWarning(string $message): void
    {
        if ($_ENV['APP_ENV'] === 'development') {
            trigger_error("Form Warning: {$message}", E_USER_WARNING);
        }

        // Always log to system log
        error_log("Form Warning: {$message}");
    }


    private function init(): void
    {
        $securityConfig = $this->configService->get('security');
        if ($securityConfig === null) {
            throw new \RuntimeException('Fatal error: Required config file "security.php" is missing.');
        }
        $forceCaptcha = $securityConfig['captcha']['force_captcha'] ?? false;

        ///////////////////////////////////////////////////////////////////////
        // Retrieve Default Config values
        ///////////////////////////////////////////////////////////////////////
        $defaultConfig = $this->configService->get('view.list', []);
        //$formTheme              = $defaultConfig['default_form_theme'] ?? "christmas";

        //$defaultConfig2 = $this->configService->get('view.form', []); // fix - temp shit, safe to remove


        $defaultOptions         = $defaultConfig['options'];
        $defaultPagination      = $defaultConfig['pagination'];
        $defaultRenderOptions   = $defaultConfig['render_options'];
        $defaultListFields      = $defaultConfig['list_fields'];
        ///////////////////////////////////////////////////////////////////////


        ///////////////////////////////////////////////////////////////////////
        // Retrieve View Config values
        ///////////////////////////////////////////////////////////////////////
        $pageConfigKey = $this->pageConfigKey;
        //$pageConfigKey = 'testy_list'; // dangerdanger

        $viewConfig = $this->configService->get('view_options/' . $pageConfigKey); // loads "list_fields/posts.php"
        if ($viewConfig === null) {
            throw new \RuntimeException(
                "Fatal error: Required config file \"view_options/{$pageConfigKey}.php\" is missing."
            );
        }

        $viewOptions         = $viewConfig['options'];
        $viewPagination      = $viewConfig['pagination'];
        $viewRenderOptions   = $viewConfig['render_options'];
        $viewListFields      = $viewConfig['list_fields'];
        ///////////////////////////////////////////////////////////////////////


        ///////////////////////////////////////////////////////////////////////
        // Merge default and view Config values
        // Except for List_field values, they replace if set
        ///////////////////////////////////////////////////////////////////////
        $finalOptions          = array_merge($defaultOptions, $viewOptions);
        $finalPagination       = array_merge($defaultPagination, $viewPagination);
        $finalRenderOptions    = array_merge($defaultRenderOptions, $viewRenderOptions);
        if (!isset($viewListFields) || !is_array($viewListFields) || empty($viewListFields)) {
            $finalListFields   = $defaultListFields;
        } else {
            $finalListFields   = $viewListFields;
        }

        $this->setOptions($finalOptions);
        $this->setPaginationOptions($finalPagination);
        $this->setRenderOptions($finalRenderOptions);
        $this->setFields($finalListFields);
    }
}
