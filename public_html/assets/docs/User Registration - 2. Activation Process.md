# **MVCLixo Framework: User Activation Process**

## **1. Activation Flow**

### **Step 1: User Receives Verification Email**
- **Trigger**: After successful registration, the system sends a verification email to the user.
- **Email Content**:
  - A link containing the activation token (e.g., `http://mvclixo.tv/verify-email?token=abc123`).
  - Expiry information (e.g., "This link will expire in 24 hours").
  - A fallback plain-text URL for manual entry.

---

### **Step 2: User Clicks the Verification Link**
- **URL**: `http://mvclixo.tv/verify-email?token=<activation_token>`
- **Controller**: `EmailVerificationController::verifyAction()`
- **Process**:
  1. The system extracts the `token` from the query parameters.
  2. The system validates the token:
     - Checks if the token exists in the database.
     - Checks if the token has expired.
  3. If the token is valid:
     - The user's status is updated to `ACTIVE`.
     - The activation token is cleared (set to `null`).
  4. If the token is invalid or expired:
     - An error message is displayed, and the user is prompted to request a new verification email.

---

### **Step 3: User is Redirected**
- **Success**:
  - The user is redirected to the **Email Verified** page (`verification_success.php`).
  - A success message is displayed, and the user is prompted to log in.
- **Failure**:
  - The user is redirected to the **Verification Failed** page (`verification_error.php`).
  - An error message is displayed with options to resend the verification email or contact support.

---

## **2. Key Components**

### **2.1. Controller**
#### **EmailVerificationController**
- Handles the email verification process.
- Key Methods:
  - `verifyAction(ServerRequestInterface $request)`: Verifies the activation token and activates the user.
  - `resendAction(ServerRequestInterface $request)`: Allows users to request a new verification email.

---

### **2.2. Entity**
#### **User**
- Represents the user in the system.
- Key Attributes:
  - `activationToken`: Store the activation token.
  - `activationTokenExpiry`: Store the expiry timestamp of the token.
  - `status`: Tracks the user's status (`PENDING`, `ACTIVE`, etc.).
- Key Methods:
  - `generateActivationToken(int $expireHours)`: Generates a secure activation token with an expiry.
  - `isActivationTokenExpired()`: Checks if the activation token has expired.

---

### **2.3. Repository**
#### **UserRepositoryInterface**
- Handles database operations for the `User` entity.
- Key Methods:
  - `findByActivationToken(string $token)`: Retrieves a user by their activation token.
  - `update(User $user)`: Updates the user's status and clears the activation token.

---

### **2.4. Email Notification**
#### **EmailNotificationService**
- Sends verification emails to users.
- Key Method:
  - `sendVerificationEmail(User $user, string $token, ?UriInterface $requestUri)`: Sends a verification email with the activation token.

---

### **2.5. Views**
#### **verification_success.php**
- Displays a success message after email verification.
- Prompts the user to log in.

#### **verification_error.php**
- Displays an error message if the verification fails.
- Provides options to resend the verification email or contact support.

#### **verification_resend.php**
- Allows users to request a new verification email.

---

## **3. Security Features**

### **3.1. Token Expiry**
- Activation tokens expire after 24 hours.
- Expired tokens cannot be used to activate accounts.

### **3.2. Email Enumeration Prevention**
- The `resendAction()` method always displays a success message, even if the email does not exist in the system. This prevents attackers from enumerating valid email addresses.

### **3.3. CSRF Protection**
- All forms include CSRF tokens to prevent cross-site request forgery attacks.

---

## **4. Error Handling**

### **Invalid Token**
- If the token is invalid or does not exist:
  - The user is redirected to the **Verification Failed** page.
  - An error message is displayed.

### **Expired Token**
- If the token has expired:
  - The user is redirected to the **Verification Failed** page.
  - An error message is displayed with an option to resend the verification email.

---

## **5. Future Enhancements**

### **5.1. Rate Limiting**
- Add rate limiting to the `resendAction()` method to prevent abuse (e.g., spamming the resend endpoint).

### **5.2. Token Cleanup**
- Implement a background task to periodically delete expired tokens from the database.

### **5.3. Enhanced Security**
- Use short-lived, single-use tokens for better security.
- Log verification attempts for auditing purposes.

---

## **6. Summary**
The MVCLixo framework implements a secure and user-friendly activation process. It ensures that only verified users can access the system while providing clear feedback and options for users who