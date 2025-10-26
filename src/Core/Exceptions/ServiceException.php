<?php

declare(strict_types=1);

namespace Core\Exceptions;

/**
 * Exception thrown for errors that occur within service classes.
 *
 * This exception should be used for general service-layer failures, such as
 * failed API calls, invalid responses from external services, or other
 * recoverable errors that do not fit more specific exception types.
 *
 * Usage:
 *   throw new ServiceException('Failed to fetch data from external API.');
 *
 * This class extends \RuntimeException to indicate that the error is not
 * necessarily a result of invalid input, but rather an unexpected failure
 * during service execution.
 */
class ServiceException extends \RuntimeException
{
    /**
     * Constructs a new ServiceException.
     *
     * @param string $message   The exception message describing the error.
     * @param int $code         The exception code (optional, defaults to 0).
     * @param \Throwable|null $previous The previous throwable used for exception chaining (optional).
     */
    public function __construct(
        string $message = "A service error occurred.",
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
