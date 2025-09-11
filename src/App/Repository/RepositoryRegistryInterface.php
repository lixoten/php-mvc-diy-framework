<?php

declare(strict_types=1);

namespace App\Repository;

use DI\NotFoundException;

// Dynamic-me 3
/**
 * Provides access to repositories based on an entity type identifier.
 */
interface RepositoryRegistryInterface
{
    /**
     * Gets the repository associated with the given entity type.
     *
     * @param string $entityType The identifier for the entity (e.g., 'posts', 'users').
     * @return object The repository instance. Should ideally implement a common interface.
     * @throws RepositoryNotFoundException If no repository is registered for the type.
     */
    public function getRepository(string $entityType): object;

    /**
     * Checks if a repository exists for the given entity type.
     *
     * @param string $entityType
     * @return bool
     */
    public function hasRepository(string $entityType): bool;
}
