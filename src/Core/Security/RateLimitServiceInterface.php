<?php

declare(strict_types=1);

namespace Core\Security;

interface RateLimitServiceInterface
{
    /**
     * Check if a request exceeds rate limits
     *
     * @param string $identifier User identifier or IP address
     * @param string $actionType Type of action (login, registration, etc)
     * @param string $ipAddress IP address of requester
     * @throws \Core\Auth\Exception\AuthenticationException if limit exceeded
     */
    public function checkRateLimit(string $identifier, string $actionType, string $ipAddress): void;

    /**
     * Record an attempt for rate limiting
     *
     * @param string $identifier User identifier or IP address
     * @param string $actionType Type of action (login, registration, etc)
     * @param string $ipAddress IP address of requester
     * @param bool $success Whether the attempt was successful
     * @param string|null $userAgent User agent string
     */
    public function recordAttempt(
        string $identifier,
        string $actionType,
        string $ipAddress,
        bool $success = false,
        ?string $userAgent = null
    ): void;

    /**
     * Update status of the last attempt
     *
     * @param string $identifier User identifier or IP address
     * @param string $actionType Type of action (login, registration, etc)
     * @param bool $success Whether the attempt was successful
     */
    public function updateLastAttemptStatus(string $identifier, string $actionType, bool $success): void;
}
