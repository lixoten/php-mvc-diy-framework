<?php

declare(strict_types=1);

namespace Core\Http;

use Psr\Http\Message\ResponseInterface;

class ResponseEmitter
{
    /**
     * Emit a response to the client
     *
     * @param ResponseInterface $response
     * @return void
     */
    public function emit(ResponseInterface $response): void
    {
        //error_log("EMITTER: Starting to emit response with status code: " . $response->getStatusCode());
        //error_log("EMITTER: Body length: " . $response->getBody()->getSize());


        $statusCode = $response->getStatusCode();

        // Send status line
        header(sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $statusCode,
            $response->getReasonPhrase() ? ' ' . $response->getReasonPhrase() : ''
        ), true, $statusCode);

        // Send headers
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        // Send body
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (!$body->eof()) {
            echo $body->read(8192);
        }

        //error_log("EMITTER: Finished emitting response");
    }
}
