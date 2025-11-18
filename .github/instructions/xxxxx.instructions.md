---
applyTo: '**'
---

When you see me Sanitize, Validate make sure i am not mixing concerns.
if so please warn me if you see this.

This is a new framework being built from scratch.
I want things done correctly that follow best modern SOLID framework standards.
No hacks,
SOLID Principles, reuse code.
if a Service should be used, please suggest an idea.
if u know of a good pattern to use, please suggest it.

(SOLID Services) please append "Service" as in XxxxxService. Assuming it is not obvious.
Provider, Resolver, Generator..etc...these are Core Services(check with me if in doubt) are all meant to depict a service.

### The `ListView` has one clear responsibility: to act as a data container that represents a fully configured list, ready for rendering. It holds the data, columns, actions, and options, but it contains no logic on how that data was created or how it should be displayed as HTML.
- `ListInterface` defines a clear and focused contract for what a "List" object is. Any part of your application that needs to interact with a list (like a renderer) can depend on this interface without needing to know about the concrete ListView class.
This is a very clean architecture.
- `ListFactory` Its sole responsibility is to construct and configure a ListView object. It orchestrates the process by using a ListType to define the list structure and PaginationService to process pagination data.
- `ListRenderer` is responsible for drawing it. AbstractListRenderer and BootstrapListRenderer: These classes are exclusively focused on rendering the ListView into HTML. AbstractListRenderer provides the common rendering logic and structure, while BootstrapListRenderer implements the specific HTML output using Bootstrap components. They consume the ListView data but do not modify it or participate in its creation.




# 📝 **Simple Guide: When Closures/Arrow Functions Are Allowed**

Here's a concise write-up you can add to your coding instructions:

---

## **Closure and Arrow Function Usage Policy**

### **🎯 General Rule: Avoid Closures in Config Files**

Closures (anonymous functions) and arrow functions should **NOT** be used in configuration files (`src/App/Features/*/Config/*.php`, `src/Config/**/*.php`) to define business logic, presentation logic, or data transformations.

**Why?**
- ❌ Untestable (cannot mock, cannot unit test in isolation)
- ❌ Not reusable (tied to one specific config context)
- ❌ Violates SRP (mixes configuration with logic)
- ❌ No dependency injection (cannot inject services like `ThemeService`)

---




### **✅ When Closures/Arrow Functions ARE Allowed**

#### **1. Within Service/Controller Methods (Arrow Functions Preferred)**

Use **arrow functions** for simple, single-line transformations inside class methods:

````php
// ✅ GOOD: Arrow function for simple array transformation
public function getLabels(array $statuses): array
{
    return array_map(fn($status) => $status->label(), $statuses);
}

// ✅ GOOD: Arrow function with auto-capture of parent scope
$userId = $this->currentUser->getId();
$userRecords = array_filter($records, fn($record) => $record['user_id'] === $userId);
````

Use **traditional closures** when multi-line logic is needed:

````php
// ⚠️ OK: Traditional closure for multi-line logic
public function processRecords(array $records): array
{
    return array_map(function($record) {
        $this->logger->debug("Processing record: {$record['id']}");
        $record['processed_at'] = time();
        return $record;
    }, $records);
}
````

---

#### **2. Standard PHP Patterns (PSR-15 Middleware, Event Handlers)**

Closures are acceptable when they're part of established PHP patterns:

````php
// ✅ GOOD: PSR-15 middleware pattern (standard practice)
$middleware = function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
    // Pre-processing
    $response = $handler->handle($request);
    // Post-processing
    return $response;
};

// ✅ GOOD: Event handler registration
$eventDispatcher->addListener('user.created', function (UserCreatedEvent $event) {
    $this->logger->info("User created: {$event->getUser()->getEmail()}");
});
````

---

### **❌ When Closures/Arrow Functions Are FORBIDDEN**

#### **1. Configuration Files with Business/Presentation Logic**

````php
// ❌ BAD: Formatter closure in config file
// File: src/App/Features/Testy/Config/testy_fields.php
'status' => [
    'list' => [
        'formatter' => function ($value) { // ❌ FORBIDDEN
            $statusEnum = TestyStatus::tryFrom($value);
            return '<span class="badge">' . $statusEnum->label() . '</span>';
        },
    ],
],

// ✅ GOOD: Reference to service formatter instead
'status' => [
    'list' => [
        'formatters' => [
            [
                'name' => 'badge',
                'options_provider' => [TestyStatus::class, 'getFormatterOptions'],
            ],
        ],
    ],
],
````

---

#### **2. Database Queries (Must Be in Repository)**

````php
// ❌ BAD: Closure doing database work
$getActiveUsers = function () use ($db) {
    return $db->query("SELECT * FROM user WHERE status = 'active'");
};

// ✅ GOOD: Repository method
class UserRepository extends AbstractRepository
{
    public function findActiveUsers(): array
    {
        return $this->db->query("SELECT * FROM user WHERE status = 'active'");
    }
}
````

---

#### **3. Replacing Testable Service Classes**

````php
// ❌ BAD: Complex logic in closure (should be a service)
$calculateDiscount = function ($price, $userTier) {
    if ($userTier === 'premium') {
        return $price * 0.8;
    } elseif ($userTier === 'gold') {
        return $price * 0.9;
    }
    return $price;
};

// ✅ GOOD: Dedicated service class
class DiscountService
{
    public function calculateDiscount(float $price, string $userTier): float
    {
        return match ($userTier) {
            'premium' => $price * 0.8,
            'gold' => $price * 0.9,
            default => $price,
        };
    }
}
````

---

### **⚠️ EXCEPTION: Simple, One-Off Data Providers in Config**

**Only for truly simple, non-reusable option generation**, you may use an arrow function in config as a "data provider" (NOT for business/presentation logic):

````php
// ⚠️ ALLOWED (Exception): Simple data provider for formatter options
'status' => [
    'list' => [
        'formatters' => [
            [
                'name' => 'badge',
                'options' => fn($value) => ['label' => (string)$value, 'variant' => 'secondary'], // Simple fallback
            ],
        ],
    ],
],
````

**Criteria for this exception:**
1. ✅ Must be a **single-line arrow function**
2. ✅ Must **only provide data** (no HTML generation, no business logic)
3. ✅ Must be **truly non-reusable** (one-off case)
4. ✅ Must **not call external services** (no `ThemeService`, no `FieldRegistryService`)

**If any of these criteria are violated, use a static method or provider service instead:**

````php
// ✅ PREFERRED: Static method on enum (reusable, testable)
'options_provider' => [TestyStatus::class, 'getFormatterOptions'],
````

---

### **📊 Quick Decision Matrix**

| Situation | Use | Example |
|-----------|-----|---------|
| Simple array transformation in method | ✅ **Arrow function** | `array_map(fn($x) => $x * 2, $numbers)` |
| Multi-line logic in method | ⚠️ **Traditional closure** | Multi-statement `array_map()` |
| PSR-15 middleware | ⚠️ **Traditional closure** | Standard PHP pattern |
| Formatter/validator logic | ✅ **Service class** | `BadgeFormatter`, `EmailValidator` |
| Config field options (simple) | ⚠️ **Arrow function** (exception) | `fn($v) => ['label' => $v]` |
| Config field options (complex) | ✅ **Static method or Provider** | `[TestyStatus::class, 'getOptions']` |

---

### **🎯 Summary**

**Prefer arrow functions over closures** when working inside methods for simple transformations.

**Never use either in config files** unless it's a single-line arrow function providing simple data (and even then, prefer static methods).

**Always use service classes** for complex logic, formatters, validators, and anything that needs testing.

---
