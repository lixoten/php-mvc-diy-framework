<?php

declare(strict_types=1);

namespace Core\Security;

use App\Repository\RateLimitRepositoryInterface;
use Core\Auth\Exception\AuthenticationException;
use Core\Interfaces\ConfigInterface;

class RateLimitService implements RateLimitServiceInterface
{
    /**
     * Default configuration for different action types
     */
    private array $defaultConfig = [
        'login' => ['limit' => 5, 'window' => 300],          // 5 attempts per 5 minutes
        'registration' => ['limit' => 3, 'window' => 1800],   // 3 attempts per 30 minutes
        'password_reset' => ['limit' => 3, 'window' => 900],  // 3 attempts per 15 minutes
        'email_verification' => ['limit' => 5, 'window' => 900], // 5 attempts per 15 minutes
        'activation_resend' => ['limit' => 3, 'window' => 1800], // 3 attempts per 30 minutes
    ];

    /**
     * @var array The final configuration
     */
    private array $config;

    /**
     * Constructor
     *
     * @param RateLimitRepositoryInterface $repository Repository for storing rate limit data
     * @param ConfigInterface $configService Configuration service
     * @param array|null $customConfig Optional custom configuration
     */
    public function __construct(
        private RateLimitRepositoryInterface $repository,
        private ConfigInterface $configService,
        ?array $customConfig = null
    ) {
        // Try to load config from config service
        $serviceConfig = [];
        try {
            $serviceConfig = $configService->get('security.rate_limits', []);
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
     * {@inheritdoc}
     */
    public function checkRateLimit(string $identifier, string $actionType, string $ipAddress): void
    {
        if (!isset($this->config[$actionType])) {
            return; // No limit defined for this action
        }

        // Periodically clean up old records (1% chance to avoid performance impact)
        if (mt_rand(1, 100) === 1) {
            $this->cleanupExpiredAttempts($actionType);
        }


        $window = $this->config[$actionType]['window'];
        $limit = $this->config[$actionType]['limit'];

        // Calculate the timestamp for "window" seconds ago
        $since = time() - $window;

        // Get attempts from repository
        $userAttempts = $this->repository->countRecentAttempts($identifier, $actionType, $since);
        $ipAttempts = $this->repository->countRecentAttemptsFromIp($ipAddress, $actionType, $since);

        // Check limits
        if ($userAttempts >= $limit) {
            throw new AuthenticationException(
                'Too many attempts. Please try again later.',
                AuthenticationException::TOO_MANY_ATTEMPTS ?? 0
            );
        }

        // IP-based limit is 3x the regular limit
        if ($ipAttempts >= ($limit * 3)) {
            throw new AuthenticationException(
                'Too many attempts from your location. Please try again later.',
                AuthenticationException::TOO_MANY_ATTEMPTS ?? 0
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function recordAttempt(
        string $identifier,
        string $actionType,
        string $ipAddress,
        bool $success = false,
        ?string $userAgent = null
    ): void {
        if (!isset($this->config[$actionType])) {
            return; // No limit defined for this action
        }

        // Format data for repository
        $data = [
            'identifier' => $identifier,
            'action_type' => $actionType,
            'ip_address' => $ipAddress,
            'success' => $success ? 1 : 0,
            'attempted_at' => date('Y-m-d H:i:s'),
            'user_agent' => $userAgent ?? ''
        ];

        // Record ALL attempts regardless of success/failure
        $this->repository->recordAttempt($data);

        // If identifier is not the IP address, also record an IP-based attempt
        if ($identifier !== $ipAddress) {
            $ipData = [
                'identifier' => $ipAddress,
                'action_type' => $actionType,
                'ip_address' => $ipAddress,
                'success' => $success ? 1 : 0,
                'attempted_at' => $data['attempted_at'],
                'user_agent' => $userAgent ?? ''
            ];
            $this->repository->recordAttempt($ipData);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateLastAttemptStatus(string $identifier, string $actionType, bool $success): void
    {
        if (!isset($this->config[$actionType])) {
            return;
        }

        // Update the status in the repository
        $this->repository->updateLastAttemptStatus($identifier, $actionType, $success);

        // Also update IP-based attempt if needed
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($identifier !== $ipAddress) {
            $this->repository->updateLastAttemptStatus($ipAddress, $actionType, $success);
        }
    }


    /**
     * Cleanup old rate limit attempt records
     *
     * @param string|null $actionType Optional specific action type to clean
     */
    private function cleanupExpiredAttempts(?string $actionType = null): void
    {
        // If specific action type provided, only clean that one
        if ($actionType !== null && isset($this->config[$actionType])) {
            $cutoffTime = time() - ($this->config[$actionType]['window'] * 2);
            $this->repository->deleteExpired($cutoffTime, $actionType);
            return;
        }

        // Otherwise clean all configured action types
        foreach ($this->config as $type => $settings) {
            $cutoffTime = time() - ($settings['window'] * 2);
            $this->repository->deleteExpired($cutoffTime, $type);
        }
    }
}
