<?php

declare(strict_types=1);

namespace App\Entities;

class RememberToken
{
    private ?int $id = null;
    private int $userId;
    private string $selector;
    private string $hashedValidator;
    private string $expiresAt;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getSelector(): string
    {
        return $this->selector;
    }

    public function setSelector(string $selector): self
    {
        $this->selector = $selector;
        return $this;
    }

    public function getHashedValidator(): string
    {
        return $this->hashedValidator;
    }

    public function setHashedValidator(string $hashedValidator): self
    {
        $this->hashedValidator = $hashedValidator;
        return $this;
    }

    public function getExpiresAt(): string
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(string $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isExpired(): bool
    {
        $expiryDate = new \DateTime($this->expiresAt);
        $now = new \DateTime();
        return $expiryDate < $now;
    }
}
