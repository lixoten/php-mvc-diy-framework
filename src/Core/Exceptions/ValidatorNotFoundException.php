<?php

declare(strict_types=1);

namespace Core\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Custom exception to be thrown when a requested validator cannot be found.
 *
 * This class extends InvalidArgumentException, as it represents a scenario
 * where an invalid argument (the validator name) was passed to a method.
 */
class ValidatorNotFoundException extends InvalidArgumentException
{
    /**
     * @var string The name of the validator that was not found.
     */
    private string $validatorName;

    /**
     * ValidatorNotFoundException constructor.
     *
     * @param string $validatorName The name of the validator that was not found.
     * @param string|null $message The exception message (optional).
     * @param int $code The exception code (optional).
     * @param Throwable|null $previous The previous throwable used for the exception chain (optional).
     */
    public function __construct(
        string $validatorName,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->validatorName = $validatorName;

        // Construct a default, helpful message if one is not provided.
        if ($message === null) {
            $message = "The validator '{$validatorName}' could not be found.";
        }

        // Call the parent constructor to set the message, code, and previous exception.
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the name of the validator that was not found.
     *
     * This getter provides access to the specific context that caused the error,
     * allowing for more specific error handling.
     *
     * @return string
     */
    public function getValidatorName(): string
    {
        return $this->validatorName;
    }
}
