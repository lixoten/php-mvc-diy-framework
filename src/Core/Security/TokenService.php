<?php

declare(strict_types=1);

namespace Core\Security;

use App\Helpers\DebugRt;
use Core\Interfaces\ConfigInterface;

/**
 * Secure token service implementation
 */
class TokenService implements TokenServiceInterface
{
    private ConfigInterface $configService;
    private string $secretKey;

    /**
     * Constructor
     *
     * @param ConfigInterface $configService Configuration service for retrieving settings.
     * @param string|null $secretKey Optional HMAC secret key.
     */
    public function __construct(
        ConfigInterface $configService,
        ?string $secretKey = null,
    ) {
        $this->configService = $configService;

        $this->secretKey = $secretKey ?? $this->configService->get('app.secret');
        if (empty($this->secretKey)) {
            throw new \RuntimeException('A valid secret key is required for the TokenService.');
        }
    }


    /**
     * {@inheritdoc}
     */
    public function generate(int $length = 32, bool $withExpiry = false, int $expiresIn = 3600): array|string
    {
        // Generate a random token using cryptographically secure method
        $token = bin2hex(random_bytes($length));

        if (!$withExpiry) {
            return $token;
        }

        // Calculate expiry time
        //$expiresAt = (new \DateTime())->modify("+{$expiresIn} seconds")->format('Y-m-d H:i:s');

        // Calculate expiry time as a Unix timestamp
        $expiresAt = time() + $expiresIn;

        return [
            'token' => $token,
            'expires_at' => $expiresAt // Unix timestamp
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generateUrlSafe(int $length = 32): string
    {
        // Generate random bytes and encode as URL-safe base64
        $randomBytes = random_bytes($length);

        // Use URL-safe character set and remove padding
        return rtrim(strtr(base64_encode($randomBytes), '+/', '-_'), '=');
    }

    /**
     * {@inheritdoc}
     */
    public function generateWithExpiry(int $length = 32, int $expiresIn = 3600): array
    {
        return $this->generate($length, true, $expiresIn);
    }

    /**
     * {@inheritdoc}
     */
    public function hasExpired(int $expiryTime): bool
    {
        // $expiry = new \DateTime($expiryTime);
        // $now = new \DateTime();

        // return $expiry <= $now;
        return $expiryTime <= time();
    }


    /**
     * {@inheritdoc}
     */
    public function generateSigned(string $data, ?string $key = null): string
    {
        $secretKey = $key ?? $this->secretKey;
        $timestamp = time();
        $payload = $data . '|' . $timestamp;
        // return hash_hmac('sha256', $payload, $secretKey) . '.' . base64_encode($payload);

        $signature = hash_hmac('sha256', $payload, $secretKey);
        $encodedPayload = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

        return $signature . '.' . $encodedPayload;
    }

    /**
     * {@inheritdoc}
     */
    public function verifySigned(string $token, ?string $key = null, int $maxAge = 0): array|false
    {
        $secretKey = $key ?? $this->secretKey;

        // Split token into signature and payload
        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return false;
        }

        [$signature, $encodedPayload] = $parts;

        // Convert from URL-safe base64 and restore padding
        $encodedPayload = strtr($encodedPayload, '-_', '+/');
        $padding = strlen($encodedPayload) % 4;
        if ($padding) {
            $encodedPayload .= str_repeat('=', 4 - $padding);
        }

        // Decode the properly padded payload
        $payload = base64_decode($encodedPayload);
        if ($payload === false) {
            return false;
        }

        // Verify signature
        $expectedSignature = hash_hmac('sha256', $payload, $secretKey);
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }

        // Parse payload
        $payloadParts = explode('|', $payload, 2);
        if (count($payloadParts) !== 2) {
            return false;
        }

        [$data, $timestamp] = $payloadParts;
        $timestamp = (int)$timestamp;

        // Check age if required
        if ($maxAge > 0 && (time() - $timestamp) > $maxAge) {
            return false;
        }

        return ['data' => $data, 'timestamp' => $timestamp];
    }
}
