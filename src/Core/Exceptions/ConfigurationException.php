<?php

declare(strict_types=1);

namespace Core\Exceptions;

class ConfigurationException extends \RuntimeException
{
    private ?string $configFile = null;
    private ?string $entityType = null;
    private ?string $suggestion = null;

    public function __construct(
        string $message,
        ?string $configFile = null,
        ?string $entityType = null,
        ?string $suggestion = null,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->configFile = $configFile;
        $this->entityType = $entityType;
        $this->suggestion = $suggestion;
    }

    public function getConfigFile(): ?string
    {
        return $this->configFile;
    }

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function getSuggestion(): ?string
    {
        return $this->suggestion;
    }
}