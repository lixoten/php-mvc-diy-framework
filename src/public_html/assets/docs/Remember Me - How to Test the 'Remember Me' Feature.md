# How to Test the "Remember Me" Feature

Now that you've fixed the checkbox to be properly unchecked by default, here's a complete testing procedure:

## Basic Functionality Test

1. **Login with Remember Me**
   - Navigate to `/login`
   - Verify "Remember Me" is unchecked by default
   - Enter your credentials
   - Check the "Remember Me" box
   - Click Login
   - Verify you're redirected to dashboard

2. **Verify Cookie Creation**
   - Open browser developer tools (F12)
   - Go to Application tab > Cookies
   - Verify a cookie named `remember_me` exists (or whatever name you used in your code)
   - Note that it has a far-future expiration date

3. **Verify Database Token**
   - Check your database:
   ```sql
   SELECT * FROM remember_tokens;
   ```
   - Verify a row exists for your user

4. **Test Persistence**
   - **IMPORTANT:** Close your browser COMPLETELY (all windows/tabs)
   - Reopen the browser
   - Navigate to a protected page like `/admin/dashboard`
   - You should be automatically logged in without seeing the login form

5. **Test Token Rotation**
    - Check database again:
    ```sql
    SELECT * FROM remember_tokens;
    ```
    - A new token should be generated each time you're auto-logged in. 
    - This is a security feature that helps prevent token theft

6. **Test Logout**
   - Click logout
   - Check database - the token should be deleted
   - Close browser completely
   - Reopen and go to protected page
   - You should NOT be automatically logged in

## Edge Case Tests

7. **Invalid Token Test**
   - Log in with Remember Me
   - Manually edit the cookie value to be invalid
   - Refresh the page
   - You should be logged out and the token in DB deleted

8. **Multiple Device Test**
   - Log in with Remember Me on two different browsers
   - Check database - there should be two different tokens
   - Log out from one browser
   - That token should be deleted but the other remains

9. **Security Testing:**
   - Try manipulating the cookie value - should invalidate the token
   - Check if tokens expire properly after their configured lifetime

These tests will ensure your Remember Me feature is working correctly and securely.


## Troubleshooting

If the Remember Me feature isn't working:

1. **Cookie Not Created**: Check that the cookie is being set with proper attributes (HTTP-only, secure)
2. **Auto-login Fails**: Verify that `attemptRememberMeLogin()` is called at the right point
3. **Token Not in Database**: Ensure the token is properly stored during login
4. **Session Issues**: Check that you're completely closing the browser (not just tabs)

**Note: Browser Incognito Mode:**
- Testing in incognito/private browsing can help isolate issues

## Token Cleanup
For database hygiene, expired tokens are periodically removed:
- A random chance (1/100) cleanup occurs during auto-login
- This prevents database bloat without requiring a dedicated cron job