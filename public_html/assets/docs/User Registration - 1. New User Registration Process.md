# **MVCLixo Framework: User Registration Overview**

This document provides a focused overview of the **User Registration** process in the MVCLixo framework. It highlights the key components, flow, and implementation details, ensuring clarity and maintainability.



 M src/App/Entities/User.php
 M src/App/Features/Auth/AuthConst.php
 M src/App/Features/Auth/LoginController.php
 M src/App/Features/Auth/Views/login.php
 M src/App/Features/Testy/TestyConst.php
 M src/App/Features/Testy/TestyController.php
 M src/App/Views/menu.php
 M src/Config/app.php
 M src/Core/Controller.php
 M src/Core/FrontController.php
    M src/Core/Middleware/MiddlewareFactory.php
    M src/Core/Middleware/TimingMiddleware.php
    M src/Core/Router.php
    M src/Core/Services/ConfigService.php
    M src/Core/View.php
    M src/dependencies.php
    M src/public_html/assets/css/menu.css
    M src/public_html/index.php

?? src/App/Features/Auth/Form/RegistrationFieldRegistry.php
?? src/App/Features/Auth/Views/registration.php
?? src/App/Features/Auth/Views/verification_pending.php
?? src/App/Features/Auth/Views/verification_email.php


?? src/App/Features/Auth/EmailVerificationController.php
?? src/App/Features/Auth/Form/RegistrationFormType.php
?? src/App/Features/Auth/RegistrationController.php
?? src/App/Features/Auth/Views/password_reset.php
?? src/App/Features/Auth/Views/registration_success.php
    ?? src/App/Features/Auth/Views/verification_error.php
    ?? src/App/Features/Auth/Views/verification_success.php
?? src/App/Features/Auth/Views/verification_resend.php
?? src/App/Features/Testy/Views/emailtest.php
?? src/App/Features/Testy/Views/placeholder.php
?? src/App/Services/Email/
?? src/App/Services/Interfaces/EmailServiceInterface.php
?? src/Config/email.php
?? src/Core/Email/
    ?? src/Core/Form/AbstractFormType.php
?? src/TokenService.php
?? src/TokenServiceInterface.php


---

## **1. Registration Flow**

### **Step 1: Display Registration Form**
Capture new user values and Submit
- **url**: http://mvclixo.tv/registration
- **Controller**: `RegistrationController::indexAction()`
- **View**: registration.php
- **Form Handling**:
  - The form is created using `FormFactory` and rendered with `FormView`.
  - Fields include:
    - `username`
    - `email`
    - `password`
    - `confirm_password`
  - Validation errors are displayed using `errorSummary()`.

---

### **Step 2: Form Submission and Validation**
- **Controller**: `RegistrationController::indexAction()`
- **Validation**:
  - **Unique Username**: Checks if the username already exists in the database.
  - **Unique Email**: Ensures the email is not already registered.
  - **Password Confirmation**: Ensures `password` matches `confirm_password`.

---

### **Step 3: User Creation**
- **Entity**: `User`
- **Repository**: `UserRepositoryInterface`
- **Process**:
  1. A new `User` entity is created with the following attributes:
     - `username`
     - `email`
     - `passwordHash` (hashed using `password_hash`)
     - `roles` (default: `['user']`)
     - `status` (default: `PENDING`)
  2. An activation token is generated using `User::generateActivationToken()` with a 24-hour expiry.
  3. The user is saved to the database via `UserRepositoryInterface::create()`.

---

### **Step 4: Send Verification Email**
- **Service**: `EmailNotificationService`
- **Template**: verification_email.php
- **Process**:
  1. A verification email is sent to the user using the `EmailNotificationService::sendVerificationEmail()` method.
  2. The email includes:
     - A verification link with the activation token.
     - Expiry information (24 hours).
     - A fallback plain-text URL.

---

### **Step 5: Redirect to Pending Verification Page**
- **Controller**: `RegistrationController::indexAction()`
- **Redirect**: `/verify-email/pending`
- **View**: verification_pending.php
- **Message**:
  - Informs the user that their account has been created but requires email verification.

---

## **2. Key Components**

### **2.1. Controller**
#### **RegistrationController**
- Handles the registration process.
- Validates form data, creates the user, and sends the verification email.

---

### **2.2. Entity**
#### **User**
- Represents the user in the system.
- Key attributes:
  - `username`, `email`, `passwordHash`
  - `roles` (array)
  - `status` (enum: `PENDING`, `ACTIVE`, etc.)
  - `activationToken`, `activationTokenExpiry`
- Key methods:
  - `generateActivationToken(int $expireHours)`: Generates a secure activation token with an expiry.
  - `setPassword(string $password)`: Hashes and sets the user's password.

---

### **2.3. Repository**
#### **UserRepositoryInterface**
- Handles database operations for the `User` entity.
- Key methods:
  - `findByUsername(string $username)`: Checks if a username exists.
  - `findByEmail(string $email)`: Checks if an email exists.
  - `create(User $user)`: Saves a new user to the database.

---

### **2.4. Email Notification**
#### **EmailNotificationService**
- Centralized service for sending emails.
- Key methods:
  - `sendVerificationEmail(User $user, string $token, ?UriInterface $requestUri)`: Sends a verification email with a token.
- Uses `EmailServiceInterface` to send emails via SMTP or Mailgun.

---

### **2.5. Views**
#### **registration.php**
- Displays the registration form.
- Uses `$form->row()` to render fields dynamically.

#### **registration_success.php**
- Displays a success message after registration.

#### **verification_pending.php**
- Informs the user that email verification is required.

---

## **3. Security Features**

### **3.1. Password Hashing**
- Passwords are hashed using `password_hash()` with the `PASSWORD_DEFAULT` algorithm before being stored in the database.
- This ensures that even if the database is compromised, passwords remain secure.


### **3.2. Email Verification**
- Users must verify their email address before their account becomes active.
- Tokens expire after 24 hours.
- Verification tokens are:
    - Randomly generated using `random_bytes()` for high entropy.
    - Stored securely in the database with an expiry timestamp.
    - Cleared after successful activation.

### **3.3. CSRF Protection**
- All forms include CSRF tokens to prevent cross-site request forgery attacks.

### **3.4. Email Enumeration Prevention**
- The system does not reveal whether an email exists during the resend process.
- A generic success message is always displayed:
    - *"If your email exists in our system and requires verification, a new verification link has been sent."*

---


## **4. Future Enhancements** // TODO

### **4.1. Rate Limiting for Resend Verification Email** [Planned Feature]
- Add a cooldown period (e.g., 5 minutes) for resending verification emails to prevent abuse.

### **4.2. Password Strength Validation** [Planned Feature]
- Enforce stronger password policies during registration (e.g., minimum length, special characters).

### **4.3. Expired Token Cleanup** [Planned Feature]
- Implement a background task or cron job to periodically delete expired tokens from the database.

---

## **5. Summary**
The MVCLixo framework implements a robust and secure user registration process. It follows best practices such as email verification, password hashing, and CSRF protection. The modular design ensures maintainability and extensibility for future enhancements.
