# Testing Rate Limiting and Brute Force Protection

This document provides detailed instructions for testing the rate limiting and brute force protection systems currently implemented in our MVC framework.

## How Our System Works

Our rate limiting system operates using these key components:

1. **IP-Based Identification**: All rate limiting is based on the visitor's IP address
   ```php
   $ipAddress = $_SERVER['REMOTE_ADDR'];
   $identifier = $ipAddress;
   ```

2. **Path-to-Action Mapping**: URLs are mapped to specific action types
   ```php
   $pathMappings = [
       '/registration' => 'registration',
       '/login' => 'login',
       '/forgot-password' => 'password_reset',
       '/verify-email/resend' => 'activation_resend',
       '/verify-email/verify' => 'email_verification',
   ];
   ```

3. **Dual-Level Protection**: Each action type has:
   - Per-identifier limits (currently IP-based)
   - Global IP limits (higher thresholds)

4. **Configuration Tiers**: 
   - Default values in `BruteForceProtectionService`
   - Environment-specific values in security.php
   - Custom overrides in dependencies.php

## Test Accounts

Use these seeded accounts for testing:
- Username: `admin`, Password: `password123`
- Username: `admin2`, Password: `password123`

## Login Protection Tests

1. **Verify IP-Based Lockout**
   - Navigate to the login page
   - Enter username `admin` with incorrect password (e.g., `wrongpassword`) 5 times
   - On the 6th attempt, you should see: "Too many attempts. Please try again later."
   - The IP address will be blocked for 15 minutes (or 1 minute if using the customConfig)

2. **Test From Different IP**
   - After being locked out from one IP, try accessing from a different IP
   - You should be able to attempt logins again

3. **Verify Custom Config Override**
   - Based on our dependencies.php, login is limited to 1 attempt
   - Make 1 failed login attempt
   - On the 2nd attempt, you should be locked out
   - This confirms the custom config override is working

4. **Test Lockout Reset After Time**
   - Lock your IP by failed attempts
   - Wait for the lockout period (60 seconds with custom config)
   - Try to login again - it should now allow new attempts

## Registration Protection Tests

1. **Test Registration Rate Limiting**
   - Make 3 registration attempts with invalid data (missing fields or invalid email)
   - On the 4th attempt, you should be rate-limited for 60 minutes

2. **Test IP-Based Registration Limiting**
   - Try creating multiple accounts rapidly from the same IP
   - After 10 attempts (successful or failed), you should be rate-limited
   - Note: For registration, both successful and failed attempts count toward IP limits

## Email Verification Tests

1. **Test Verification Email Resend Limits**
   - Go to `/verify-email/resend`
   - Submit 3 resend requests
   - On the 4th attempt, you should be rate-limited

2. **Test Email Verification Link Protection**
   - Note: With our custom config in dependencies.php, email verification is limited to 4 attempts
   - Click on invalid verification links multiple times
   - After 4 attempts, further verification attempts should be blocked

## Verifying Database Records

1. **Check Rate Limit Records**
   - Make some failed login attempts
   - Query the database: 
     ```sql
     SELECT * FROM rate_limit_attempts WHERE action_type = 'login' ORDER BY attempted_at DESC;
     ```
   - Verify entries contain:
     - identifier = IP address
     - action_type = 'login'
     - success = 0 (for failed attempts)

2. **View Success Status Updates**
   - Make a failed login attempt, then a successful one
   - Query: 
     ```sql
     SELECT * FROM rate_limit_attempts WHERE action_type = 'login' ORDER BY attempted_at DESC;
     ```
   - The successful attempt should have success = 1

3. **View Different Actions**
   - Query: 
     ```sql
     SELECT action_type, COUNT(*) FROM rate_limit_attempts GROUP BY action_type;
     ```
   - This shows distribution of rate limit attempts across different actions

## Testing Custom Configuration

Our system uses a three-tier configuration approach:

1. **Default Configuration** (in BruteForceProtectionService.php)
   ```php
   private array $defaultConfig = [
       'login' => [
           'max_attempts' => 5,
           'ip_max_attempts' => 15,
           'lockout_time' => 900 // 15 minutes
       ],
       // other action types...
   ];
   ```

2. **Environment Configuration** (in security.php)

3. **Custom Configuration** (in dependencies.php)
   ```php
   'customConfig' => [
       'email_verification' => [
           'max_attempts' => 4,      // Default is 5
           'ip_max_attempts' => 7,   // Default is 15
           'lockout_time' => 300     // 5 minutes vs default 15 minutes
       ],
       'login' => [
           'max_attempts' => 1,      // More strict than default (5)
           'lockout_time' => 60      // 1 minute vs default 15 minutes
       ]
   ]
   ```

To test custom configuration:
1. Edit dependencies.php to adjust rate limits
2. Test that your new limits are enforced
3. Return to original values when finished

## Troubleshooting

If rate limiting doesn't work as expected:

1. **Check Path Mapping**
   - Verify the paths in `RateLimitMiddleware` match your actual URLs
   - Debug output from `getActionTypeForPath()` should not be NULL

2. **Check Database Records**
   - If attempts aren't being recorded, check that your endpoints match the mapped paths

3. **Status Code Issues**
   - Attempts are marked successful if response code is < 400
   - Ensure error responses use appropriate status codes (422, 429) for failures

4. **Debug Statements**
   - Add `DebugRt::j()` calls to trace identifier values and action types

## Advanced Testing

1. **Test Token Cleanup Feature**
   - Increase cleanup chance in BruteForceProtectionService.php:
     ```php
     // Change from rand(1, 10) === 1 to:
     if (rand(1, 2) === 1) {
         $this->cleanupExpiredAttempts($actionType);
     }
     ```
   - Generate many attempts
   - Verify old records get periodically removed

2. **Test Rate Limited Redirects**
   - When rate limited, the system should redirect to home page with flash message
   - Verify this behavior by intentionally triggering rate limits