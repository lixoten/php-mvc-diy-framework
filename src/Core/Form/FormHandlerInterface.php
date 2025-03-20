<?php

declare(strict_types=1);

namespace Core\Form;

// use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for form handlers
 */
interface FormHandlerInterface
{
    /**
     * Handle a form submission
     *
     * @param FormInterface $form The form to handle
     * @param ServerRequestInterface $request The current request
     * @return bool True if form was submitted and valid, false otherwise
     */
    public function handle(FormInterface $form, ServerRequestInterface $request): bool;
}
