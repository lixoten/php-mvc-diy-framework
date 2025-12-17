# Understanding the "Remember Me" Feature

The "Remember Me" feature allows users to remain logged in even after closing their browser or restarting their device. Here's how it works and how to test it:

## How "Remember Me" Works:

1. **When User Logs In with "Remember Me" checked:**
   - Generate a secure, random token
   - Store this token in database with user ID and expiration date (typically 2 weeks or 30 days)
   - Send the token to the user in a secure, HTTP-only cookie with long expiration

2. **On Subsequent Visits:**
   - If no active session exists, check for the remember me cookie
   - If cookie exists, validate the token against database
   - If valid, automatically log the user in (create new session)
   - Optionally, rotate the token for enhanced security

3. **Security Measures:**
   - Tokens should be cryptographically secure (use `random_bytes()`)
   - Tokens should be stored with a hash in the database
   - Tokens should be single-use (regenerate after each use)
   - HTTP-only cookies prevent JavaScript access

### Token Security Pattern:
We use a selector:validator pattern where:
- Selector: Used to look up the token in the database (stored in plain text)
- Validator: Secret proof component (stored only as a hash in the database)
This prevents timing attacks while maintaining security.

## Security Considerations:
- Remember that "Remember Me" is always a trade-off between security and convenience.
- The longer a token is valid, the more vulnerable it is to theft or misuse.

## Your Current Implementation:

In your `LoginController.php`, you already have:

```php
// Attempt login
$remember = isset($data['remember']) ? (bool)$data['remember'] : false;
$this->authService->login($data['username'], $data['password'], $remember);
```

And in your `SessionAuthenticationService`:

```php
public function login(string $username, string $password, bool $remember = false): bool
{
    // Authentication logic...
    
    if ($remember) {
        $this->createRememberMeToken($user);
    }
    
    return true;
}
```
