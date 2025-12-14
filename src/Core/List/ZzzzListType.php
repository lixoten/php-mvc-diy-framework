<?php

declare(strict_types=1);

namespace Core\List;

use Core\Interfaces\ConfigInterface;
use Core\List\AbstractListType;
use Core\Services\ListConfigurationService;
use Core\Services\FieldRegistryService;
use Core\Services\UrlGeneratorService;
use Psr\Log\LoggerInterface;

/**
 * list type definition
 */
class ZzzzListType extends AbstractListType
{
    //protected array $options = [];

    /** {@inheritdoc} */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
        protected ListConfigurationService $listConfigService,
        protected LoggerInterface $logger,
        protected UrlGeneratorService $urlGeneratorService,
    ) {
        parent::__construct(
            fieldRegistryService: $fieldRegistryService,
            configService: $configService,
            listConfigService: $listConfigService,
            logger: $logger,
            urlGeneratorService: $urlGeneratorService,
        );
    }



    /** {@inheritdoc} */
    protected function getDeleteActionAttributes(): array
    {
        return [
            'data-confirm' => 'Are you sure you want to delete this item?',
            'data-method' => 'DELETE',
        ];
    }
}
