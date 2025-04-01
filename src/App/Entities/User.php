<?php

declare(strict_types=1);

namespace App\Entities;

use App\Enums\UserStatus;

class User
{
    private ?int $userId = null;
    private string $username;
    private string $email;
    private string $passwordHash;
    private array $roles = [];
    private UserStatus $status = UserStatus::PENDING;
    private ?string $activationToken = null;
    private ?string $resetToken = null;
    private ?string $resetTokenExpiry = null;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    /**
     * Get user ID
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Set user ID
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Get username
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set username
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set email
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get password hash
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * Set password hash
     */
    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    /**
     * Hash and set password
     */
    public function setPassword(string $password): self
    {
        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    /**
     * Get user roles
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Set user roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Add a role to the user
     */
    public function addRole(string $role): self
    {
        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }
        return $this;
    }

    /**
     * Remove a role from the user
     */
    public function removeRole(string $role): self
    {
        $key = array_search($role, $this->roles);
        if ($key !== false) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles); // Re-index array
        }
        return $this;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * Get user status
     */
    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    /**
     * Set user status
     */
    public function setStatus(UserStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    /**
     * Activate the user
     */
    public function activate(): self
    {
        $this->status = UserStatus::ACTIVE;
        $this->activationToken = null;
        return $this;
    }

    /**
     * Suspend the user
     */
    public function suspend(): self
    {
        $this->status = UserStatus::SUSPENDED;
        return $this;
    }

    /**
     * Ban the user
     */
    public function ban(): self
    {
        $this->status = UserStatus::BANNED;
        return $this;
    }

    /**
     * Delete the user (mark as deleted)
     */
    public function markDeleted(): self
    {
        $this->status = UserStatus::DELETED;
        return $this;
    }

    /**
     * Get activation token
     */
    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }

    /**
     * Set activation token
     */
    public function setActivationToken(?string $activationToken): self
    {
        $this->activationToken = $activationToken;
        return $this;
    }

    /**
     * Get password reset token
     */
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    /**
     * Set password reset token
     */
    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    /**
     * Get reset token expiry
     */
    public function getResetTokenExpiry(): ?string
    {
        return $this->resetTokenExpiry;
    }

    /**
     * Set reset token expiry
     */
    public function setResetTokenExpiry(?string $resetTokenExpiry): self
    {
        $this->resetTokenExpiry = $resetTokenExpiry;
        return $this;
    }

    /**
     * Get created timestamp
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Set created timestamp
     */
    public function setCreatedAt(?string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get updated timestamp
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Set updated timestamp
     */
    public function setUpdatedAt(?string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Create a new user activation token
     */
    public function generateActivationToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->activationToken = $token;
        return $token;
    }

    /**
     * Create a new password reset token
     */
    public function generateResetToken(int $expireMinutes = 60): string
    {
        $token = bin2hex(random_bytes(32));
        $this->resetToken = $token;

        // Set expiry time
        $expiry = new \DateTime();
        $expiry->modify("+{$expireMinutes} minutes");
        $this->resetTokenExpiry = $expiry->format('Y-m-d H:i:s');

        return $token;
    }

    /**
     * Check if reset token is valid
     */
    public function isResetTokenValid(): bool
    {
        if (empty($this->resetToken) || empty($this->resetTokenExpiry)) {
            return false;
        }

        $expiry = new \DateTime($this->resetTokenExpiry);
        $now = new \DateTime();

        return $expiry > $now;
    }

    /**
     * Clear reset token and expiry
     */
    public function clearResetToken(): self
    {
        $this->resetToken = null;
        $this->resetTokenExpiry = null;
        return $this;
    }
}
