# **🛠️ Decision Guide: PHP Enums vs. CodeLookupService**

This document helps you choose the most appropriate pattern for handling fixed sets of "codes" in your MVC LIXO framework, balancing type safety, encapsulated behavior, and avoiding boilerplate.

---

### **🎯 Core Principle: Encapsulate Behavior vs. Store Data**

The fundamental difference lies in whether the code's values (e.g., 'P' for Pending) primarily represent:

1.  **Rich Behavior & Unique Logic** ➡️ **Choose PHP Enum**
2.  **Simple Data & Display Properties** ➡️ **Choose CodeLookupService**

---

### **✅ When to Choose a PHP Enum Class (e.g., `TestyStatus` enum)**

Choose a PHP `enum` class when your codes represent a **small, finite, and stable set of values** that carry **significant, unique, and encapsulated *behavior* or *properties*** directly tied to each case.

| Icon | Criteria | Why it's a good fit for Enums | Example Methods |
| :--- | :------- | :----------------------------- | :-------------- |
| 🔢 | **Small, Finite Set:** Typically less than ~50 values. | The overhead of a dedicated PHP class is justified for a manageable number of distinct items. | `case PENDING = 'P';` |
| 🧠 | **Rich, Unique Behavior:** Each case needs distinct PHP logic or methods. | Enums excel at encapsulating rules, state transitions, or specific calculations directly within the value itself. | `TestyStatus::PENDING->canTransitionTo(self::ACTIVE): bool` |
| 🏷️ | **Complex Display Properties:** Beyond just a label, a case has specific UI attributes that differ. | Methods can dynamically provide different badge variants, icons, or accessibility attributes based on the enum case. | `TestyStatus::PENDING->badgeVariant(): string` |
| 🛡️ | **Strong Type Safety in Code:** You want to avoid "magic strings" in your PHP logic. | Using `TestyStatus::PENDING` provides compile-time checks and IDE auto-completion. | `if ($order->status === OrderStatus::PENDING)` |
| 🔄 | **State Transitions / Business Rules:** The values are part of a workflow with defined transitions. | Enums are perfect for defining and enforcing valid state changes. | `OrderStatus::PENDING->isRefundable(): bool` |

**Common Examples that often justify an Enum:**

*   **`TestyStatus`:** (e.g., P, A, S, B, D) - Has `label()`, `badgeVariant()`, potentially `canTransitionTo()`.
*   **`HttpMethod`:** (e.g., GET, POST, PUT) - Has `isSafe()`, `isIdempotent()`, `allowsBody()`.
*   **`UserRole`:** (e.g., ADMIN, EDITOR, GUEST) - Has `can(Permission $p)`, `isAdmin()`, `getDashboardRoute()`.
*   **`OrderStatus`:** (e.g., PENDING, SHIPPED, CANCELED) - Has `canTransitionTo()`, `isFinal()`, `isRefundable()`.
*   **`Currency`:** (e.g., USD, EUR, JPY) - Has `symbol()`, `decimalPlaces()`, `format(float $amount)`.

---

### **➡️ When to Choose the `CodeLookupService` Pattern**

Choose the `CodeLookupService` pattern (a single generic service reading from a central config file) when your codes primarily need **simple data lookup, display properties, and validation**, and they **do not require unique, complex PHP behavior** for each individual code.

| Icon | Criteria | Why it's a good fit for CodeLookupService | Config Example (app_lookups.php) |
| :--- | :------- | :---------------------------------------- | :------------------------------- |
| 📈 | **Large or Numerous Groups:** Many codes in one group (e.g., 50+ states), or many *different small groups* (e.g., 5-10 items per group, but dozens of groups). | Avoids a proliferation of PHP class files (boilerplate). All definitions live as data in one central config. | `'us_states' => ['AL' => ['label' => 'states.alabama'], ...]` |
| ℹ️ | **Data-Centric, Simple Properties:** Primarily needs a label, perhaps a generic icon, a hex code, or a simple display variant. | Focuses on storing and retrieving data, not encapsulating complex logic. | `'gender' => ['m' => ['label' => 'gender.male', 'variant' => 'info'], ...]` |
| 🔁 | **Generic Behavior:** The logic applied to these codes is mostly generic (e.g., "get its label", "get all options"). | The *single* `CodeLookupService` provides all these generic methods, reusing them across all code types. | `codeLookupService->getLabel('gender', 'f')` |
| 🔄 | **Potentially Dynamic/Evolving Data:** If the list might change frequently or eventually be admin-managed. | Easier to update a config file (or eventually a database) than modify and deploy new enum classes. | `['payment_type' => ['CC' => ['label' => 'payment.credit_card'], ...]]` |
| 🧱 | **Avoids Boilerplate:** You don't want a new PHP class for every trivial code list. | This is the core benefit – *one* generic service, *one* central config file for all these lookup needs. | (No new class file for 'gender' or 'payment_type') |

**Common Examples that often justify the `CodeLookupService`:**

*   **US States/Countries:** (e.g., AL, CA, US) - Primarily display names, perhaps a region. No unique complex behavior per state.
*   **Gender Identifiers:** (e.g., m, f, o, nb) - Primarily labels and simple display variants. Minimal unique behavior.
*   **Payment Types:** (e.g., CC, PP, INV) - Primarily labels, maybe a logo/icon path.
*   **Delivery Methods:** (e.g., STD, EXP, PUP) - Labels, perhaps an estimated time (calculated by a service, not the code itself).
*   **Notification Frequencies:** (e.g., D, W, M, N) - Labels, simple mapping.
*   **Simple Icon Types/Theme Colors:** (e.g., CAL, USR; RED, BLUE) - Labels, hex codes, SVG paths.
*   **Medical Codes/Product Categories:** Often thousands of entries, primarily needing lookups, not complex PHP behavior on each code.

---

### **📊 Quick Decision Matrix**

| Question                                            | If YES, Consider... | If NO (or generic), Consider... |
| :-------------------------------------------------- | :------------------ | :------------------------------ |
| **Does each code need *unique, complex PHP logic*?** | ✅ **PHP Enum**     | ➡️ **CodeLookupService**          |
| **Is the code group small (<~50) AND stable?**      | ✅ **PHP Enum**     | ➡️ **CodeLookupService**          |
| **Do I want type safety (`Enum::CASE`) in my code?** | ✅ **PHP Enum**     | ➡️ **CodeLookupService**          |
| **Is the primary need just lookup/display data?**    | ➡️ **CodeLookupService** | (Leans away from Enum)        |
| **Do I have many *similar small lists* of codes?**  | ➡️ **CodeLookupService** | (Leans away from Enum)        |

---

By using this guide, you can leverage the powerful type safety and behavior encapsulation of PHP enums where they truly add value, while efficiently managing simpler and more numerous code sets with the scalable, data-driven `CodeLookupService` pattern. This creates a flexible and maintainable architecture for your framework.