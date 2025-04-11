<?php

declare(strict_types=1);

namespace Core\Security;

/**
 * Interface for token generation
 */
interface TokenServiceInterface
{
    /**
     * Generate a new random token
     *
     * @param int $length Length of the token in bytes (before encoding)
     * @param bool $withExpiry Whether to include expiry information
     * @param int $expiresIn Expiration time in seconds if $withExpiry is true
     * @return array|string Token string or array with token and expiry
     */
    public function generate(int $length = 32, bool $withExpiry = false, int $expiresIn = 3600): array|string;

    /**
     * Generate a URL-safe token (base64 encoded)
     *
     * @param int $length Length of the token in bytes
     * @return string The generated token
     */
    public function generateUrlSafe(int $length = 32): string;

    /**
     * Create a token with expiration information
     *
     * @param int $length Length of the token in bytes
     * @param int $expiresIn Expiration time in seconds
     * @return array ['token' => string, 'expires_at' => int]
     */
    public function generateWithExpiry(int $length = 32, int $expiresIn = 3600): array;

    /**
     * Check if a timestamp has expired
     *
     * @param int $expiryTime Unix timestamp to check
     * @return bool True if timestamp has expired
     */
    public function hasExpired(int $expiryTime): bool;


    /**
     * Generate a signed token using HMAC
     *
     * @param string $data Data to include in the token (e.g. user ID)
     * @param string|null $key Optional override for secret key
     * @return string The signed token
     */
    public function generateSigned(string $data, ?string $key = null): string;

    /**
     * Verify a signed token
     *
     * @param string $token The token to verify
     * @param string|null $key Optional override for secret key
     * @param int $maxAge Maximum age in seconds (0 for no limit)
     * @return array|false ['data' => string, 'timestamp' => int] or false if invalid
     */
    public function verifySigned(string $token, ?string $key = null, int $maxAge = 0): array|false;
}
