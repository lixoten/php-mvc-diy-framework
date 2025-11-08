<?php

declare(strict_types=1);

namespace Core\Session;

/**
 * Manages session operations with enhanced security features
 */
class SessionManager implements SessionManagerInterface
{
    /**
     * Whether the session has been started
     */
    private bool $started = false;

    /**
     * Session configuration options
     */
    private array $options;

    /**
     * Create a new session manager with optional configuration
     *
     * @param array $options Session configuration options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'name' => 'mvc3_session',
            'lifetime' => 7202,            // 2 hours in seconds
            'path' => '/',
            'domain' => '',
            'secure' => false,             // Set to true in production with HTTPS
            'httponly' => true,
            'samesite' => 'Lax'           // None, Lax, or Strict
        ], $options);

        // // Set PHP session settings based on options
        // if ($this->options['name']) {
        //     session_name($this->options['name']);
        // }

        //$this->configureCookieParams();
    }

    /**
     * Start the session if not already started
     *
     * @return bool True if session started successfully
     */
    public function start(): bool
    {
        if ($this->started) {
            return true;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return true;
        }

        // Set PHP session settings based on options
        if ($this->options['name']) {
            session_name($this->options['name']);
        }

        // Configure cookie parameters right before starting the session
        $this->configureCookieParams();

        $this->started = session_start();

        // Security: regenerate ID periodically
        if (
            $this->started &&
            (!isset($_SESSION['_last_regenerated']) || $_SESSION['_last_regenerated'] < time() - 300)
        ) {
            $this->regenerateId();
        }

        return $this->started;
    }

    /**
     * Get data from the session
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The session data or default value
     */
    public function get(string $key, $default = null)
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set data in the session
     *
     * @param string $key The key to set
     * @param mixed $value The value to store
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if a key exists in the session
     *
     * @param string $key The key to check
     * @return bool True if the key exists
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove data from the session
     *
     * @param string $key The key to remove
     * @return void
     */
    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Clear all session data
     *
     * @return void
     */
    public function clear(): void
    {
        $this->ensureStarted();
        $_SESSION = [];
    }

    /**
     * Regenerate the session ID
     *
     * @param bool $deleteOldSession Whether to delete the old session data
     * @return bool True if successful
     */
    public function regenerateId(bool $deleteOldSession = true): bool
    {
        $this->ensureStarted();
        $result = session_regenerate_id($deleteOldSession);

        if ($result) {
            $_SESSION['_last_regenerated'] = time();
        }

        return $result;
    }

    /**
     * Destroy the session completely
     *
     * @return bool True if successful
     */
    public function destroy(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // Clear session data first
            $_SESSION = [];

            // Clear the cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

            // Destroy the session
            $result = session_destroy();
            $this->started = false;
            return $result;
        }
        return false;
    }

    /**
     * Configure session cookie parameters
     *
     * @return void
     */
    private function configureCookieParams(): void
    {
        session_set_cookie_params([
            'lifetime' => $this->options['lifetime'],
            'path' => $this->options['path'],
            'domain' => $this->options['domain'],
            'secure' => $this->options['secure'],
            'httponly' => $this->options['httponly'],
            'samesite' => $this->options['samesite']
        ]);
    }

    /**
     * Ensure the session is started
     *
     * @return void
     */
    private function ensureStarted(): void
    {
        if (!$this->started) {
            $this->start();
        }
    }

    /**
     * Get all session data
     *
     * @return array All session data
     */
    public function all(): array
    {
        // Make sure session is started
        $this->start();

        // Return all session data
        return $_SESSION;
    }
}
