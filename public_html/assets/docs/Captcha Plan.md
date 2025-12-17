# CAPTCHA Integration Plan with Rate Limiting in MVC Framework

@workspace src\App\Features\Auth\LoginController.php
src\Config\security.php
src\Core\Form\Field\Type\CaptchaFieldType.php
src\Core\Form\Field\Field.php
src\Core\Form\Field\FieldInterface.php
src\Core\Form\Validation\Rules\CaptchaValidator.php
src\Core\Form\Validation\Validator.php
src\Core\Form\View\FormView.php
src\Core\Form\Form.php
src\Core\Form\FormHandler.php
src\Core\Security\Captcha\CaptchaServiceInterface.php
src\Core\Security\Captcha\GoogleReCaptchaService.php
src\Core\Security\BruteForceProtectionService.php
src\dependencies.php

src\Core\Form\Renderer\Bo

Tell me how captcha works in my Application. make sure you are explaing our system noot concepts,
i need a markdown document produced


## 1. Architecture Overview

### Core Components to Create
1. **CaptchaServiceInterface**
   - Core interface for any CAPTCHA implementation
   - Must be provider-agnostic

2. **GoogleReCaptchaService**
   - Implementation for Google reCAPTCHA v2/v3
   - First CAPTCHA provider

3. **CaptchaFormField**
   - New form field type for rendering CAPTCHA elements
   - Handles client-side integration

4. **CaptchaMiddleware**
   - Optional middleware for non-form CAPTCHA verification
   - For APIs or special routes

### Code Integration Points
1. **BruteForceProtectionService**
   - Add `isCaptchaRequired()` method
   - Enhance configuration with CAPTCHA thresholds

2. **Form Types**
   - Conditional CAPTCHA field in forms
   - Integration in existing form handling flow

3. **Controllers**
   - Additional verification step for CAPTCHA tokens
   - Response handling for CAPTCHA failures

## 2. Step-by-Step Implementation Plan

### Phase 1: Foundation (Core Interface and Service)

1. **Create CaptchaServiceInterface**
```php
namespace Core\Security\Captcha;

interface CaptchaServiceInterface
{
    // Check if CAPTCHA should be displayed
    public function isRequired(string $actionType, ?string $identifier = null): bool;
    
    // Generate HTML for displaying CAPTCHA
    public function render(string $formId = null, array $options = []): string;
    
    // Verify CAPTCHA response
    public function verify(string $response): bool;
    
    // Get client-side script tags
    public function getScripts(): string;
    
    // Get site key for client-side rendering
    public function getSiteKey(): string;
}
```

2. **Create Google reCAPTCHA Service Implementation**
```php
namespace Core\Security\Captcha;

class GoogleReCaptchaService implements CaptchaServiceInterface
{
    private string $siteKey;
    private string $secretKey;
    private Core\Security\BruteForceProtectionService $bruteForceService;
    
    // Constructor with DI for keys and brute force service
    
    // Implementation of interface methods
}
```

3. **Update Security Configuration**
```php
// security.php
'captcha' => [
    'provider' => 'google',
    'site_key' => $_ENV['RECAPTCHA_SITE_KEY'],
    'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'],
    'version' => 'v2',  // 'v2' or 'v3'
    'score_threshold' => 0.5,  // For v3 only
    'thresholds' => [
        'login' => 3,  // Show CAPTCHA after 3 failed attempts
        'registration' => 2,
        'password_reset' => 2,
        'email_verification' => 3
    ]
]
```

### Phase 2: Integration with Rate Limiting

1. **Update BruteForceProtectionService**
```php
// Add new methods to BruteForceProtectionService

public function isCaptchaRequired(string $actionType, string $identifier): bool
{
    $this->ensureActionTypeExists($actionType);
    $captchaThreshold = $this->config[$actionType]['captcha_threshold'] ?? 0;
    
    if ($captchaThreshold <= 0) {
        return false;
    }
    
    $cutoffTime = time() - $this->config[$actionType]['lockout_time'];
    $attempts = $this->repository->countRecentAttempts(
        $identifier,
        $actionType,
        $cutoffTime
    );
    
    return $attempts >= $captchaThreshold;
}
```

2. **Create CaptchaFieldType**
```php
namespace Core\Form\Field\Type;

class CaptchaFieldType implements FieldTypeInterface
{
    private CaptchaServiceInterface $captchaService;
    
    // Constructor with CAPTCHA service DI
    
    // Implementation of render, validate methods
}
```

3. **Register in Dependencies**
```php
// dependencies.php
'Core\Security\Captcha\CaptchaServiceInterface' => function(ContainerInterface $c) {
    $config = $c->get('config')->get('security.captcha', []);
    $bruteForceService = $c->get('Core\Security\BruteForceProtectionService');
    
    return new Core\Security\Captcha\GoogleReCaptchaService(
        $config['site_key'] ?? $_ENV['RECAPTCHA_SITE_KEY'],
        $config['secret_key'] ?? $_ENV['RECAPTCHA_SECRET_KEY'],
        $bruteForceService,
        $config
    );
},

'Core\Form\Field\Type\CaptchaFieldType' => DI\autowire()
    ->constructorParameter('captchaService', DI\get('Core\Security\Captcha\CaptchaServiceInterface')),
```

### Phase 3: Form Integration

