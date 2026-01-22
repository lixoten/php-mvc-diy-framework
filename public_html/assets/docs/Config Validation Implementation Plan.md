**Claude Sonnet 4.5**

---

# 🚀 **Config Validation Implementation Plan**

Based on your requirements (CLI-only validation, no runtime overhead, non-intrusive), here's the implementation plan:

---

## 📋 **Implementation Plan Overview**

### **Phase 1: Foundation (Core Exception & Schema)**
- Create exception class for config errors
- Create base schema class for validation rules

### **Phase 2: Schema Definitions**
- Define validation schemas for each config type
- Start with `model_bindings.php` (most critical)

### **Phase 3: Validator Service**
- Create validator service that uses schemas
- Implement validation logic

### **Phase 4: CLI Command**
- Create console command for manual validation
- Register command in console application

### **Phase 5: Environment Configuration**
- Add .env flag for optional validation
- Update `.env.example` with documentation

### **Phase 6: Integration**
- Register services in DI container
- Test validation with intentional errors

---

## 📊 **Detailed Phase Breakdown**

### **Phase 1: Foundation (2 files)**
1. ✅ ConfigurationException.php (already exists, may need enhancements)
2. ⚠️ **Review existing file** first, then decide if modifications needed

---

### **Phase 2: Schema Definitions (3 files)**
3. 🆕 `src/Core/Config/Schema/ModelBindingsSchema.php`
4. 🆕 `src/Core/Config/Schema/ListFieldsSchema.php`
5. 🆕 `src/Core/Config/Schema/FormFieldsSchema.php`

**Why this order?**
- `model_bindings.php` is the most complex and critical (you just built Model Binding)
- `list_fields` and `form_fields` follow similar patterns

---

### **Phase 3: Validator Service (1 file)**
6. 🆕 `src/Core/Services/ConfigValidatorService.php`

**Responsibilities:**
- Load config files
- Apply schema validation
- Return detailed error reports
- Support both full validation and single-file validation

---

### **Phase 4: CLI Command (1 file)**
7. 🆕 `src/Core/Console/Commands/ValidateConfigCommand.php`

**Usage:**
```bash
# Validate all config files
php bin/console.php config:validate

# Validate specific feature
php bin/console.php config:validate --feature=Testy

# Validate specific config type
php bin/console.php config:validate --type=model_bindings
```

---

### **Phase 5: Environment Configuration (1 file)**
8. ✏️ `.env.example` (add `CONFIG_VALIDATION_ENABLED` flag)

**Purpose:**
- Document the feature for other developers
- Provide toggle for future runtime validation (if needed)

---

### **Phase 6: Integration (2 files)**
9. ✏️ dependencies.php (register `ConfigValidatorService`)
10. ✏️ console.php (register `ValidateConfigCommand`)

---

## 🎯 **Implementation Order (File-by-File)**

| Step | Phase | File | Action | Complexity |
|------|-------|------|--------|-----------|
| **1** | Phase 1 | ConfigurationException.php | ⚠️ **Review & Enhance** | Low |
| **2** | Phase 2 | `src/Core/Config/Schema/ModelBindingsSchema.php` | 🆕 **Create** | High |
| **3** | Phase 2 | `src/Core/Config/Schema/ListFieldsSchema.php` | 🆕 **Create** | Medium |
| **4** | Phase 2 | `src/Core/Config/Schema/FormFieldsSchema.php` | 🆕 **Create** | Medium |
| **5** | Phase 3 | `src/Core/Services/ConfigValidatorService.php` | 🆕 **Create** | High |
| **6** | Phase 4 | `src/Core/Console/Commands/ValidateConfigCommand.php` | 🆕 **Create** | Medium |
| **7** | Phase 5 | `.env.example` | ✏️ **Modify** | Low |
| **8** | Phase 6 | dependencies.php | ✏️ **Modify** | Low |
| **9** | Phase 6 | console.php | ✏️ **Modify** | Low |

---

## 📝 **Detailed Breakdown by Phase**

### **Phase 1: Foundation**

#### **Step 1: Review ConfigurationException.php**

**What we need to check:**
- ✅ Does it have `$configFile` property?
- ✅ Does it have `$entityType` property?
- ✅ Does it have `$suggestion` property?
- ✅ Does it have getters for these properties?

**If missing, we'll enhance it.**

**Current file:** Already exists at ConfigurationException.php

---

### **Phase 2: Schema Definitions**

#### **Step 2: Create `ModelBindingsSchema.php`**

**Purpose:** Define expected structure for `model_bindings.php` files

