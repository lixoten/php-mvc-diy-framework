<?php

declare(strict_types=1);

namespace Core\Exceptions;

use InvalidArgumentException;
use RuntimeException;

/**
 * Exception thrown when a schema definition is not found
 */
class FooException extends RuntimeException
{
        public function __construct(
        string $message = null,
        int $code = 301,
    ) {
        echo $message;
        parent::__construct($message, $code);
    }
}
