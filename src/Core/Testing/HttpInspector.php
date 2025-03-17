<?php

/**
 *<?php
 * // In a test or debug middleware
 * $inspector = new HttpInspector();
 * $inspector->inspectRequest($request);
 *
 * // Process request and get response
 * $response = $handler->handle($request);
 *
 * $inspector->inspectResponse($response);
 * return $response;
 *
 */

namespace Core\Testing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpInspector
{
    private bool $logToFile = false;
    private string $logFile = '';

    public function __construct(bool $logToFile = false, string $logFile = '')
    {
        $this->logToFile = $logToFile;
        $this->logFile = $logFile ?: __DIR__ . '/../../logs/http_inspector.log';
    }

    /**
     * Inspect a request object
     */
    public function inspectRequest(ServerRequestInterface $request): void
    {
        $method = $request->getMethod();
        $uri = $request->getUri();
        $headers = $request->getHeaders();
        $body = (string)$request->getBody();

        $this->log("REQUEST: $method $uri");
        $this->log("Request headers: " . json_encode($headers));

        if (!empty($body)) {
            $this->log("Request body length: " . strlen($body));
            $this->log("Request body preview: " . substr($body, 0, 200));
        }

        // For form submissions
        $parsedBody = $request->getParsedBody();
        if (!empty($parsedBody)) {
            $this->log("Form data: " . json_encode($parsedBody));
        }
    }

    /**
     * Inspect a response object
     */
    public function inspectResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        $headers = $response->getHeaders();
        $body = (string)$response->getBody();

        $this->log("RESPONSE: HTTP $statusCode");
        $this->log("xResponse headers: " . json_encode($headers));
        $this->log("xResponse body length: " . strlen($body));

        if (strlen($body) > 0) {
            $this->log("xResponse body preview: " . substr($body, 0, 200));
        }

        // Rewind the body so it can be read again
        if ($body->isSeekable()) {
            $body->rewind();
        }
    }

    /**
     * Log message to file or error log
     */
    private function log(string $message): void
    {
        if ($this->logToFile) {
            file_put_contents(
                $this->logFile,
                date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL,
                FILE_APPEND
            );
        } else {
            error_log($message);
        }
    }
}