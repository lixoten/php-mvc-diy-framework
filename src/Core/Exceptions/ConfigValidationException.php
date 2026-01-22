<?php

declare(strict_types=1);

namespace Core\Exceptions;

/**
 * Exception thrown when configuration validation fails.
 */
class ConfigValidationException extends \RuntimeException
{
    private ?string $configFile = null;
    private ?string $entityType = null;
    private ?string $validationError = null;

    public function __construct(
        string $message,
        ?string $configFile = null,
        ?string $entityType = null,
        ?string $validationError = null,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->configFile = $configFile;
        $this->entityType = $entityType;
        $this->validationError = $validationError;
    }

    public function getConfigFile(): ?string
    {
        return $this->configFile;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function getValidationError(): ?string
    {
        return $this->validationError;
    }
}