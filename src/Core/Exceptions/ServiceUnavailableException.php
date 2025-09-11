<?php

declare(strict_types=1);

namespace Core\Exceptions;

class ServiceUnavailableException extends HttpException
{
    /**
     * Create a new server error request exception.
     *
     * @param string $message Error message
     * @param int $code Exception code (defaults to 503)
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = "Service Unavailable Error: Our service is temporarily unavailable.",
        int $code = 503,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
