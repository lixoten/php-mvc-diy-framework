<?php

declare(strict_types=1);

namespace Core\Exceptions;

class RecordNotFoundException extends HttpException
{
    private string $entityType;
    private $entityId;

    public function __construct(
        string $entityType = 'record',
        $entityId = null,
        string $message = null,
        int $code = 404,
        \Throwable $previous = null
    ) {
        $this->entityType = $entityType;
        $this->entityId = $entityId;

        // Auto-generate message if not provided
        if ($message === null) {
            $message = ucfirst($entityType) . ($entityId !== null ? " with ID '$entityId'" : '') . " not found..";
        }

        parent::__construct($message, $code, $previous);
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEntityId()
    {
        return $this->entityId;
    }
}
