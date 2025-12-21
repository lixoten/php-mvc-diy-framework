<?php

declare(strict_types=1);

namespace App\Features\Image\Exceptions;

use RuntimeException;

class ImageRecordImmutableException extends RuntimeException
{
    public function __construct(
        string $message = "Cannot change the file associated with an existing image record. Please create a new record.",
        int $code = 400, // Bad Request
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
