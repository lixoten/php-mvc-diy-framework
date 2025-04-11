<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\User;
use App\Repository\UserRepositoryInterface;
use Core\Security\TokenServiceInterface;
use App\Enums\UserStatus;

class UserService
{
    private UserRepositoryInterface $userRepository;
    private TokenServiceInterface $tokenService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenServiceInterface $tokenService
    ) {
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPasswordHash(password_hash($data['password'], PASSWORD_DEFAULT));
        $user->setRoles(['user']);
        $user->setStatus(UserStatus::PENDING);

        // Generate activation token
        $tokenData = $this->tokenService->generateWithExpiry(24 * 3600); // 24 hours
        $user->setActivationToken($tokenData['token']);
        $user->setActivationTokenExpiry($tokenData['expires_at']);

        // Save user to repository
        return $this->userRepository->create($user);
    }

    /**
     * Fetch a user by ID
     */
    public function getUserById(int $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }

    /**
     * Fetch a user by username
     */
    public function getUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    /**
     * Fetch a user by email
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Fetch a user by activation token
     */
    public function getUserByActivationToken(string $token): ?User
    {
        return $this->userRepository->findByActivationToken($token);
    }

    /**
     * Fetch a user by reset token
     */
    public function getUserByResetToken(string $token): ?User
    {
        return $this->userRepository->findByResetToken($token);
    }

    /**
     * Update an existing user
     */
    public function updateUser(User $user): bool
    {
        return $this->userRepository->update($user);
    }

    /**
     * Delete a user by ID
     */
    public function deleteUser(int $userId): bool
    {
        return $this->userRepository->delete($userId);
    }
}
