<?php

declare(strict_types=1);

namespace Core\Exceptions;

use App\Helpers\DebugRt;

/**
 * Exception thrown when a specific record (entity) is not found in the database.
 *
 * This exception provides contextual information such as the entity type, ID,
 * and optional helpful links to assist in debugging and error handling.
 *
 * @example
 * // Throw a generic "record not found" exception
 * throw new RecordNotFoundException();
 *
 * @example
 * // Throw a specific "user not found" exception with an ID
 * throw new RecordNotFoundException(entityType: 'user', entityId: 123);
 *
 * @example
 * // Throw an exception with a custom message and helpful links
 * $links = ['docs' => 'https://example.com/docs/api'];
 * throw new RecordNotFoundException(message: 'User not found in the cache.', helpfulLinks: $links);
 *
 * @param string          $entityType     The type of entity that was not found.
 * @param int|string|null $entityId       The ID of the entity that was not found.
 * @param array           $helpfulLinks   Optional associative array of helpful links or resources.
 * @param string|null     $message        Optional custom exception message.
 * @param int             $code           The HTTP status code (default: 404 Not Found).
 * @param \Throwable|null $previous       The previous throwable used for the exception chaining.
 */
class RecordNotFoundException extends HttpException
{
    private string $entityType;
    private $entityId;
    private $helpfulLinks;


    /**
     * Constructs a new RecordNotFoundException.
     *
     * @param string          $entityType     The type of entity that was not found.
     * @param int|string|null $entityId       The ID of the entity that was not found.
     * @param array           $helpfulLinks   Optional array of helpful links or resources.
     * @param string|null     $message        Optional custom exception message.
     * @param int             $code           The HTTP status code (default: 404 Not Found).
     * @param \Throwable|null $previous       The previous throwable used for the exception chaining.
     */
    public function __construct(
        string $entityType = 'record',
        $entityId = null,
        $helpfulLinks = [],
        string $message = null,
        int $code = 404,
        \Throwable $previous = null
    ) {
        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->helpfulLinks = $helpfulLinks;
        //DebugRt::j('1', '', '111');
        // Auto-generate message if not provided
        if ($message === null) {
            $message = ucfirst($entityType) . ($entityId !== null ? " with ID '$entityId'" : '') . " not found..";
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the type of the entity that was not found.
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return $this->entityType;
    }

    /**
     * Get the ID of the entity that was not found.
     *
     * @return int|string|null
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Get an array of helpful links for this error.
     *
     * @return array
     */
    public function getHelpfulLinks(): array
    {
        return $this->helpfulLinks ?? [];
    }
}
