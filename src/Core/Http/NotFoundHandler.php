<?php

declare(strict_types=1);

namespace Core\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * A simple fallback handler that returns a 404 Not Found response.
 */
class NotFoundHandler implements RequestHandlerInterface
{
    private HttpFactory $httpFactory;

    public function __construct(HttpFactory $httpFactory)
    {
        $this->httpFactory = $httpFactory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = $this->httpFactory->createResponse(404);
        $response->getBody()->write('404 Not Found');
        return $response;
    }
}
