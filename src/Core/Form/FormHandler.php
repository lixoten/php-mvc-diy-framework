<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Form\CSRF\CSRFToken;
//use Psr\Http\Message\RequestInterface;
use App\Helpers\DebugRt as Debug;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles form submissions from HTTP requests
 */
class FormHandler implements FormHandlerInterface
{
    private CSRFToken $csrf;

    /**
     * Constructor
     *
     * @param CSRFToken $csrf
     */
    public function __construct(CSRFToken $csrf)
    {
        $this->csrf = $csrf;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(FormInterface $form, ServerRequestInterface $request): bool
    {
        // Only process POST requests
        if ($request->getMethod() !== 'POST') {
            return false;
        }

        // Get form data from request
        $data = $this->parseRequestData($request);
        //Debug::p($data);

        // Debug the data we're getting from the request
        error_log("Form data received: " . print_r($data, true));

        $token = $data['csrf_token'] ?? '';
        error_log("CSRF token received: " . $token);

        if (!$this->csrf->validate($token)) {
            $form->addError('_form', 'CSRF token validation failed. Please try again.');
            return false;
        }

        // Remove token from data before submitting to form
        unset($data['csrf_token']);

        // Submit data to form (validates internally)
        $form->submit($data);

        // Return validation result
        return $form->isValid();
    }

    /**
     * Parse request data based on content type
     *
     * @param RequestInterface $request
     * @return array
     */
    private function parseRequestData(ServerRequestInterface $request): array
    {
        // First try the parsed body (works for application/x-www-form-urlencoded and multipart/form-data)
        $parsedBody = $request->getParsedBody();
        if (is_array($parsedBody) && !empty($parsedBody)) {
            return $parsedBody;
        }

        $contentType = $request->getHeaderLine('Content-Type');

        // Handle application/json
        if (strpos($contentType, 'application/json') !== false) {
            $content = (string) $request->getBody();
            return json_decode($content, true) ?? [];
        }

        // Try to parse form data from raw body as fallback
        $content = (string) $request->getBody();
        if (!empty($content)) {
            parse_str($content, $data);
            return $data;
        }

        return [];
    }
}
