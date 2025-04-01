<?php

declare(strict_types=1);

namespace Core\Auth;

use App\Entities\User;
use Core\Auth\Exception\AuthenticationException;

interface AuthenticationServiceInterface
{
    /**
     * Authenticate a user with username/email and password
     *
     * @param string $usernameOrEmail Username or email
     * @param string $password Plain text password
     * @param bool $remember Whether to remember the login
     * @return bool True if login is successful
     * @throws AuthenticationException If authentication fails
     */
    public function login(string $usernameOrEmail, string $password, bool $remember = false): bool;

    /**
     * Log the current user out
     */
    public function logout(): void;

    /**
     * Get the currently authenticated user
     *
     * @return User|null The authenticated user or null if not authenticated
     */
    public function getCurrentUser(): ?User;

    /**
     * Check if a user is currently authenticated
     *
     * @return bool True if a user is authenticated
     */
    public function isAuthenticated(): bool;

    /**
     * Check if the authenticated user has a specific role
     *
     * @param string $role The role to check for
     * @return bool True if user has the role, false if not or if not authenticated
     */
    public function hasRole(string $role): bool;

    /**
     * Renew the current authentication session
     *
     * Useful for extending session lifetime on activity
     */
    public function renewSession(): void;

    /**
     * Get the last authentication time
     *
     * @return int|null Timestamp of last authentication or null if not authenticated
     */
    public function getLastAuthTime(): ?int;

    /**
     * Verify if the current session is valid
     *
     * @return bool True if the session is valid
     */
    public function validateSession(): bool;
}
