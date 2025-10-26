Using "Codebase"

Searched for `FormatterService`, 4 results

Searched for `ValidatorService`, no results

Searched for files matching `**/Formatter*.php`, 8 matches

Searched for files matching `**/Validator*.php`, 8 matches

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Services/FormatterService.php)

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Formatters/FormatterInterface.php)

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Formatters/FormatterRegistry.php)

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Form/Validation/Validator.php)

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Form/Validation/ValidatorInterface.php)

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Form/Validation/ValidatorRegistry.php)

Searched for files matching `**/src/Core/Formatters/*Formatter.php`, 16 matches

Searched for files matching `**/src/Core/Form/Validation/*Validator.php`, 2 matches

Searched text for `class.*Formatter.*implements FormatterInterface`, 2 results

Searched text for `class.*Validator.*implements ValidatorInterface`, 1 result

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Formatters/AbstractFormatter.php)

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Form/Validation/Rules/AbstractValidator.php)

Searched for files matching `**/src/Core/Form/Validation/Rules/*Validator.php`, 46 matches

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Formatters/EmailFormatter.php)

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Formatters/PhoneNumberFormatter.php)

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Form/Validation/Rules/EmailValidator.php)

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Form/Validation/Rules/RequiredValidator.php)

Read [](file:///d%3A/xampp/htdocs/my_projects/mvclixo/src/Core/Formatters/TextFormatter.php)

Based on analyzing your codebase, here's a comparison of your Validator and Formatter architectures:

## Architecture Similarities

Both systems follow similar patterns:

1. **Registry Pattern**: Both use a registry to manage and retrieve instances
   - `FormatterRegistry`
   - `ValidatorRegistry`

2. **Interface-Based Design**: Both define contracts via interfaces
   - `FormatterInterface`
   - `ValidatorInterface`

3. **Abstract Base Class**: Both provide abstract implementations
   - `AbstractFormatter`
   - `AbstractValidator`

4. **Service Layer**: Both have a service that coordinates operations
   - `FormatterService`
   - `Validator` (acts as service)

## Key Differences

### 1. **Callable Support**

**FormatterService** is more flexible:
```php
// Supports both FormatterInterface instances AND callables
public function format(string $formatterName, mixed $value, array $options = []): string
{
    $formatter = $this->registry->get($formatterName);

    if ($formatter instanceof FormatterInterface) {
        return $formatter->format($value, $options);
    } elseif (is_callable($formatter)) {
        return $formatter($value, $options); // ✅ Supports closures
    }
}
```

**ValidatorService** only supports `ValidatorInterface`:
```php
// Only works with ValidatorInterface instances
public function validate($value, string $name, array $options = []): ?string
{
    return $this->get($name)->validate($value, $validatorOptions); // ❌ No callable support
}
```

### 2. **Registry Initialization**

**FormatterRegistry** pre-registers defaults with mixed types:
```php
public function registerDefaults(): void
{
    $this->formatters = [
        'htmlspecialchars' => fn($value) => htmlspecialchars((string)($value ?? '')), // Callable
        'email' => new EmailFormatter(), // Instance
        // ...
    ];
}
```

**ValidatorRegistry** requires explicit injection:
```php
public function __construct(array $defaultValidators = [])
{
    foreach ($defaultValidators as $validator) {
        $this->register($validator); // Must be ValidatorInterface
    }
}
```

### 3. **Error Handling**

**FormatterService** has fallback mechanism:
```php
private function getFallbackValue(mixed $value): string
{
    if ($value === null) return '';
    if (is_bool($value)) return $value ? 'Yes' : 'No';
    if (is_array($value)) return '[Complex Value]';
    return (string) $value;
}
```

**Validators** throw exceptions:
```php
if (!$this->has($name)) {
    throw new \Core\Exceptions\ValidatorNotFoundException("Validator '{$name}' not found.");
}
```

### 4. **Caching**

**FormatterService** implements caching:
```php
private array $cache = [];
private bool $cacheEnabled = true;

public function format(string $formatterName, mixed $value, array $options = []): string
{
    if ($this->cacheEnabled && isset($this->cache[$cacheKey])) {
        return $this->cache[$cacheKey];
    }
    // ...
}
```

**Validators** have no caching.

### 5. **Dual Responsibilities**

**FormatterInterface** has two distinct operations:
```php
interface FormatterInterface
{
    public function format(mixed $value, array $options = []): string;
    public function sanitize(mixed $value): string; // ⚠️ Separate concern
}
```

**ValidatorInterface** has single responsibility:
```php
interface ValidatorInterface
{
    public function validate($value, array $options = []): ?string; // ✅ Single purpose
}
```

## SOLID Principles Analysis

### ✅ **Validators Follow SOLID Better**

1. **Single Responsibility Principle (SRP)**
   - ✅ Validators: Each validator has ONE job - validate
   - ⚠️ Formatters: Mix formatting AND sanitization concerns

2. **Open/Closed Principle (OCP)**
   - ✅ Both: Can add new validators/formatters without modifying existing code

3. **Liskov Substitution Principle (LSP)**
   - ✅ Validators: All return `?string` consistently
   - ⚠️ Formatters: Callables break type safety (no interface contract)

4. **Interface Segregation Principle (ISP)**
   - ✅ Validators: Clean, focused interface
   - ⚠️ Formatters: `supports()` method may not be needed by all implementations

5. **Dependency Inversion Principle (DIP)**
   - ✅ Both: Depend on abstractions (interfaces), not concrete classes

## ⚠️ **Concern Separation Issue**

You're mixing **sanitization** with **formatting** in FormatterInterface:

```php
// 🔴 Violation of SRP
interface FormatterInterface
{
    public function format(mixed $value, array $options = []): string;
    public function sanitize(mixed $value): string; // Should be separate!
}
```

**Sanitization** (preparing data for storage/validation) is different from **formatting** (preparing data for display).

## Recommendations

### 1. **Separate Sanitization**

Create a dedicated `SanitizerInterface`:

````php
interface SanitizerInterface
{
    public function sanitize(mixed $value): string;
    public function getName(): string;
}

class EmailSanitizer implements SanitizerInterface
{
    public function sanitize(mixed $value): string
    {
        $email = trim((string)$value);
        $email = strtolower($email);
        return filter_var($email, FILTER_SANITIZE_EMAIL) ?: '';
    }

    public function getName(): string
    {
        return 'email';
    }
}
````

### 2. **Add Callable Support to Validators**

Make validators as flexible as formatters:

````php
public function set(string $name, ValidatorInterface|callable $validator): void
{
    if (!$validator instanceof ValidatorInterface && !is_callable($validator)) {
        throw new InvalidArgumentException('Validator must be ValidatorInterface or callable');
    }

    $this->validators[$name] = $validator;
}
````

### 3. **Standardize Registry Behavior**

Both registries should support:
- Factory pattern (lazy loading)
- Direct instances
- Callables
- Default registration

### 4. **Consider Caching for Validators**

For expensive validations (e.g., database uniqueness checks), add caching:

````php
class ValidatorService
{
    private array $cache = [];

    public function validateWithCache(string $name, $value, array $options = []): ?string
    {
        $cacheKey = md5($name . serialize($value) . serialize($options));

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $result = $this->validate($value, $name, $options);
        $this->cache[$cacheKey] = $result;

        return $result;
    }
}
````

## Summary

| Aspect | Validators | Formatters | Winner |
|--------|-----------|-----------|---------|
| **SRP Compliance** | ✅ Single purpose | ⚠️ Mixed concerns | Validators |
| **Type Safety** | ✅ Interface-only | ⚠️ Mixed callables | Validators |
| **Flexibility** | ❌ No callables | ✅ Supports callables | Formatters |
| **Error Handling** | ❌ Throws exceptions | ✅ Graceful fallback | Formatters |
| **Performance** | ❌ No caching | ✅ Built-in cache | Formatters |
| **Extensibility** | ✅ Clean extension | ✅ Clean extension | Tie |

**Overall**: Validators are more SOLID-compliant, but Formatters are more feature-rich. Ideally, separate sanitization from formatting and add callable support to validators to get the best of both worlds.