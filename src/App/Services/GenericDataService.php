<?php

declare(strict_types=1);

namespace App\Services;

// Dynamic-me 3
// REMOVE: use App\Repository\PostRepositoryInterface;
use App\Repository\RepositoryRegistryInterface; // ADD THIS
use App\Services\Interfaces\GenericDataServiceInterface;
use InvalidArgumentException;
use RuntimeException;

class GenericDataService implements GenericDataServiceInterface
{
    // REMOVE: private PostRepositoryInterface $postRepository;
    private RepositoryRegistryInterface $repositoryRegistry; // ADD THIS

    public function __construct(
        RepositoryRegistryInterface $repositoryRegistry
    ) {
        // REMOVE: $this->postRepository = $postRepository;
        $this->repositoryRegistry = $repositoryRegistry; // Assign Registry
    }

    /** {@inheritdoc} */
    public function fetchListData(
        string $entityType, // This is the key now
        array $fields,     // These define what data to extract
        array $criteria, // Changed from ?int $storeId
        int $page,
        int $limit,
        array $orderBy = []
    ): array {
        // Get the correct repository using the entityType string
        $repository = $this->repositoryRegistry->getRepository($entityType);
        $offset = ($page - 1) * $limit;

        // --- Use generic repository methods ---
        // Assume repositories have findBy(criteria, orderBy, limit, offset)
        // and countBy(criteria) methods.
        if (!method_exists($repository, 'findBy')) {
            throw new RuntimeException("Repository for '$entityType' missing required 'findBy' method.");
        }
        if (!method_exists($repository, 'countBy')) {
            throw new RuntimeException("Repository for '$entityType' missing required 'countBy' method.");
        }


        // Pass the criteria directly to the repository
        $records = $repository->findBy($criteria, $orderBy, $limit, $offset);
        $totalRecords = $repository->countBy($criteria);
        // --- End generic repository methods ---

        //$records = $repository->findByStoreId($storeId, $orderBy, $limit, $offset);
        //$totalRecords = $repository->countByStoreId($storeId); // Assumes this method exists
        // $totalRecords = $repository->countAll(); // Assumes this method exists

        $totalPages = ($limit > 0) ? (int) ceil($totalRecords / $limit) : 0;

        // Map the raw records based on the requested $fields
        $itemRecords = array_map(function ($record) use ($repository, $fields) {
            return $repository->toArray($record, $fields);
        }, $records);

        return [
            'items' => $itemRecords,
            'totalRecords' => $totalRecords,
            'totalPages' => $totalPages,
        ];
    }


    /** {@inheritdoc} */
    public function fetchEntityById(string $entityType, int $entityId): ?object
    {
        // Get the correct repository using the entityType string
        $repository = $this->repositoryRegistry->getRepository($entityType);
        if (!method_exists($repository, 'findById')) {
            throw new RuntimeException("Repository for '$entityType' missing 'findById' method.");
        }
        return $repository->findById($entityId);
    }


    /** {@inheritdoc} */
    public function createNewEntity(string $entityType): object
    {
        // Map entity types to their class names
        $entityClasses = [
            'posts' => \App\Entities\Post::class,
            'albums' => \App\Entities\Album::class,
            // Add other entity types as needed
        ];

        if (!isset($entityClasses[$entityType])) {
            throw new RuntimeException("No entity class defined for type: $entityType");
        }

        $className = $entityClasses[$entityType];
        return new $className();
    }
}
