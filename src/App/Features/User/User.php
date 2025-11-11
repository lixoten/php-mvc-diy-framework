<?php

declare(strict_types=1);

namespace App\Features\User;

use App\Enums\UserStatus;

/**
 * Generated File - Date: 20251109_201441
 * Entity class for User.
 *
 * @property-read array<string, mixed> $fields
 */
class User
{
    /**
     * @var int
     */
    private int $id = 0;

    /**
     * @var string
     */
    private string $username = '';

    /**
     * @var string
     */
    private string $email = '';

    /**
     * @var string
     */
    private string $password_hash = '';

    /**
     * @var array<string>
     */
    private array $roles = [];

    /**
     * @var UserStatus
     */
    private UserStatus $status = UserStatus::PENDING;

    /**
     * @var string
     */
    private ?string $activation_token = null;

    /**
     * @var string
     */
    private ?string $reset_token = null;

    /**
     * @var string
     */
    private ?string $reset_token_expiry = null;

    /**
     * @var bool
     */
    private bool $is_green = false;

    /**
     * @var bool
     */
    private bool $is_blue = false;

    /**
     * @var bool
     */
    private bool $is_red = false;

    /**
     * @var string
     */
    private string $generic_code = '';

    /**
     * @var string
     */
    private string $created_at = '';

    /**
     * @var string
     */
    private string $updated_at = '';
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPasswordHash(): string
    {
        return $this->password_hash;
    }

    /**
     * @param string $password_hash
     * @return self
     */
    public function setPasswordHash(string $password_hash): self
    {
        $this->password_hash = $password_hash;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return self
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return UserStatus
     */
    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    /**
     * @param UserStatus $status
     * @return self
     */
    public function setStatus(UserStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getActivationToken(): ?string
    {
        return $this->activation_token;
    }

    /**
     * @param ?string $activation_token
     * @return self
     */
    public function setActivationToken(?string $activation_token): self
    {
        $this->activation_token = $activation_token;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    /**
     * @param ?string $reset_token
     * @return self
     */
    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;
        return $this;
    }

    /**
     * @return ?string
     */
    public function getResetTokenExpiry(): ?string
    {
        return $this->reset_token_expiry;
    }

    /**
     * @param ?string $reset_token_expiry
     * @return self
     */
    public function setResetTokenExpiry(?string $reset_token_expiry): self
    {
        $this->reset_token_expiry = $reset_token_expiry;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsGreen(): bool
    {
        return $this->is_green;
    }

    /**
     * @param bool $is_green
     * @return self
     */
    public function setIsGreen(bool $is_green): self
    {
        $this->is_green = $is_green;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsBlue(): bool
    {
        return $this->is_blue;
    }

    /**
     * @param bool $is_blue
     * @return self
     */
    public function setIsBlue(bool $is_blue): self
    {
        $this->is_blue = $is_blue;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsRed(): bool
    {
        return $this->is_red;
    }

    /**
     * @param bool $is_red
     * @return self
     */
    public function setIsRed(bool $is_red): self
    {
        $this->is_red = $is_red;
        return $this;
    }

    /**
     * @return string
     */
    public function getGenericCode(): string
    {
        return $this->generic_code;
    }

    /**
     * @param string $generic_code
     * @return self
     */
    public function setGenericCode(string $generic_code): self
    {
        $this->generic_code = $generic_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * @param string $created_at
     * @return self
     */
    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    /**
     * @param string $updated_at
     * @return self
     */
    public function setUpdatedAt(string $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }
}
