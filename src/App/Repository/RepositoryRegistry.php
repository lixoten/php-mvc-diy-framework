<?php

declare(strict_types=1);

namespace App\Repository;

use DI\NotFoundException;
use Psr\Container\ContainerInterface;

// Dynamic-me 3
class RepositoryRegistry implements RepositoryRegistryInterface
{
    private ContainerInterface $container;
    /** @var array<string, string> Maps entity type string to repository service ID/class name */
    private array $repositoryMap;

    /**
     * @param ContainerInterface $container The DI container to resolve repositories.
     * @param array<string, string> $repositoryMap Map of entity type => repository service ID/class name.
     */
    public function __construct(ContainerInterface $container, array $repositoryMap)
    {
        $this->container = $container;
        $this->repositoryMap = $repositoryMap;
    }

    public function getRepository(string $entityType): object
    {
        if (!$this->hasRepository($entityType)) {
            throw new NotFoundException("No repository registered for entity type: " . $entityType);
        }

        $repositoryServiceId = $this->repositoryMap[$entityType];

        // Get the repository instance from the container
        // Add error handling if the service doesn't exist or isn't the expected type
        return $this->container->get($repositoryServiceId);
    }

    public function hasRepository(string $entityType): bool
    {
        return isset($this->repositoryMap[$entityType]);
    }
}
