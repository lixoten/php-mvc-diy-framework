<?php

namespace Core\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class HttpFactory
{
    private Psr17Factory $factory;
    private ServerRequestCreator $requestCreator;

    public function __construct()
    {
        $this->factory = new Psr17Factory();
        $this->requestCreator = new ServerRequestCreator(
            $this->factory, // ServerRequestFactory
            $this->factory, // UriFactory
            $this->factory, // UploadedFileFactory
            $this->factory  // StreamFactory
        );
    }

    /**
     * Create a ServerRequest from globals ($_SERVER, $_GET, etc)
     *
     * @return ServerRequestInterface
     */
    public function createServerRequestFromGlobals(): ServerRequestInterface
    {
        return $this->requestCreator->fromGlobals();
    }

    /**
     * Create a Response
     *
     * @param int $statusCode HTTP status code
     * @param string $reasonPhrase Reason phrase
     * @return ResponseInterface
     */
    public function createResponse(int $statusCode = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return $this->factory->createResponse($statusCode, $reasonPhrase);
    }

    /**
     * Create a Stream from a string
     *
     * @param string $content
     * @return StreamInterface
     */
    public function createStream(string $content = ''): StreamInterface
    {
        return $this->factory->createStream($content);
    }

    /**
     * Create a URI from a string
     *
     * @param string $uri
     * @return UriInterface
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return $this->factory->createUri($uri);
    }
}
