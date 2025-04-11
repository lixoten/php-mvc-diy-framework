<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\User;
use App\Enums\UserStatus;

class UserValidationService
{
    public function validateAccountStatus(User $user, UserStatus $requiredStatus): bool
    {
        return $user->getStatus() === $requiredStatus;
    }

    public function isAccountActive(User $user): bool
    {
        return $user->getStatus() === UserStatus::ACTIVE;
    }

    public function validatePasswordConfirmation(string $password, string $confirmPassword): ?string
    {
        if ($password !== $confirmPassword) {
            return 'zzzPasswords do not match.';
        }
        return null;
    }
}
