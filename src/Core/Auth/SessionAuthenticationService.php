<?php

declare(strict_types=1);

namespace Core\Auth;

// use App\Entities\User;
// use App\Repository\StoreRepositoryInterface;
use App\Features\User\UserRepositoryInterface;
// use App\Repository\UserRepositoryInterface;
// use App\Features\User\User;
// use App\Features\Store\StoreRepositoryInterface;
// use App\Features\Store\UserRepositoryInterface;
//////////----------------
use App\Enums\UserStatus;
use App\Features\User\User;
use App\Repository\RememberTokenRepository;
use App\Features\Store\StoreRepositoryInterface;
use Core\Auth\Exception\AuthenticationException;
use Core\Interfaces\ConfigInterface;
// use Core\Security\BruteForceProtectionService; // foofee
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
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * Constructor
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        SessionManagerInterface $session,
        protected RememberTokenRepository $rememberTokenRepository,
        ConfigInterface $config,
        protected ?StoreRepositoryInterface $storeRepository = null
        // protected BruteForceProtectionService $bruteForceProtection, // foofee
    ) {
        $this->userRepository = $userRepository;
        $this->session = $session;
        $this->config = $config;

        //$defaultLifetime = $this->config->get('session.lifetime', 120); // minutes


        // $defaultLifetime = 7200; // 2 hours
        // if (isset($config['session']['lifetime'])) {
        //     // Support nested config as in config/app.php
        //     $defaultLifetime = (int)$config['session']['lifetime'] * 60; // convert minutes to seconds
        // }
        // $this->config = array_merge([
        //     'session_lifetime' => $defaultLifetime,
        //     // Fik - override debugger hack so sessions will not expire
        //     // 'session_lifetime' => 86400, // 24 hours // hack
        //     'secure_cookie' => false, // Set to true in production
        //     'cookie_path' => '/',
        //     'cookie_domain' => '',
        // ], $config);

        // Start session if not already started
        $this->session->start();

        // Auto-login test user in development
        // if ($_ENV['APP_ENV'] === 'development' && empty($this->session->get(self::SESSION_USER_ID))) {
        if ($_ENV['APP_ENV'] === 'development') {
            // Replace 1 with your test user ID
            // FikHack - FAKE LOGIN with fakeUserId // findme bypass login // findme bypass security
            $fakeUserId = 2; // hack
            $testUser = $this->userRepository->findById($fakeUserId);
            if ($testUser) {
                $this->storeUserInSession($testUser);
                $this->setupStoreContext($testUser);
                $this->currentUser = $testUser;
            }
        } elseif ($_ENV['APP_ENV'] === 'production') {
            // Replace 1 with your test user ID
            // FikHack - FAKE LOGIN with fakeUserId
            $fakeUserId = 2; // hack
            $testUser = $this->userRepository->findById($fakeUserId);
            if ($testUser) {
                $this->storeUserInSession($testUser);
                $this->setupStoreContext($testUser);
                $this->currentUser = $testUser;
            }
        }

        // Auto-login from remember me cookie if available
        $this->attemptRememberMeLogin();
    }

    /**
     * {@inheritdoc}
     */
    public function login(string $usernameOrEmail, string $password, bool $remember = false): bool
    {
        // Try to find user by username or email
        $user = null;
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepository->findByEmail($usernameOrEmail);
        } else {
            $user = $this->userRepository->findByUsername($usernameOrEmail);
        }

        // Verify user exists and password is correct
        if (!$user || !$user->verifyPassword($password)) {
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

        // Set up store context for store owners
        $this->setupStoreContext($user);

        // Set remember me cookie if requested
        if ($remember) {
            $this->setRememberMeCookie($user);
        }

        // Store user for later retrieval
        $this->currentUser = $user;

        return true;
    }

    /**
     * Override logout method to clear remember me tokens
     */
    public function logout(): void
    {
        // Get user ID before clearing session
        $userId = null;
        if ($this->isAuthenticated()) {
            $user = $this->getCurrentUser();
            if ($user) {
                $userId = $user->getId();
            }
        }

        // Clear session data
        $this->session->remove(self::SESSION_USER_ID);
        $this->session->remove(self::SESSION_USER_ROLES);
        $this->session->remove(self::SESSION_AUTH_TIME);
        $this->session->remove(self::SESSION_LAST_ACTIVITY);

        // Clear store context
        $this->session->remove('active_store_id');
        $this->session->remove('active_store_slug');
        $this->session->remove('active_store_name');

        // Clear remember me cookie and any stored tokens for this user
        $this->clearRememberMeCookie();
        if ($userId) {
            $this->rememberTokenRepository->deleteByUserId($userId);
        }

        // Regenerate session ID
        $this->session->regenerateId(true);

        // Reset current user
        $this->currentUser = null;
    }

    /**
     * Set up store context for store owners
     */
    private function setupStoreContext(User $user): void
    {
        // Check if user has store_owner role
        if (in_array('store_owner', $user->getRoles())) {
            // Skip if no store repository was injected
            if (!$this->storeRepository) {
                return;
            }

            // Get user's store from repository
            $store = $this->storeRepository->findByUserId($user->getId());

            if ($store) {
                // Store the active store information in session //fixme //dangerdanger bi no no... can a user have multi-store?
                $this->session->set('active_store_name', $store[0]->getName());
                $this->session->set('active_store_slug', $store[0]->getSlug());
                $this->session->set('active_store_id', $store[0]->getId());
            }
        }
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

        // $sessionLifetime = (int)$this->config->get('session.lifetime', 120) * 60;
        $sessionLifetime = (int)$this->config->get('app.session.lifetime', 120) * 60;
        // $sessionLifetime = (int)$this->config->getConfigValue('app', 'session.lifetime', 120) * 60;
        return $timeSinceActivity < $sessionLifetime;
        //return $timeSinceActivity < $this->config['session_lifetime'];
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
        $this->session->set(self::SESSION_USER_ID, $user->getId());
        $this->session->set(self::SESSION_USER_ROLES, $user->getRoles());
        $this->session->set(self::SESSION_AUTH_TIME, time());
        $this->session->set(self::SESSION_LAST_ACTIVITY, time());
    }

    /**
     * Set a remember me cookie
     */
    private function setRememberMeCookie(User $user): void
    {
        // Generate a unique token
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));

        // Hash the validator for storage
        $hashedValidator = hash('sha256', $validator);

        // Set cookie with selector:validator
        $token = $selector . ':' . $validator;
        $expires = time() + self::REMEMBER_COOKIE_EXPIRY;

        // Set secure cookie
        setcookie(
            self::REMEMBER_COOKIE_NAME,
            $token,
            [
                'expires' => $expires,
                // 'path' => $this->config['cookie_path'],
                // 'domain' => $this->config['cookie_domain'],
                // 'secure' => $this->config['secure_cookie'],
                'path' => $this->config->get('app.session.cookie_path', '/'),
                'domain' => $this->config->get('app.session.cookie_domain', ''),
                'secure' => $this->config->get('app.session.secure_cookie', false),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );

        // Store token in database with expiration date
        $expiresAt = date('Y-m-d H:i:s', $expires);
        $this->rememberTokenRepository->create(
            $user->getId(),
            $selector,
            $hashedValidator,
            $expiresAt
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
                // 'path' => $this->config['cookie_path'],
                // 'domain' => $this->config['cookie_domain'],
                // 'secure' => $this->config['secure_cookie'],
                'path' => $this->config->get('app.session.cookie_path', '/'),
                'domain' => $this->config->get('app.session.cookie_domain', ''),
                'secure' => $this->config->get('app.session.secure_cookie', false),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }


    /**
     * Attempt login using remember me cookie
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

        // Cookie should contain 'selector:validator'
        $parts = explode(':', $cookie);
        if (count($parts) !== 2) {
            $this->clearRememberMeCookie();
            return;
        }

        [$selector, $validator] = $parts;

        // Find token by selector
        $token = $this->rememberTokenRepository->findBySelector($selector);

        // If no token found or token is expired, clear cookie and return
        if (!$token || $token->isExpired()) {
            $this->clearRememberMeCookie();
            if ($token) {
                $this->rememberTokenRepository->deleteBySelector($selector);
            }
            return;
        }

        // Verify the validator
        if (!hash_equals($token->getHashedValidator(), hash('sha256', $validator))) {
            // Invalid token, possible attack
            $this->clearRememberMeCookie();
            $this->rememberTokenRepository->deleteByUserId($token->getUserId());
            return;
        }

        // Token is valid, load the user
        $user = $this->userRepository->findById($token->getUserId());

        if (!$user || !$user->isActive()) {
            $this->clearRememberMeCookie();
            if ($token) {
                $this->rememberTokenRepository->deleteBySelector($selector);
            }
            return;
        }

        // Delete the used token (single use token)
        $this->rememberTokenRepository->deleteBySelector($selector);

        // Create a new token for future use (token rotation)
        $this->setRememberMeCookie($user);

        // Set user session
        $this->storeUserInSession($user);

        // Set up store context for remember-me login too
        $this->setupStoreContext($user);

        $this->currentUser = $user;

        // Delete expired tokens periodically (1/100 chance to run this cleanup)
        if (rand(1, 100) === 1) {
            $this->rememberTokenRepository->deleteExpired();
        }
    }
}