1. **Update Form Field Registries**
```php
// Add to LoginFieldRegistry
public function registerFields(): void
{
    // Existing fields
    
    $this->registry['captcha'] = [
        'type' => 'captcha',
        'options' => [
            'label' => 'Security Check',
            'help_text' => 'Please complete the CAPTCHA to continue'
        ]
    ];
}
```

2. **Update Form Types**
```php
// Modify LoginFormType::buildForm
public function buildForm(FormBuilderInterface $builder, array $options = []): void
{
    // Define standard fields
    $fieldNames = ['username', 'password', 'remember'];
    
    // Process standard fields
    foreach ($fieldNames as $name) {
        // Get definition from registry
        $fieldDef = $this->fieldRegistry->get($name) ?? [];
        
        // Add field to form
        $builder->add($name, $fieldDef);
    }
    
    // Check if CAPTCHA is required
    $captchaRequired = $options['captcha_required'] ?? false;
    
    if ($captchaRequired) {
        // Add CAPTCHA field
        $builder->add('captcha', $this->fieldRegistry->get('captcha') ?? []);
        $fieldNames[] = 'captcha';
    }
    
    // Set form layout
    $builder->setLayout([
        'sequential' => [
            'fields' => $fieldNames
        ]
    ]);
}
```

### Phase 4: Controller Integration

1. **Update LoginController**
```php
// Modify LoginController::indexAction
public function indexAction(ServerRequestInterface $request): ResponseInterface
{
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $identifier = $ipAddress;
    
    // Check if CAPTCHA should be shown
    $captchaRequired = $this->captchaService->isRequired('login', $identifier);
    
    // Create login form
    $form = $this->formFactory->create(
        $this->loginFormType,
        ['remember' => false],
        [
            'layout_type' => 'none',
            'error_display' => 'summary',
            'renderer' => 'bootstrap',
            'captcha_required' => $captchaRequired
        ]
    );
    
    // Process form submission
    $formHandled = $this->formHandler->handle($form, $request);
    if ($formHandled && $form->isValid()) {
        $data = $form->getData();
        
        // If CAPTCHA is required, verify it
        if ($captchaRequired) {
            $captchaResponse = $data['captcha'] ?? '';
            if (!$this->captchaService->verify($captchaResponse)) {
                $form->addError('captcha', 'Invalid CAPTCHA response. Please try again.');
                // Return form with error
                // ...
            }
        }
        
        try {
            // Proceed with login
            // ...
        }
    }
    
    // View rendering with CAPTCHA script if needed
    $viewData = [
        'title' => 'Log In',
        'form' => $formView,
    ];
    
    if ($captchaRequired) {
        $viewData['captcha_scripts'] = $this->captchaService->getScripts();
    }
    
    return $this->view(AuthConst::VIEW_AUTH_LOGIN, $viewData);
}
```

2. **Do the Same for Other Controllers**:
   - `RegistrationController`
   - `EmailVerificationController` 
   - `PasswordResetController`

### Phase 5: Template Updates

1. **Update View Templates**
```php
// login.php, registration.php, etc.
<?php if (isset($captcha_scripts)): ?>
    <?= $captcha_scripts ?>
<?php endif; ?>

<!-- Form HTML -->
<form method="post">
    <?= $form->start() ?>
    
    <!-- Regular fields -->
    <?= $form->row('username') ?>
    <?= $form->row('password') ?>
    
    <!-- CAPTCHA if required -->
    <?php if ($form->has('captcha')): ?>
        <div class="captcha-container mb-3">
            <?= $form->row('captcha') ?>
        </div>
    <?php endif; ?>
    
    <!-- Submit button -->
    <div class="d-grid gap-2">
        <?= $form->submit('Login', ['class' => 'btn btn-primary']) ?>
    </div>
    
    <?= $form->end() ?>
</form>
```

## 3. Testing Plan

1. **Unit Tests**
   - Test `GoogleReCaptchaService` implementation
   - Test `isCaptchaRequired` logic in `BruteForceProtectionService`
   - Test form creation with and without CAPTCHA

2. **Integration Tests**
   - Test CAPTCHA display after X failed attempts
   - Test successful CAPTCHA submission allows form submission
   - Test failed CAPTCHA prevents form submission

3. **Manual Testing Scenarios**
   - Verify CAPTCHA appears after configured number of failures
   - Verify CAPTCHA validation works correctly
   - Test across browsers and devices

## 4. Security Considerations

1. **Progressive Security**
   - Start with no CAPTCHA for better UX
   - Show CAPTCHA after X failures
   - Apply rate limiting after Y failures
   - Complete lockout after Z failures

2. **IP + Session Tracking**
   - Use combined identifiers for more precise targeting
   - Track failures across user sessions

3. **Accessibility**
   - Ensure CAPTCHA implementation has accessibility options
   - Include audio alternatives for visual CAPTCHAs

## 5. Implementation Timeline

1. **Phase 1**: Foundation (1-2 days)
   - Core interfaces and Google reCAPTCHA service
   - Configuration setup

2. **Phase 2**: Rate Limiting Integration (1 day)
   - Update `BruteForceProtectionService`
   - Create CAPTCHA form field

3. **Phase 3-4**: Form and Controller Integration (2-3 days)
   - Update all form types and controllers
   - Handle CAPTCHA verification

4. **Phase 5**: Template Updates and Testing (1-2 days)
   - Update view templates
   - Comprehensive testing

**Total Timeline**: 5-8 days for complete implementation

Would you like me to proceed with any specific part of this plan first?