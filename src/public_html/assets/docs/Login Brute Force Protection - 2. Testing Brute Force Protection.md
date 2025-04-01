# Testing Brute Force Protection

# How to Test Brute Force Protection

This document provides step-by-step instructions for testing the brute force protection system.

## Test Accounts

Use these seeded accounts for testing:
- Username: `admin`, Password: `password123`
- Username: `admin2`, Password: `password123`

## Basic Tests

1. **Verify Account Lockout**
   - Navigate to the login page
   - Enter username `admin` with incorrect password (e.g., `wrongpassword`) 5 times
   - On the 6th attempt, you should see an error: "Too many failed login attempts. Please try again later."
   - The account should remain locked for 15 minutes

2. **Verify Per-User Lockout**
   - After locking `admin` account, try logging in as `admin2`
   - Enter correct password `password123`
   - It should succeed, proving lockouts are per-user

3. **Verify IP-Based Lockout**
   - Lock `admin` account with 5 failed attempts
   - Then try incorrect passwords for `admin2` 10+ times
   - Eventually (after 15 total attempts), you should see: "Too many failed login attempts from your location"

4. **Test Lockout Reset After Time**
   - Lock an account by failed attempts
   - Wait 15+ minutes
   - Try to login again - it should now allow new attempts

5. **Test Successful Login Resets Lockout**
   - Make 4 failed attempts (not enough to lock)
   - Successfully login with correct password
   - Log out
   - You should now have a fresh set of 5 attempts (previous 4 should be cleared)

## Verifying Database Records

1. **Check Failed Attempts Recording**
   - Make some failed login attempts
   - Run SQL query: `SELECT * FROM login_attempts ORDER BY attempted_at DESC;`
   - Verify entries are created with correct username and IP address

2. **Check Attempt Clearing on Success**
   - Make failed login attempts for a specific user
   - Then successfully login as that user
   - Run SQL query: `SELECT COUNT(*) FROM login_attempts WHERE username_or_email = 'admin';`
   - Count should be 0 (all attempts cleared)

## Troubleshooting

If lockout testing doesn't work as expected:
1. **Check Database Records**: Verify attempts are being recorded in the database
2. **Check Config Values**: Ensure `max_attempts` is set to 5 and `lockout_time` to 900 seconds
3. **Check IP Detection**: Ensure the system correctly identifies your IP address
4. **Browser Issue**: Try from a private/incognito window to rule out caching issues