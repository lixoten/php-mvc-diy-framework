# **User Registration Overview**

### **User Registration Page**
- Accessible via various login links (e.g., main menu).
- Captures user details (username, email, password) and submits the form.
- **On Submit**:
  - **Success**:
    - User is added to the database with a `PENDING` status and an activation token.
    - A verification email is sent to the user.
    - User is redirected to the **Email Verification Pending Page** (`/verify-email/pending`).
  - **Fail**:
    - The form is reshown with validation errors (e.g., "Email already registered").

---

### **User Registration Email**
- **Trigger**: Sent after successful registration.
- **Content**:
  - Includes a link with an activation token.
  - The link expires in 24 hours.
- **On Link Click**:
  - **Success**:
    - The user's status is updated to `ACTIVE`.
    - The activation token is cleared.
    - User is redirected to the **Verification Success** page.
  - **Fail**:
    - If the token is invalid or expired:
      - User is redirected to the **Verification Error** page.
      - A message is displayed: *"Your verification link has expired. Please request a new one."*
      - User can request a new verification email.

---

### **Resend Verification Email Page**
- **How to Access**:
  - From the **Email Verification Pending Page** or when attempting to log in with an unverified account.
- **Process**:
  - User enters their email address.
  - **On Submit**:
    - A new verification email is sent if the email exists and the account is pending.
    - A generic success message is displayed to prevent email enumeration:
      - *"If your email exists in our system and requires verification, a new verification link has been sent."*
  - **Rate Limiting**: *[Planned Feature]* Limit requests to once every 5 minutes.

---