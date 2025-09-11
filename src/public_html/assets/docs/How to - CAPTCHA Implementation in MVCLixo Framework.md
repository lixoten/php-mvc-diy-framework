# CAPTCHA Implementation in MVCLixo Framework
# Important!!! This document might be obsolte and out of date

./vendor/bin/phpunit Tests/Core/Security/RateLimitServiceTest.php
./vendor/bin/phpunit Tests/Core/Middleware/RateLimitMiddlewareTest.php 
./vendor/bin/phpunit Tests/App/Repository/RateLimitRepositoryTest.php
./vendor/bin/phpunit Tests/Integration/LoginRateLimitingIntegrationTest.php

## Notes
- @workspace src\App\Features\Auth\LoginController.php
- src\Config\security.php
- src\Core\Form\Field\Type\CaptchaFieldType.php
- src\Core\Form\Field\Field.php
- src\Core\Form\Field\FieldInterface.php
- src\Core\Form\Validation\Rules\CaptchaValidator.php
- src\Core\Form\Validation\Validator.php
- src\Core\Form\View\FormView.php
- src\Core\Form\Form.php
- src\Core\Form\FormHandler.php
- src\Core\Security\Captcha\CaptchaServiceInterface.php
- src\Core\Security\Captcha\GoogleReCaptchaService.php
- src\Core\Security\BruteForceProtectionService.php
- src\dependencies.php

RateLimiting Files
- src\Core\Security\RateLimitService.php
- src\Core\Security\RateLimitServiceInterface.php
- src\Core\Middleware\RateLimitMiddleware.php
- src\Config\security.php
- src\Core\Auth\SessionAuthenticationService.php
- src\App\Repository\RateLimitRepository.php
- src\App\Repository\RateLimitRepositoryInterface.php
- src\dependencies.php



d:\xampp\htdocs\my_projects\mvclixo\src\Core\Security\Captcha\GoogleReCaptchaService.php
# captcha
./vendor/bin/phpunit Tests/Core/Security/Captcha/GoogleReCaptchaServiceTest.php
./vendor/bin/phpunit Tests/Core/Form/FormHandlerCaptchaTest.php
./vendor/bin/phpunit Tests/App/Features/Auth/LoginControllerCaptchaTest.php
./vendor/bin/phpunit Tests/Integration/CaptchaIntegrationTest.php


src\App\Features\Auth\LoginController.php
- src\Config\security.php
- src\Core\Form\FormHandler.php
- src\Core\Security\Captcha\CaptchaServiceInterface.php
- src\Core\Security\Captcha\GoogleReCaptchaService.php
- src\dependencies.php


Tell me how captcha works in my Application. make sure you are explain our system not concepts,



## Overview

The MVCLixo framework implements a CAPTCHA system to prevent automated form submissions and enhance security, particularly for sensitive actions like login and registration. The implementation uses Google reCAPTCHA but is designed with interfaces to allow for alternative CAPTCHA providers.

## Core Components
https://www.google.com/recaptcha/admin/site/722719784

### 1. Service Layer

#### `CaptchaServiceInterface`
- Defines the contract for all CAPTCHA services
- Key methods:
  - `isRequired()`: Determines if CAPTCHA should be shown
  - `render()`: Generates HTML for the CAPTCHA
  - `verify()`: Validates the CAPTCHA response
  - `getScripts()`: Returns necessary JavaScript
  - `getSiteKey()`: Gets the public site key

#### `GoogleReCaptchaService`
- Implementation for Google reCAPTCHA
- Supports both v2 (checkbox) and v3 (invisible)
- Uses stream context to call Google's verification API
- Works with `BruteForceProtectionService` to determine when CAPTCHA is needed

### 2. Form Integration

#### `CaptchaFieldType`
- Custom field type for CAPTCHA in the form system
- Renders the CAPTCHA HTML
- Registers with the field type registry

#### `CaptchaValidator`
- Validates the CAPTCHA response from submitted forms
- Extracts `g-recaptcha-response` from request body
- Returns error messages for failed verification

### 3. View Integration

#### `FormView`
- Special `captcha()` method for rendering CAPTCHA fields
- Handles error display for invalid CAPTCHA attempts

## Configuration

CAPTCHA settings are defined in `security.php`:

```php
'captcha' => [
    'provider' => 'google',
    'site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? '',
    'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? '',
    'version' => 'v2',  // 'v2' or 'v3'
    'score_threshold' => 0.5,  // For v3 only
    'thresholds' => [
        'login' => 3,  // Show CAPTCHA after 3 failed attempts
        'registration' => 2,
        'password_reset' => 2,
        'activation_resend' => 3,
        'email_verification' => 3
    ]
]
```

## Form Processing Flow

1. **Controller decides if CAPTCHA is needed**:
   ```php
   $captchaRequired = $this->captchaService->isRequired('login', $ipAddress);
   ```

2. **Form creation with CAPTCHA when needed**:
   ```php
   $form = $this->formFactory->create(
       $this->loginFormType,
       [], 
       ['captcha_required' => $captchaRequired]
   );
   ```

3. **Form handling passes request to validator**:
   ```php
   $formHandled = $this->formHandler->handle($form, $request);
   ```

4. **CaptchaValidator extracts and validates the response**:
   ```php
   $captchaResponse = $request->getParsedBody()['g-recaptcha-response'] ?? '';
   if (!$this->captchaService->verify($captchaResponse)) {
       return $this->getErrorMessage($options, $this->defaultMessage);
   }
   ```

## Template Implementation

In `login.php`, the CAPTCHA is rendered conditionally:

```php
<?php if (isset($captcha_scripts)) : ?>
    <?= $captcha_scripts ?>
<?php endif; ?>

<?php if ($form->has('captcha')) : ?>
    <div class="mb-3">
        <?= $form->captcha('captcha', ['theme' => 'dark']) ?>
    </div>
<?php endif; ?>
```
