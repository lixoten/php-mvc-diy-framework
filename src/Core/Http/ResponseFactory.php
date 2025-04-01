<?php

declare(strict_types=1);

namespace Core\Http;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Factory for creating HTTP responses
 */
class ResponseFactory
{
    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * Constructor
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Create a redirect response
     *
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code (default: 302 Found)
     * @return ResponseInterface
     */
    public function redirect(string $url, int $statusCode = 302): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);
        return $response->withHeader('Location', $url);
    }

    /**
     * Create a JSON response
     *
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code (default: 200 OK)
     * @return ResponseInterface
     */
    public function json($data, int $statusCode = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);
        $response = $response->withHeader('Content-Type', 'application/json');

        $body = $response->getBody();
        $body->write(json_encode($data));
        $body->rewind();

        return $response;
    }

    /**
     * Create an HTML response
     *
     * @param string $html HTML content
     * @param int $statusCode HTTP status code (default: 200 OK)
     * @return ResponseInterface
     */
    public function html(string $html, int $statusCode = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);
        $response = $response->withHeader('Content-Type', 'text/html; charset=utf-8');

        $body = $response->getBody();
        $body->write($html);
        $body->rewind();

        return $response;
    }
}
