# Understanding Rate Limiting and Brute Force Protection

This document explains how the rate limiting and brute force protection system works in our application.

## Core Concept

The rate limiting system protects sensitive endpoints from abuse by limiting the number of requests that can be made within a specific timeframe. This prevents various attacks including password guessing, account enumeration, and credential stuffing.

## System Architecture

Our implementation uses a middleware-based approach with these components:

1. **Rate Limit Middleware**: Intercepts HTTP requests, identifies protected routes, and enforces limits
2. **Brute Force Protection Service**: Contains core logic for tracking attempts and enforcing limits
3. **Rate Limit Repository**: Handles database operations for storing and querying attempt records

## How It Works

1. **Request Interception**
   - The `RateLimitMiddleware` intercepts incoming requests
   - It maps request paths to action types (e.g., `/login` → `login`, `/registration` → `registration`)
   - Each action type has its own separate rate limits

2. **Tracking Attempts**
   - Attempts are recorded in the `rate_limit_attempts` table with:
     - Identifier (IP address or combined identifier)
     - Action type (login, registration, password_reset, etc.)
     - Success status (failed or successful)
     - IP address for additional tracking
     - Timestamp and user agent information

3. **Enforcing Limits**
   - Before processing a request, the system checks recent failures:
     - If identifier-based limit is reached, request is blocked
     - If IP-based limit is reached, request is also blocked
     - Different thresholds exist for different action types

4. **Updating Attempt Status**
   - Initially all attempts are recorded as failures
   - After successful processing (HTTP 2xx/3xx), the status is updated to success
   - Failed attempts (HTTP 4xx/5xx) remain marked as failures

5. **Database Management**
   - After successful requests, there's a 10% chance old records are cleaned up
   - This keeps the database size manageable without scheduled maintenance

## Configuration

Each action type has customizable settings:

```
'login' => [
    'max_attempts' => 5,      // Per identifier (username/email)
    'ip_max_attempts' => 15,  // Per IP address
    'lockout_time' => 900     // 15 minutes (in seconds)
]
```

The system supports these action types:
- `login`: Protects login endpoints
- `registration`: Prevents mass account creation
- `password_reset`: Protects password reset functionality
- `activation_resend`: Limits email verification resend requests
- `email_verification`: Controls verification link usage

## Security Benefits

1. **Prevents Brute Force Attacks**: Makes systematic password guessing impractical
2. **Limits Enumeration**: Helps prevent discovering valid accounts through rate limiting
3. **Reduces Spam**: Prevents abuse of functionality that sends emails
4. **Protection Against Distributed Attacks**: Uses both per-identifier and per-IP limits
5. **Self-Maintaining**: Periodically cleans up old records to maintain performance

## Implementation Details

The system uses a sliding window approach:
- Attempts are counted within the most recent period (configured by lockout_time)
- As old attempts age beyond the window, they no longer count toward limits
- This creates a natural cooldown rather than a fixed reset time

