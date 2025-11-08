<?php

declare(strict_types=1);

namespace App\Features\User;

use App\Enums\UserStatus;

/**
 * Generated File - Date: 20251102_232227
 * Entity class for User.
 *
 * @property-read array<string, mixed> $fields
 */
class User
{
    /**
     * @var int
     */
    private int $id;

    /**
     * @var string
     */
    private string $username;

    /**
     * @var string
     */
    private string $email;

    /**
     * @var string
     */
    private string $password_hash;

    private array $roles = [];
    private array $my_colors = [];

    /**
     * @var string
     */
    private UserStatus $status = UserStatus::PENDING;

    private string $generic_code;
    private bool $is_green;
    private bool $is_blue;
    private bool $is_red;




    /**
     * @var string|null
     */
    private ?string $activation_token = null;

    /**
     * @var string|null
     */
    private ?string $reset_token = null;

    /**
     * @var string|null
     */
    private ?string $reset_token_expiry = null;

    /**
     * @var string
     */
    private string $created_at;

    /**
     * @var string
     */
    private string $updated_at;
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



    public function getIsGreen(): bool
    {
        return $this->is_green;
    }
    public function setIsGreen(int|bool $is_green): self
    {
        $this->is_green = (bool)$is_green;
        return $this;
    }

    public function getIsBlue(): bool
    {
        return $this->is_blue;
    }
    public function setIsBlue(int|bool $is_blue): self
    {
        $this->is_blue = (bool)$is_blue;
        return $this;
    }

    public function getIsRed(): bool
    {
        return $this->is_red;
    }
    public function setIsRed(int|bool $is_red): self
    {
        $this->is_red = (bool)$is_red;
        return $this;
    }

    /**
     * @return array
     */
    public function getMyColors(): array
    {
        return $this->my_colors;
    }

    /**
     * @param array $my_colors
     * @return self
     */
    public function setMyColors(array $my_colors): self
    {
        $this->my_colors = $my_colors;
        return $this;
    }


    /**
     * @return array
     */
    public function getGenericCode(): string
    {
        return $this->generic_code;
    }

    /**
     * @param string $generic_code
     * @return self
     */
    public function setGenericCodes(string $generic_code): self
    {
        $this->generic_code = $generic_code;
        return $this;
    }

}
