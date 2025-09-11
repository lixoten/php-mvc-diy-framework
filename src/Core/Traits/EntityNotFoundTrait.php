<?php

namespace Core\Traits;

use Core\Constants\Urls;
use Core\Exceptions\RecordNotFoundException;

/**
 * Trait for handling entity not found scenarios
 */
trait EntityNotFoundTrait
{
    /**
     * Throw a record not found exception
     *
     * @param string $entityType Entity type (Post, Product, etc)
     * @param mixed $entityId ID of the entity (can be null for missing ID)
     * @param string|null $message Custom message (optional)
     * @param array $additionalLinks Additional helpful links
     * @throws RecordNotFoundException
     */
    protected function throwRecordNotFound(
        string $entityType,
        $entityId = null,
        ?string $message = null,
        array $additionalLinks = []
    ): void {
        // Generate standard message if none provided
        if ($message === null) {
            $message = $entityId === null
                ? "{$entityType} ID is missing from the request"
                : "{$entityType} not found. It may have been deleted or never existed.";
        }

        // Get standard links for this entity type
        $helpfulLinks = $this->getStandardLinksForEntity($entityType);

        // Add any additional custom links
        if (!empty($additionalLinks)) {
            $helpfulLinks = array_merge($helpfulLinks, $additionalLinks);
        }

        throw new RecordNotFoundException(
            message: $message,
            entityType: $entityType,
            entityId: $entityId,
            helpfulLinks: $helpfulLinks
        );
    }

    /**
     * Get standard links for a given entity type
     *
     * @param string $entityType
     * @return array
     */
    protected function getStandardLinksForEntity(string $entityType): array
    {
        // Base links for all entity types
        $links = [
            'Go to Dashboard' => Urls::USER_DASHBOARD
        ];

        // Add entity-specific links
        switch (strtolower($entityType)) {
            case 'post':
                $links['Return to Posts List'] = Urls::STORE_POSTS;
                $links['Create a New Post'] = Urls::STORE_POSTS_CREATE;
                break;

            case 'product':
                $links['Return to Products'] = Urls::STORE_PRODUCTS;
                $links['Create a New Product'] = Urls::STORE_PRODUCTS_ADD;
                break;

            case 'gallery':
                $links['Return to Galleries'] = Urls::STORE_GALLERIES;
                $links['Create a New Gallery'] = Urls::STORE_GALLERIES_ADD;
                break;

            // Add more entity types as needed
        }

        return $links;
    }

    /**
     * Convenience method for post not found
     */
    protected function throwPostNotFound($postId = null, ?string $message = null, array $additionalLinks = []): void
    {
        $this->throwRecordNotFound('Post', $postId, $message, $additionalLinks);
    }

    /**
     * Convenience method for product not found
     */
    protected function throwProductNotFound($productId = null, ?string $message = null, array $additionalLinks = []): void
    {
        $this->throwRecordNotFound('Product', $productId, $message, $additionalLinks);
    }

    // Add more convenience methods for other common entity types
}
