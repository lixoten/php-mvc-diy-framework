# Strategy Pattern Implementation Plan for MVC LIXO Framework

## Overview
--- Strategy Pattern 
=====================
--- FormatterInterface: - Strategy Contract
--- Concrete Strategies: (The Formatter Classes):  - PhoneNumberFormatter, DateFormatter, BadgeFormatter, CurrencyFormatter, ETC... Each of these classes implements the FormatterInterface
--- FormatterRegistry: -  would store instances of your FormatterClasses, not just their names.
--- Context (The Service): FormatterService


so something like this:
// 1. Create instances of your formatter classes
$phoneNumberFormatter = new PhoneNumberFormatter();
$uppercaseFormatter = new UppercaseFormatter();

// 2. Register these instances with the registry
$registry = new FormatterRegistry();
$registry->set('tel', $phoneNumberFormatter);
$registry->set('uppercase', $uppercaseFormatter);

// 3. The service can now retrieve and use them
$formatterService = new FormatterService($registry);
$formattedPhone = $formatterService->format('tel', '1234567890');



## Phase 1: Core Strategy Components

### 1.1 FormatterInterface (Strategy Contract)
- **Location:** `src/Core/Formatters/FormatterInterface.php`
- **Purpose:** Define the contract all formatters must implement
- **Methods:**
  - `format(mixed $value, array $options = []): string`
  - `getName(): string`
  - `supports(mixed $value): bool` (for type checking)

### 1.2 FormatterRegistry (Strategy Storage)
- **Location:** `src/Core/Formatters/FormatterRegistry.php`
- **Purpose:** Store and retrieve formatter instances
- **Features:**
  - Instance storage (not just names)
  - Lazy loading support
  - Validation of formatter types

### 1.3 FormatterService (Context)
- **Location:** `src/Core/Services/FormatterService.php`
- **Purpose:** Main entry point for formatting operations
- **Responsibilities:**
  - Coordinate with registry
  - Handle fallbacks
  - Manage caching

## Phase 2: Concrete Formatter Strategies

### 2.1 Built-in Formatters
- **TextFormatter** - Basic text formatting with truncation
- **DateFormatter** - Date/time formatting with timezone support
- **CurrencyFormatter** - Money formatting with locale
- **PhoneNumberFormatter** - Phone number standardization
- **BadgeFormatter** - HTML badge generation
- **NumberFormatter** - Numeric formatting (decimals, percentages)
- **UppercaseFormatter** - Text case transformations
- **EmailFormatter** - Email obfuscation/display

### 2.2 Formatter Organization
- **Location:** `src/Core/Formatters/Concrete/`
- **Naming:** `{Purpose}Formatter.php`
- **Registration:** Auto-discovery or explicit registration

## Phase 3: Integration with Existing Architecture

### 3.1 FieldRegistryService Integration
- **Enhance existing service** to work with FormatterService
- **Maintain backward compatibility** with formatter closures
- **Gradual migration path** from closures to strategy classes

### 3.2 Configuration Integration
- **Current config structure:** Keep existing field config format
- **Formatter options:** Support both string names and configuration arrays
- **Fallback hierarchy:** Maintain your existing layered config system

## Phase 4: Advanced Features & Concerns

### 4.1 Handling Missing Formatters
**Strategies:**
- **Graceful degradation:** Fall back to `TextFormatter` when formatter not found
- **Error modes:** Configurable (silent, warning, exception)
- **Default formatter:** Always available baseline formatter
- **Validation:** Check formatter availability during config loading

### 4.2 Dependency Injection Integration
**Container Registration:**
- **FormatterRegistry:** Singleton with all formatters pre-registered
- **FormatterService:** Singleton depending on registry
- **Lazy loading:** Formatters instantiated only when first used
- **Factory pattern:** For formatters requiring dependencies

### 4.3 Caching Strategy ⚡️
**Multi-level caching:**
- **Instance caching:** Formatter objects cached in registry
- **Result caching:** Formatted values cached by input hash
- **Config caching:** Parsed formatter configurations cached
- **Memory management:** LRU eviction for result cache

### 4.4 Type Safety
**PHP 8.2+ Features:**
- **Strict typing:** `declare(strict_types=1)`
- **Union types:** Support for multiple input types
- **Generics in PHPDoc:** `array<string, FormatterInterface>`
- **Return type declarations:** Ensure string returns
- **Parameter validation:** Runtime type checking

### 4.5 Configuration Integration
**Seamless integration:**
- **Existing format:** Keep current field config structure
- **Formatter resolution:** String name → registry lookup
- **Options passing:** Merge field options with formatter defaults
- **Environment-specific:** Different formatters per environment

## Phase 5: Migration & Fallback Strategy

### 5.1 Backward Compatibility
- **Closure detection:** Check if formatter is callable
- **Hybrid support:** Support both closures and strategy names
- **Migration helpers:** Tools to convert closures to strategies
- **Deprecation path:** Gradual phase-out of closures

### 5.2 Fallback Hierarchy
**When formatter not found:**
1. **Try alternative names** (aliases)
2. **Fall back to base formatter** (text)
3. **Log warning** (development mode)
4. **Continue gracefully** (production mode)

### 5.3 Error Handling
- **FormatterNotFoundException:** Custom exception
- **InvalidFormatterException:** For type mismatches
- **FormattingException:** For runtime formatting errors
- **Graceful degradation:** Never break the page

## Phase 6: Testing & Quality Assurance

### 6.1 Unit Testing
- **Formatter tests:** Each formatter thoroughly tested
- **Registry tests:** Instance management and retrieval
- **Service tests:** Integration and fallback scenarios
- **Mock support:** Easy mocking for dependent services

### 6.2 Integration Testing
- **Config integration:** Test with real field configurations
- **Performance testing:** Benchmark against closure approach
- **Memory testing:** Ensure no memory leaks in caching

## Phase 7: Developer Experience

### 7.1 Documentation
- **Formatter creation guide:** How to build custom formatters
- **Migration guide:** Converting from closures
- **Configuration reference:** All available options
- **Performance tips:** Best practices for caching

### 7.2 Developer Tools
- **Formatter listing:** CLI command to see all available formatters
- **Config validation:** Check formatter availability in configs
- **Debug mode:** Detailed logging of formatter resolution

## Implementation Priority

### High Priority (Core functionality)
1. FormatterInterface and basic concrete formatters
2. FormatterRegistry with instance storage
3. FormatterService with fallback support
4. Integration with FieldRegistryService

### Medium Priority (Production readiness)
1. Comprehensive error handling
2. Caching implementation
3. Container integration
4. Migration tools

### Low Priority (Enhancement)
1. Advanced caching strategies
2. Performance optimizations
3. Developer tools
4. Extended formatter library

This plan ensures your Strategy pattern implementation respects your framework's SOLID principles, maintains the existing field registry system, and provides a smooth migration path from formatter closures while addressing all the technical concerns around fallbacks, caching, and type safety.