<?php

declare(strict_types=1);

namespace App\Features\Image\Exceptions;

use RuntimeException;

class DuplicateImageContentException extends RuntimeException
{
    private string $duplicateHash;
    private int $existingImageId;

    public function __construct(
        string $duplicateHash,
        int $existingImageId,
        string $message = "An image with this content already exists.",
        int $code = 409, // Conflict
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->duplicateHash = $duplicateHash;
        $this->existingImageId = $existingImageId;
    }

    public function getDuplicateHash(): string
    {
        return $this->duplicateHash;
    }

    public function getExistingImageId(): int
    {
        return $this->existingImageId;
    }
}
