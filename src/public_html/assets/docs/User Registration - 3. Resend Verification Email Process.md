# **MVCLixo Framework: Resend Verification Email Process**

This document provides a detailed overview of the **Resend Verification Email** process in the MVCLixo framework. It describes the flow, key components, and implementation details to ensure clarity and maintainability.

---

## **1. Resend Verification Email Flow**

### **Step 1: User Requests a New Verification Email**
- **URL**: `/verify-email/resend`
- **Controller**: `EmailVerificationController::resendAction()`
- **View**: verification_resend.php
- **Process**:
  1. The user accesses the **Resend Verification Email** page.
  2. The user enters their email address and submits the form.

---

### **Step 2: Validate the Email Address**
- **Controller**: `EmailVerificationController::resendAction()`
- **Process**:
  1. The system validates the email address format.
     - If invalid, the user is shown an error message:
       ```
       Please enter a valid email address.
       ```
  2. The system checks if the email exists in the database and if the user’s status is `PENDING`.
     - If the email does not exist or the user is already active, the system displays a generic success message to prevent email enumeration:
       ```
       If your email exists in our system and requires verification, a new verification link has been sent.
       ```

---

### **Step 3: Generate a New Activation Token**
- **Entity**: `User`
- **Method**: `User::generateActivationToken()`
- **Process**:
  1. A new activation token is generated with a 24-hour expiry.
  2. The token is saved to the database.

---

### **Step 4: Send the Verification Email**
- **Service**: `EmailNotificationService`
- **Template**: `verification_email.php`
- **Process**:
  1. The system sends a new verification email to the user.
  2. The email includes:
     - A verification link with the new activation token.
     - Expiry information (24 hours).
     - A fallback plain-text URL.

---

### **Step 5: Provide Feedback to the User**
- **Controller**: `EmailVerificationController::resendAction()`
- **View**: `verification_pending.php`
- **Message**:
  - The user is redirected to the **Email Verification Pending** page with a success message:
    ```
    A new verification email has been sent. Please check your inbox.
    ```

---

## **2. Key Components**

### **2.1. Controller**
#### **EmailVerificationController**
- Handles the resend verification email process.
- Key Methods:
  - `resendAction(ServerRequestInterface $request)`: Processes the resend request and sends a new verification email.

---

### **2.2. Entity**
#### **User**
- Represents the user in the system.
- Key Attributes:
  - `activationToken`: Storethe activation token.
  - `activationTokenExpiry`: Store the expiry timestamp of the token.
- Key Methods:
  - `generateActivationToken(int $expireHours)`: Generates a secure activation token with an expiry.

---

### **2.3. Repository**
#### **UserRepositoryInterface**
- Handles database operations for the `User` entity.
- Key Methods:
  - `findByEmail(string $email)`: Retrieves a user by their email address.
  - `update(User $user)`: Updates the user's activation token and expiry.

---

### **2.4. Email Notification**
#### **EmailNotificationService**
- Sends verification emails to users.
- Key Method:
  - `sendVerificationEmail(User $user, string $token, ?UriInterface $requestUri)`: Sends a verification email with the activation token.

---

### **2.5. Views**
#### **verification_resend.php**
- Displays the form for requesting a new verification email.
- Includes CSRF protection and email validation.

#### **verification_pending.php**
- Informs the user that a new verification email has been sent.

---

## **3. Security Features**

### **3.1. Email Enumeration Prevention**
- The system does not reveal whether an email exists in the database.
- A generic success message is always displayed:
  ```
  If your email exists in our system and requires verification, a new verification link has been sent.
  ```

### **3.2. CSRF Protection**
- The resend form includes a CSRF token to prevent cross-site request forgery attacks.

### **3.3. Rate Limiting**
- **Current Status**: Not implemented.
- **Future Enhancement**:
  - Add rate limiting to prevent abuse of the resend endpoint (e.g., limit to one request every 5 minutes per user or IP address).

---

## **4. Error Handling**

### Invalid Email Address
- If the email address format is invalid, the user is shown an error message:
  ```
  Please enter a valid email address.
  ```

### Email Not Found or Already Active
- If the email does not exist or the user is already active, the system displays a generic success message to prevent email enumeration.

---

## **5. Future Enhancements**

### **5.1. Rate Limiting**
- Add rate limiting to the `resendAction()` method to prevent abuse:
  - Limit resend requests to once every 5 minutes per user or IP address.

### **5.2. Token Cleanup**
- Implement a background task to periodically delete expired tokens from the database.

---

## **6. Summary**
The **Resend Verification Email** process in the MVCLixo framework ensures that users can request a new verification email securely and efficiently. The system prevents email enumeration and includes CSRF protection for added security. Future enhancements, such as rate limiting and token cleanup, will further improve the process.### Email Not Found or Already Active
- If the email does not exist or the user is already active, the system displays a generic success message to prevent email enumeration.

---

## **5. Future Enhancements**

### **5.1. Rate Limiting**
- Add rate limiting to the `resendAction()` method to prevent abuse:
  - Limit resend requests to once every 5 minutes per user or IP address.

### **5.2. Token Cleanup**
- Implement a background task to periodically delete expired tokens from the database.

---

## **6. Summary**
The **Resend Verification Email** process in the MVCLixo framework ensures that users can request a new verification email securely and efficiently. The system prevents email enumeration and includes CSRF protection for added security. Future enhancements, such as rate limiting and token cleanup, will further improve the process.