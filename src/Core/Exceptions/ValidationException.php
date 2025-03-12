<?php

declare(strict_types=1);

namespace Core\Exceptions;

class ValidationException extends HttpException
{
    protected array $errors = [];

    /**
     * Create a new validation exception for invalid data.
     *
     * @param array $errors Validation errors by field
     * @param string $message Error message
     * @param int $code Exception code (defaults to 422)
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        array $errors = [],
        string $message = "Validation failed: The provided data was invalid",
        int $code = 422,
        \Throwable $previous = null
    ) {
        $this->errors = $errors;

        // Generate a better default message if none provided
        if ($message === null) {
            $message = "Validation failed: The provided data was invalid";

            // Optionally add summary of errors
            if (count($errors) > 0) {
                $message .= " (" . count($errors) . " field" .
                    (count($errors) > 1 ? "s" : "") . " with errors)";
            }
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get validation errors associated with this request
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if the exception has validation errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
