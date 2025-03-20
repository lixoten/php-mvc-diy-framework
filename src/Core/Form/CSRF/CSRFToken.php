<?php

// filepath: d:\xampp\htdocs\mvclixo\src\Core\Form\CSRF\CSRFToken.php

declare(strict_types=1);

namespace Core\Form\CSRF;

use Core\Session\SessionManagerInterface;

class CSRFToken
{
    protected SessionManagerInterface $session;
    protected string $tokenName;
    protected int $tokenExpiration;

    /**
     * Create a new CSRF token manager
     *
     * @param SessionManagerInterface $session The session manager
     * @param string $tokenName The name of the token in session
     * @param int $tokenExpiration Token lifetime in seconds
     */
    public function __construct(
        SessionManagerInterface $session,
        string $tokenName = 'csrf_token',
        int $tokenExpiration = 3600 // 1 hour
    ) {
        $this->session = $session;
        $this->tokenName = $tokenName;
        $this->tokenExpiration = $tokenExpiration;
    }

    /**
     * Generate a new CSRF token and store in session
     *
     * @return string The generated token
     */
    public function generate(): string
    {
        // Generate a random token
        $token = bin2hex(random_bytes(32));

        // Store token and expiration in session
        $this->session->set($this->tokenName, [
            'token' => $token,
            'expires' => time() + $this->tokenExpiration
        ]);

        return $token;
    }

    /**
     * Validate a submitted token against the stored one
     *
     * @param string $token The token to validate
     * @return bool True if the token is valid
     */
    public function validate(string $token): bool
    {
        // Get the stored token data
        $storedData = $this->session->get($this->tokenName);

        // If no token exists or is expired, validation fails
        if (!$storedData || !isset($storedData['token']) || !isset($storedData['expires'])) {
            return false;
        }

        // Check if token has expired
        if ($storedData['expires'] < time()) {
            // Remove expired token
            $this->session->remove($this->tokenName);
            return false;
        }

        // Compare the tokens using a constant-time comparison to prevent timing attacks
        return hash_equals($storedData['token'], $token);
    }

    /**
     * Get the current token if exists, or generate a new one
     *
     * @return string The current or new token
     */
    public function getToken(): string
    {
        $storedData = $this->session->get($this->tokenName);

        // If token exists and is not expired, return it
        if (
            $storedData &&
            isset($storedData['token']) &&
            isset($storedData['expires']) &&
            $storedData['expires'] > time()
        ) {
            return $storedData['token'];
        }

        // Otherwise generate a new one
        return $this->generate();
    }

    /**
     * Create a hidden form field with the CSRF token
     *
     * @return string HTML for the hidden form field
     */
    public function getTokenField(): string
    {
        $token = $this->getToken();
        return '<input type="hidden" name="' . $this->tokenName . '" value="' . $token . '">';
    }
}
