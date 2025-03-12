<?php

declare(strict_types=1);

namespace Core\Http;

class Request implements RequestInterface
{
    private string $method;
    private string $uri;
    private array $queryParams;
    private array $postData;
    private array $serverParams;
    private array $headers;
    private array $cookies;
    private array $attributes = [];

    public function __construct(
        string $method,
        string $uri,
        array $queryParams = [],
        array $postData = [],
        array $serverParams = [],
        array $headers = [],
        array $cookies = []
    ) {
        $this->method = $method;
        $this->uri = $uri;
        $this->queryParams = $queryParams;
        $this->postData = $postData;
        $this->serverParams = $serverParams;
        $this->headers = $headers;
        $this->cookies = $cookies;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        return parse_url($this->uri, PHP_URL_PATH) ?: '/';
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getQueryParam(string $name, $default = null)
    {
        return $this->queryParams[$name] ?? $default;
    }

    public function getPostData(): array
    {
        return $this->postData;
    }

    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name, $default = null)
    {
        return $this->headers[$name] ?? $default;
    }

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function withAttribute(string $name, $value): RequestInterface
    {
        $clone = clone $this;
        $clone->attributes[$name] = $value;
        return $clone;
    }

    public function getAttribute(string $name, $default = null)
    {
        return $this->attributes[$name] ?? $default;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
