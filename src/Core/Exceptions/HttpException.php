<?php

declare(strict_types=1);

namespace Core\Exceptions;

abstract class HttpException extends \RuntimeException
{
    /**
     * Get the HTTP status code for this exception
     *
     * @return int The HTTP status code
     */
    public function getHttpStatusCode(): int
    {
        return $this->code;
    }
}
