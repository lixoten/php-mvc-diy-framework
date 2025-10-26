<?php

declare(strict_types=1);

namespace Core\Exceptions;

use Exception;

/**
 * Exception thrown when a requested formatter is not found
 */
class FormatterNotFoundException extends Exception
{
    /**
     * Create a new FormatterNotFoundException
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Exception|null $previous Previous exception
     */
    public function __construct(
        string $message = 'Formatter not found',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
