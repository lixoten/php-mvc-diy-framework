<?php

declare(strict_types=1);

namespace Core\Exceptions;

class BadRequestException extends HttpException
{
    /**
     * Create a new bad request exception for malformed requests.
     *
     * @param string $message Error message
     * @param int $code Exception code (defaults to 400)
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = "Bad Request: The server cannot process the request",
        int $code = 400,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
