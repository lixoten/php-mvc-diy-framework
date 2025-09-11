<?php

declare(strict_types=1);

namespace Core\Security;

use App\Repository\RateLimitRepositoryInterface;
use Core\Auth\Exception\AuthenticationException;
use Core\Interfaces\ConfigInterface;

class BruteForceProtectionService
{
    /**
     * Default configuration for different action types
     */
    private array $defaultConfig = [
        'login' => [
            'max_attempts' => 5,
            'ip_max_attempts' => 15,    // Higher threshold for IP-based (3x)
            'lockout_time' => 900       // 15 minutes
        ],
        'password_reset' => [
            'max_attempts' => 3,
            'ip_max_attempts' => 10,
            'lockout_time' => 1800      // 30 minutes
        ],
        'registration' => [
            'max_attempts' => 3,
            'ip_max_attempts' => 10,
            'lockout_time' => 3600      // 60 minutes
        ],
        'activation_resend' => [
            'max_attempts' => 3,
            'ip_max_attempts' => 9,
            'lockout_time' => 3600      // 60 minutes
        ],
        'email_verification' => [
            'max_attempts' => 5,
            'ip_max_attempts' => 15,
            'lockout_time' => 900       // 15 minutes
        ]
    ];

    /**
     * @var array The final configuration
     */
    private array $config;

    /**
     * Constructor
     */
    public function __construct(
        private RateLimitRepositoryInterface $repository,
        private ConfigInterface $configService,
        ?array $customConfig = null
    ) {
        // Try to load config from config service
        $serviceConfig = [];
        try {
            $serviceConfig = $configService->get('security.rate_limits.endpoints', []);
        } catch (\Exception $e) {
            // If config not found, use defaults
        }

        // Merge configs with precedence: defaults < service config < custom passed config
        $this->config = array_replace_recursive(
            $this->defaultConfig,
            $serviceConfig,
            $customConfig ?? []
        );
    }


    /**
     * Check if brute force protection is globally enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? true;
    }


    /**
     * Check if the request is blocked due to too many attempts
     *
     * @throws AuthenticationException if too many attempts
     */
    public function checkRateLimit(string $identifier, string $actionType, string $ipAddress): void
    {
        $this->ensureActionTypeExists($actionType);
        $cutoffTime = time() - $this->config[$actionType]['lockout_time'];

        // Check attempts by identifier
        $attempts = $this->repository->countRecentAttempts(
            $identifier,
            $actionType,
            $cutoffTime
        );

        if ($attempts >= $this->config[$actionType]['max_attempts']) {
            throw new AuthenticationException(
                'Too many attempts. Please try again later.',
                AuthenticationException::TOO_MANY_ATTEMPTS
            );
        }

        // Check attempts from IP address
        $ipAttempts = $this->repository->countRecentAttemptsFromIp(
            $ipAddress,
            $actionType,
            $cutoffTime
        );

        if ($ipAttempts >= $this->config[$actionType]['ip_max_attempts']) {
            throw new AuthenticationException(
                'Too many attempts from your location. Please try again later.',
                AuthenticationException::TOO_MANY_ATTEMPTS
            );
        }
    }

    /**
     * Record an attempt (successful or failed)
     */
    public function recordAttempt(
        string $identifier,
        string $actionType,
        string $ipAddress,
        bool $success = false,
        ?string $userAgent = null
    ): void {
        $this->ensureActionTypeExists($actionType);

        $this->repository->recordAttempt([
            'identifier' => $identifier,
            'action_type' => $actionType,
            'ip_address' => $ipAddress,
            'success' => $success,
            'attempted_at' => date('Y-m-d H:i:s'),
            'user_agent' => $userAgent
        ]);
    }

    /**
     * Reset attempts for a specific identifier and action type
     */
    public function resetAttempts(string $identifier, string $actionType): void
    {
        $this->ensureActionTypeExists($actionType);
        $this->repository->clearForIdentifier($identifier, $actionType);

        // Periodically clean up expired attempts (1/10 chance)
        if (rand(1, 10) === 1) {
            $this->cleanupExpiredAttempts($actionType);
        }
    }

    /**
     * Get remaining attempts before lockout
     */
    public function getRemainingAttempts(string $identifier, string $actionType, string $ipAddress): int
    {
        $this->ensureActionTypeExists($actionType);
        $cutoffTime = time() - $this->config[$actionType]['lockout_time'];

        $attempts = $this->repository->countRecentAttempts(
            $identifier,
            $actionType,
            $cutoffTime
        );

        return max(0, $this->config[$actionType]['max_attempts'] - $attempts);
    }

    /**
     * Is the action currently being rate limited?
     */
    public function isLimited(string $identifier, string $actionType, string $ipAddress): bool
    {
        try {
            $this->checkRateLimit($identifier, $actionType, $ipAddress);
            return false;
        } catch (AuthenticationException $e) {
            return true;
        }
    }

    /**
     * Get remaining lockout time in seconds
     */
    public function getRemainingLockoutTime(string $identifier, string $actionType): int
    {
        $this->ensureActionTypeExists($actionType);

        // Find most recent attempt
        // This would require a new repository method, but showing logic here
        return 0; // Placeholder
    }

    /**
     * Clean up expired attempts for an action type
     */
    private function cleanupExpiredAttempts(string $actionType): void
    {
        // Clean up attempts that are twice the lockout time old
        $cutoffTime = time() - ($this->config[$actionType]['lockout_time'] * 2);
        $this->repository->deleteExpired($cutoffTime);
    }

    /**
     * Ensure the requested action type is configured
     */
    private function ensureActionTypeExists(string $actionType): void
    {
        if (!isset($this->config[$actionType])) {
            throw new \InvalidArgumentException("Unknown rate limit action type: $actionType");
        }
    }

    /**
     * Update status of the last attempt
     */
    public function updateLastAttemptStatus(string $identifier, string $actionType, bool $success): bool
    {
        $this->ensureActionTypeExists($actionType);
        return $this->repository->updateLastAttemptStatus($identifier, $actionType, $success);
    }

    /**
     * Get configuration for a specific action type
     *
     * @param string $actionType
     * @return array|null Action configuration or null if not found
     */
    public function getConfigForActionType(string $actionType): ?array
    {
        $this->ensureActionTypeExists($actionType);
        return $this->config[$actionType] ?? null;
    }

    /**
     * Get the count of recent failed attempts for an identifier
     *
     * @param string $identifier User identifier (email, username, etc.)
     * @param string $actionType The action being checked (login, registration, etc.)
     * @param int $since Unix timestamp representing cutoff time for counting attempts
     * @return int Number of failed attempts since the specified timestamp
     */
    public function getAttemptCount(string $identifier, string $actionType, int $since): int
    {
        // Just pass the integer timestamp, don't convert it
        return $this->repository->countRecentAttempts(
            $identifier,
            $actionType,
            $since
        );
    }

    /**
     * Determine if CAPTCHA should be required for this identifier and action
     *
     * @param string $actionType
     * @param string $identifier
     * @return bool
     */
    public function isCaptchaRequired(string $actionType, string $identifier): bool
    {
        // First check if CAPTCHA is globally enabled via the service
        if (!$this->captchaService->isEnabled()) {
            return false;
        }

        $threshold = $this->config[$actionType]['captcha_threshold'] ?? 0;

        if ($threshold <= 0) {
            return false;
        }

        $cutoffTime = time() - $this->config[$actionType]['lockout_time'];
            $attempts = $this->repository->countRecentAttempts(
                $identifier,
                $actionType,
                $cutoffTime // Just pass the integer timestamp
            );

        return $attempts >= $threshold;
    }
}
