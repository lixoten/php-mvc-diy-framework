<?php

declare(strict_types=1);

namespace Core\Form;

use Core\Form\CSRF\CSRFToken;
use Core\Form\Event\FormEvent;
use Core\Form\Event\FormEvents;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Form handler implementation
 */
class FormHandler implements FormHandlerInterface
{
    private CSRFToken $csrf;
    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * Constructor
     *
     * @param CSRFToken $csrf
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(CSRFToken $csrf, ?EventDispatcherInterface $eventDispatcher = null)
    {
        $this->csrf = $csrf;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(FormInterface $form, ServerRequestInterface $request): bool
    {
        // Only process if it's a POST request
        if ($request->getMethod() !== 'POST') {
            return false;
        }

        // Get form data from request
        $data = $this->parseRequestData($request);

        // Extract CSRF token
        $token = $data['csrf_token'] ?? '';
        unset($data['csrf_token']);

        // Dispatch PRE_SUBMIT event
        $this->dispatchEvent(FormEvents::PRE_SUBMIT, $form, $data);

        // Validate CSRF token
        if (!$form->validateCSRFToken($token)) {
            $form->addError('_form', 'Invalid form submission.');
            return false;
        }

        // Set form data
        $form->setData($data);

        // Dispatch POST_SUBMIT event
        $this->dispatchEvent(FormEvents::POST_SUBMIT, $form, $data);

        // Dispatch PRE_VALIDATE event
        $this->dispatchEvent(FormEvents::PRE_VALIDATE, $form, $data);

        // Validate form
        $isValid = $form->validate();

        // Dispatch POST_VALIDATE event
        $this->dispatchEvent(FormEvents::POST_VALIDATE, $form, $data);

        return $isValid;
    }

    /**
     * Parse request data based on content type
     *
     * @param ServerRequestInterface $request
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

        // If it's a multipart form (with file uploads)
        if (strpos($contentType, 'multipart/form-data') !== false) {
            // Get parsed body and uploaded files
            $parsedBody = $request->getParsedBody() ?? [];
            $uploadedFiles = $request->getUploadedFiles() ?? [];

            // Merge everything into a single array
            return array_merge($parsedBody, $uploadedFiles);
        }

        // Try to parse form data from raw body as fallback
        $content = (string) $request->getBody();
        if (!empty($content)) {
            parse_str($content, $data);
            return $data;
        }

        return [];
    }

    /**
     * Dispatch a form event
     *
     * @param string $eventName
     * @param FormInterface $form
     * @param mixed $data
     */
    private function dispatchEvent(string $eventName, FormInterface $form, $data = null): void
    {
        if ($this->eventDispatcher) {
            $event = new FormEvent($eventName, $form, $data);
            $this->eventDispatcher->dispatch($event);
        }
    }
}
