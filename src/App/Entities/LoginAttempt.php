<?php

declare(strict_types=1);

namespace App\Entities;

class LoginAttempt
{
    private ?int $id = null;
    private string $usernameOrEmail;
    private string $ipAddress;
    private string $attemptedAt;
    private ?string $userAgent = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUsernameOrEmail(): string
    {
        return $this->usernameOrEmail;
    }

    public function setUsernameOrEmail(string $usernameOrEmail): self
    {
        $this->usernameOrEmail = $usernameOrEmail;
        return $this;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getAttemptedAt(): string
    {
        return $this->attemptedAt;
    }

    public function setAttemptedAt(string $attemptedAt): self
    {
        $this->attemptedAt = $attemptedAt;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): self
    {
        $this->userAgent = $userAgent;
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
}
