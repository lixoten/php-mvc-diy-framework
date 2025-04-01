# Understanding Brute Force Protection

This document explains how the brute force protection system works in our application.

## Core Concept

Brute force protection prevents attackers from guessing user passwords through multiple login attempts. Our system implements a temporary lockout mechanism after several failed attempts.

## How It Works

1. **Tracking Failed Attempts**
   - Each failed login is recorded in the `login_attempts` table with:
     - Username/email that was attempted
     - IP address of the request
     - Timestamp when the attempt occurred
     - User agent information (browser details)

2. **Enforcing Lockouts**
   - Before processing any login attempt, the system checks recent failures
   - If a user has 5+ failed attempts within the past 15 minutes, login is blocked
   - Additionally, an IP address with 15+ failed attempts (across any users) is also blocked
   - Different thresholds prevent both targeted attacks and distributed attempts

3. **Resetting Attempts**
   - When a user successfully logs in, all their previous failed attempts are cleared
   - This allows legitimate users to regain access immediately after a successful login

4. **Database Maintenance**
   - After successful logins, there's a 10% chance that expired attempts (older than 30 minutes) are removed
   - This keeps the database size manageable without requiring scheduled maintenance

## Lockout Implementation

The system implements a "sliding window" approach:
- Failed attempts are counted within the most recent 15-minute period
- As old attempts age beyond the 15-minute window, they're no longer counted
- This creates a natural cooldown effect rather than a fixed reset time

## Security Benefits

1. **Prevents Password Guessing**: Makes systematic password guessing impractical
2. **Reduces Account Enumeration**: Similar responses for existing and non-existing accounts
3. **Protects Against Credential Stuffing**: Limits rapid automated login attempts
4. **Self-Managing**: Database cleanup happens automatically during normal use
