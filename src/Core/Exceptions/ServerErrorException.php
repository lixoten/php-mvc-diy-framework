<?php

declare(strict_types=1);

namespace Core\Exceptions;

class ServerErrorException extends HttpException
{
    /**
     * Create a new server error request exception.
     *
     * @param string $message Error message
     * @param int $code Exception code (defaults to 500)
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = "Server Error: The server cannot process the request",
        int $code = 500,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
