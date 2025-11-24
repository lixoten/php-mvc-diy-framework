<?php

declare(strict_types=1);

namespace Core\View;

use Core\Interfaces\ConfigInterface;
use Core\View\AbstractViewType;
use Core\Services\ViewConfigurationService;
use Core\Services\FieldRegistryService;
use Psr\Log\LoggerInterface;

/**
 * Default "Zzzz" View type definition.
 *
 * This class serves as a basic, feature-agnostic ViewType that can be used
 * as a default or extended by specific feature ViewTypes. It delegates
 * most of its logic to the AbstractViewType.
 */
class ZzzzViewType extends AbstractViewType
{
    /** @var array<string, mixed> */
    protected array $options = [];

    /** {@inheritdoc} */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
        protected ViewConfigurationService $viewConfigService,
        protected LoggerInterface $logger,
    ) {
        parent::__construct(
            fieldRegistryService: $fieldRegistryService,
            configService: $configService,
            viewConfigService: $viewConfigService,
            logger: $logger,
        );

        // ✅ Set a default focus for generic usage, or this would be overridden
        //    by a controller's specific setFocus call for a feature-specific view.
        // $this->setFocus(
        //     'zzzz_view',     // pageKey for generic view
        //     'zzzz',          // pageName for generic entity
        //     'view',          // pageAction
        //     'Zzzz',          // pageFeature
        //     'zzzz'           // pageEntity
        // );
    }

}
/*

Okay, let's get the View pattern aligned with the List/Form (external renderer) pattern.

Here is the list of files we will need to create or modify, in the order we will approach them:

ViewInterface.php
View.php
ViewBuilderInterface.php
ViewBuilder.php
ViewFactoryInterface.php
ViewFactory.php
AbstractViewType.php
ViewTypeInterface.php
ZzzzViewType.php
ViewRendererInterface.php
AbstractViewRenderer.php
BootstrapViewRenderer.php
ViewRendererRegistry.php
AbstractCrudController.php
TestyController.php
dependencies.php

*/