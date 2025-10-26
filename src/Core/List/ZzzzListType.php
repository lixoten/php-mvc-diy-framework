<?php

declare(strict_types=1);

// namespace App\Features\Testy\List;
namespace Core\List;

use Core\Interfaces\ConfigInterface;
use Core\List\AbstractListType;
use Core\Services\ConfigService;
use Core\Services\FieldRegistryService;

/**
 * Testy list type definition
 */
class ZzzzListType extends AbstractListType
{
    protected array $options = [];

    /** {@inheritdoc} */
    public function __construct(
        protected FieldRegistryService $fieldRegistryService,
        protected ConfigInterface $configService,
    ) {
        $this->fieldRegistryService = $fieldRegistryService;
        $this->configService        = $configService;

        parent::__construct(
            fieldRegistryService: $this->fieldRegistryService,
            configService: $this->configService,
        );
    }



    /** {@inheritdoc} */
    protected function getDeleteActionAttributes(): array
    {
        return [
                'testy-id' => '{id}',
                'testy-title' => '{title}',
                // 'testy-created_at' => '{created_at}',
                // 'testy-foofoo' => 'shit',
        ];
    }
}
