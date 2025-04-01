<?php

declare(strict_types=1);

namespace Core\Auth;

use App\Entities\User;
use App\Enums\UserStatus;
use App\Repository\UserRepositoryInterface;
use Core\Auth\Exception\AuthenticationException;
use Core\Session\SessionManagerInterface;

class SessionAuthenticationService implements AuthenticationServiceInterface
{
    /**
     * Session keys
     */
    private const SESSION_USER_ID = 'auth_user_id';
    private const SESSION_USER_ROLES = 'auth_user_roles';
    private const SESSION_AUTH_TIME = 'auth_time';
    private const SESSION_LAST_ACTIVITY = 'auth_last_activity';

    /**
     * Cookie settings
     */
    private const REMEMBER_COOKIE_NAME = 'remember_token';
    private const REMEMBER_COOKIE_EXPIRY = 2592000; // 30 days in seconds

    /**
     * @var UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepository;

    /**
     * @var SessionManagerInterface
     */
    private SessionManagerInterface $session;

    /**
     * @var User|null
     */
    private ?User $currentUser = null;

    /**
     * @var array
     */
    private array $config;

    /**
     * Constructor
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        SessionManagerInterface $session,
        array $config = []
    ) {
        $this->userRepository = $userRepository;
        $this->session = $session;
        $this->config = array_merge([
            'session_lifetime' => 7200, // 2 hours
            'max_attempts' => 5, // Maximum login attempts
            'lockout_time' => 900, // 15 minutes
            'secure_cookie' => false, // Set to true in production
            'cookie_path' => '/',
            'cookie_domain' => '',
        ], $config);

        // Start session if not already started
        $this->session->start();

        // Auto-login from remember me cookie if available
        $this->attemptRememberMeLogin();
    }

    /**
     * {@inheritdoc}
     */
    public function login(string $usernameOrEmail, string $password, bool $remember = false): bool
    {
        // Check for too many failed attempts
        $this->checkForBruteForce($usernameOrEmail);

        // Try to find user by username or email
        $user = null;
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepository->findByEmail($usernameOrEmail);
        } else {
            $user = $this->userRepository->findByUsername($usernameOrEmail);
        }

        // Verify user exists and password is correct
        if (!$user || !$user->verifyPassword($password)) {
            $this->recordFailedLogin($usernameOrEmail);
            throw new AuthenticationException(
                'Invalid username/email or password',
                AuthenticationException::INVALID_CREDENTIALS
            );
        }

        // Check if account is active
        if ($user->getStatus() !== UserStatus::ACTIVE) {
            throw new AuthenticationException(
                'Account is not active',
                $user->getStatus() === UserStatus::SUSPENDED ?
                    AuthenticationException::ACCOUNT_LOCKED :
                    AuthenticationException::ACCOUNT_INACTIVE
            );
        }

        // Authentication successful, regenerate session ID for security
        $this->session->regenerateId(true);

        // Store user data in session
        $this->storeUserInSession($user);

        // Set remember me cookie if requested
        if ($remember) {
            $this->setRememberMeCookie($user);
        }

        // Reset failed login attempts
        $this->resetFailedLogins($usernameOrEmail);