**Validation Rules:**
- `actions` → array (required)
- `actions.{actionName}` → array (e.g., `editAction`, `viewAction`)
- `actions.{actionName}.{paramName}` → array (e.g., `testy`)
- `repository` → string (required, must be valid class)
- `method` → string (optional, default: `findById`)
- `parameter_name` → string (optional, default: `id`)
- `fields` → array of strings (optional)
- `authorization` → array (optional)
- `authorization.check` → bool (optional)
- `authorization.owner_field` → string (optional, default: `user_id`)
- `authorization.store_field` → string (optional, default: `store_id`)
- `authorization.allowed_roles` → array of strings (optional)

**Business Rules:**
- Parameter name (`testy`) should match feature name
- Repository class must exist and implement `RepositoryInterface`
- Method must exist on repository class

---

#### **Step 3: Create `ListFieldsSchema.php`**

**Purpose:** Define expected structure for `{entity}_list_fields.php` files

**Validation Rules:**
- `{field_name}` → array
- `label` → string (optional)
- `list` → array (optional)
- `list.sortable` → bool (optional)
- `list.formatter` → closure OR array (optional)
- `form` → array (optional)

**Business Rules:**
- Fields must match database columns (we can add this check later)

---

#### **Step 4: Create `FormFieldsSchema.php`**

**Purpose:** Define expected structure for `{entity}_fields.php` files

**Validation Rules:**
- Similar to `ListFieldsSchema` but for form-specific configs

---

### **Phase 3: Validator Service**

#### **Step 5: Create `ConfigValidatorService.php`**

**Responsibilities:**
- Load config files by absolute path
- Apply schema validation
- Collect all errors (don't stop at first error)
- Return structured validation report

**Methods:**
```
validateFeatureConfig(string $featureName, string $configType): ValidationResult
validateAllFeatures(): array<ValidationResult>
validateConfigFile(string $absolutePath, SchemaInterface $schema): ValidationResult
```

---

### **Phase 4: CLI Command**

#### **Step 6: Create `ValidateConfigCommand.php`**

**Command Name:** `config:validate`

**Options:**
- `--feature=Testy` → Validate only one feature
- `--type=model_bindings` → Validate only one config type
- `--verbose` → Show detailed error messages

**Output:**
```
✅ src/App/Features/Testy/Config/model_bindings.php - VALID
❌ src/App/Features/Post/Config/model_bindings.php - INVALID
   - Line 12: Config key 'postxx' does not match feature name 'Post'
   - Line 15: Repository 'App\Features\Post\NonExistentRepository' not found
```

---

### **Phase 5: Environment Configuration**

#### **Step 7: Update `.env.example`**

**Add:**
```env
# ===========================================
# Config Validation
# ===========================================
# Enable config validation during development
# Recommended: true for development, false for production
CONFIG_VALIDATION_ENABLED=true
```

---

### **Phase 6: Integration**

#### **Step 8: Update dependencies.php**

**Add:**
```php
// Config Validator Service
\Core\Services\ConfigValidatorService::class => \DI\autowire()
    ->constructorParameter('configService', \DI\get(\Core\Interfaces\ConfigInterface::class))
    ->constructorParameter('logger', \DI\get(\Psr\Log\LoggerInterface::class)),
```

---

#### **Step 9: Update console.php**

**Add:**
```php
use Core\Console\Commands\ValidateConfigCommand;

// ... existing code ...

$application->add($container->get(ValidateConfigCommand::class));
```

---

## 🎯 **Testing Strategy**

### **Test Plan:**

1. **Create intentional errors in `model_bindings.php`:**
   - Wrong parameter name (`'testyxx'` instead of `'testy'`)
   - Non-existent repository class
   - Missing required keys

2. **Run validation command:**
   ```bash
   php bin/console.php config:validate --feature=Testy
   ```

3. **Verify error messages are helpful:**
   - Show file path
   - Show line number (if possible)
   - Show suggested fix

4. **Fix errors and re-run:**
   - Verify `✅ VALID` output

---

## 🚀 **Ready to Start?**

**Let's begin with Step 1: Review ConfigurationException.php**

**Your existing file already has:**
- ✅ `$configFile` property
- ✅ `$entityType` property
- ✅ `$suggestion` property
- ✅ Getters for all properties

**This file is already perfect for our needs! ✅**

---

## 🎯 **Next Step: Create `ModelBindingsSchema.php`**

**Shall I proceed with Step 2?** I'll show you the full implementation for `src/Core/Config/Schema/ModelBindingsSchema.php`.

**This will be the most complex schema (high complexity), so once we nail this one, the others will be easier.**

---

**Reply with:**
- ✅ **"Yes, show me `ModelBindingsSchema.php`"** → I'll create the full file
- ⚠️ **"Wait, I have questions about the plan"** → Ask away
- 🛑 **"Let's adjust the plan"** → Tell me what you want to change

**What's your call?** 🚀