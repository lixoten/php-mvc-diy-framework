<?php

declare(strict_types=1);

namespace Core\Exceptions;

class UnauthenticatedException extends HttpException
{
    private string $attemptedResource;
    private string $authMethod;
    private ?string $reasonCode;

    public function __construct(
        string $message = null,
        int $code = 401,
        string $attemptedResource = null,
        string $authMethod = null,
        string $reasonCode = null,
        \Throwable $previous = null
    ) {
        $this->attemptedResource = $attemptedResource ?? 'unknown';
        $this->authMethod = $authMethod ?? 'unknown';
        $this->reasonCode = $reasonCode;


        // Use default message if none provided
        if ($message === null) {
            $message = "Authentication required";
        }
        // Add reason if available
        if ($reasonCode !== null) {
            // Convert reason code to user-friendly message
            switch ($reasonCode) {
                case 'expired_session':
                    $message .= " (Your session has expired)";
                    break;
                case 'invalid_credentials':
                    $message .= " (Invalid username or password)";
                    break;
                case 'account_locked':
                    $message .= " (Your account has been locked)";
                    break;
                default:
                    $message .= " (Reason: {$reasonCode})";
                    break;
            }
        }

        parent::__construct($message, $code, $previous);
    }

    public function getAttemptedResource(): string
    {
        return $this->attemptedResource;
    }

    public function getAuthMethod(): string
    {
        return $this->authMethod;
    }

    public function getReasonCode(): ?string
    {
        return $this->reasonCode;
    }
}