        // Store user for later retrieval
        $this->currentUser = $user;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function logout(): void
    {
        // Clear session data
        $this->session->remove(self::SESSION_USER_ID);
        $this->session->remove(self::SESSION_USER_ROLES);
        $this->session->remove(self::SESSION_AUTH_TIME);
        $this->session->remove(self::SESSION_LAST_ACTIVITY);

        // Clear remember me cookie if present
        $this->clearRememberMeCookie();

        // Regenerate session ID
        $this->session->regenerateId(true);

        // Reset current user
        $this->currentUser = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUser(): ?User
    {
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }

        // Check if user is logged in via session
        if (!$this->isAuthenticated()) {
            return null;
        }

        // Load from repository
        $userId = (int) $this->session->get(self::SESSION_USER_ID);
        $this->currentUser = $this->userRepository->findById($userId);

        // If user no longer exists or is no longer active, logout
        if (!$this->currentUser || !$this->currentUser->isActive()) {
            $this->logout();
            return null;
        }

        return $this->currentUser;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthenticated(): bool
    {
        // Check if we have user ID in session
        if (!$this->session->has(self::SESSION_USER_ID)) {
            return false;
        }

        // Validate session is still valid (not expired)
        if (!$this->validateSession()) {
            $this->logout();
            return false;
        }

        // Update last activity time
        $this->updateLastActivity();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole(string $role): bool
    {
        if (!$this->isAuthenticated()) {
            return false;
        }

        // Get roles from session
        $roles = $this->session->get(self::SESSION_USER_ROLES, []);

        return in_array($role, $roles);
    }

    /**
     * {@inheritdoc}
     */
    public function renewSession(): void
    {
        if ($this->isAuthenticated()) {
            $this->session->set(self::SESSION_AUTH_TIME, time());
            $this->updateLastActivity();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLastAuthTime(): ?int
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $this->session->get(self::SESSION_AUTH_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function validateSession(): bool
    {
        // Check if session has timed out
        $lastActivity = $this->session->get(self::SESSION_LAST_ACTIVITY, 0);
        $timeSinceActivity = time() - $lastActivity;

        return $timeSinceActivity < $this->config['session_lifetime'];
    }

    /**
     * Update the last activity timestamp
     */
    private function updateLastActivity(): void
    {
        $this->session->set(self::SESSION_LAST_ACTIVITY, time());
    }

    /**
     * Store user information in session
     */
    private function storeUserInSession(User $user): void
    {
        $this->session->set(self::SESSION_USER_ID, $user->getUserId());
        $this->session->set(self::SESSION_USER_ROLES, $user->getRoles());
        $this->session->set(self::SESSION_AUTH_TIME, time());
        $this->session->set(self::SESSION_LAST_ACTIVITY, time());
    }

    /**
     * Generate and set a remember me cookie
     */
    private function setRememberMeCookie(User $user): void
    {
        // Generate a unique token
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));

        // Hash the validator for storage
        $hashedValidator = hash('sha256', $validator);

        // Store token in database
        // Note: In a real implementation, you would store this in the database
        // and associate it with the user. This is just a placeholder.
        // $this->rememberTokenRepository->create($user->getUserId(), $selector, $hashedValidator);

        // Set cookie with selector:validator
        $token = $selector . ':' . $validator;
        $expires = time() + self::REMEMBER_COOKIE_EXPIRY;

        // Set secure cookie
        setcookie(
            self::REMEMBER_COOKIE_NAME,
            $token,
            [
                'expires' => $expires,
                'path' => $this->config['cookie_path'],
                'domain' => $this->config['cookie_domain'],
                'secure' => $this->config['secure_cookie'],
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    /**
     * Clear the remember me cookie
     */
    private function clearRememberMeCookie(): void
    {
        // Remove from browser
        setcookie(
            self::REMEMBER_COOKIE_NAME,
            '',
            [
                'expires' => 1, // Expired
                'path' => $this->config['cookie_path'],
                'domain' => $this->config['cookie_domain'],
                'secure' => $this->config['secure_cookie'],
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    /**
     * Attempt to login via remember me cookie
     */
    private function attemptRememberMeLogin(): void
    {
        // Skip if already authenticated
        if ($this->isAuthenticated()) {
            return;
        }

        // Check if remember cookie exists
        if (!isset($_COOKIE[self::REMEMBER_COOKIE_NAME])) {
            return;
        }

        $cookie = $_COOKIE[self::REMEMBER_COOKIE_NAME];
        list($selector, $validator) = explode(':', $cookie);

        // TODO: Implement the actual remember me token validation
        // This would involve looking up the token in the database
        // and validating it. For now, this is just a placeholder.

        // If implemented, this could look something like:
        // $token = $this->rememberTokenRepository->findBySelector($selector);
        // if ($token && hash_equals(hash('sha256', $validator), $token->getHashedValidator())) {
        //     $user = $this->userRepository->findById($token->getUserId());
        //     if ($user && $user->isActive()) {
        //         $this->storeUserInSession($user);
        //     }
        // }

        // Always clear the cookie to prevent replay attacks
        $this->clearRememberMeCookie();
    }

    /**
     * Check for too many failed login attempts
     */
    private function checkForBruteForce(string $usernameOrEmail): void
    {
        // TODO: Implement brute force protection
        // This would involve tracking failed login attempts in the database
        // or cache and blocking if too many attempts are made.

        // For now, this is just a placeholder.
        // $attempts = $this->loginAttemptsRepository->countRecentAttempts(
        //     $usernameOrEmail,
        //     time() - $this->config['lockout_time']
        // );
        // // if ($attempts >= $this->config['max_attempts']) {
        //     throw new AuthenticationException(
        //         'Too many failed login attempts. Please try again later.',
        //         AuthenticationException::TOO_MANY_ATTEMPTS
        //     );
        // }
    }

    /**
     * Record a failed login attempt
     */
    private function recordFailedLogin(string $usernameOrEmail): void
    {
        // TODO: Implement failed login tracking
        // $this->loginAttemptsRepository->record([
        //     'username_or_email' => $usernameOrEmail,
        //     'ip_address' => $_SERVER['REMOTE_ADDR'],
        //     'attempted_at' => time()
        // ]);
    }


    /**
     * Reset failed login attempts
     */
    private function resetFailedLogins(string $usernameOrEmail): void
    {
        // TODO: Implement clearing of failed login tracking
        // TODO: Implement clearing of failed login tracking
        // $this->loginAttemptsRepository->clearForUser($usernameOrEmail);
    }
}
