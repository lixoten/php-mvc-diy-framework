// In a controller or service:
$tokenGener$tokenServiceator = $container->get(Core\Security\TokenServiceInterface::class);

// Generate a basic token
$simpleToken = $tokenService->generate();

// Generate a URL-safe token for email verification links
$urlSafeToken = $tokenService->generateUrlSafe();

// Generate a token with expiration for password reset
$resetTokenData = $tokenService->generateWithExpiry(7200); // 2 hours expiry
$token = $resetTokenData['token'];
$expiresAt = $resetTokenData['expires_at'];
```