<?php

declare(strict_types=1);

namespace Core\Exceptions;

use App\Helpers\DebugRt;

class PageNotFoundException extends HttpException
{
    private ?string $requestedRoute;

    public function __construct(
        string $message = null,
        int $code = 404,
        \Throwable $previous = null,
        string $requestedRoute = null
    ) {
        $this->requestedRoute = $requestedRoute;

        // Use default message if none provided
        if ($message === null) {
            $message = "Page not found.";
        }

        // Add route to message if available
        if ($requestedRoute !== null) {
            $message .= " ({$requestedRoute})";
        }

        parent::__construct($message, $code, $previous);
    }

    public function getRequestedRoute(): ?string
    {
        return $this->requestedRoute;
    }
}
