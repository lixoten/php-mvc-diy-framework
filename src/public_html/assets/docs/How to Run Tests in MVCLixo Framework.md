# **How to Run Tests in MVCLixo Framework**

This document explains how to run tests in the MVCLixo framework using PHPUnit. It covers running all tests, specific test files, and filtering tests by name or pattern.

---

## **1. Prerequisites**

Before running tests, ensure the following:
- PHPUnit is installed and configured in your project.
- The phpunit command is available in your terminal.
- Your test files are located in the Tests directory and follow the PSR-4 autoloading standard.

---

## **2. Running All Tests**

To run all tests in the project:
```bash
vendor/bin/phpunit
```

### **Output Example**
```plaintext
PHPUnit 9.x by Sebastian Bergmann and contributors.

Registration View (Tests\App\Features\Auth\Views\RegistrationViewTest)
 ✔ Registration form renders correctly
 ✔ Email verification success

User Entity (Tests\App\Entities\UserEntityTest)
 ✔ Password is hashed
 ✔ Activation token generation

Time: 00:00.123, Memory: 8.00 MB

OK (10 tests, 20 assertions)
```

---

## **3. Running a Specific Test File**

To run tests in a specific file, provide the file path:
```bash
vendor/bin/phpunit Tests/App/Features/Auth/Views/RegistrationViewTest.php
```

### **Output Example**
```plaintext
PHPUnit 9.x by Sebastian Bergmann and contributors.

Registration View (Tests\App\Features\Auth\Views\RegistrationViewTest)
 ✔ Registration form renders correctly
 ✔ Email verification success

Time: 00:00.045, Memory: 4.00 MB

OK (2 tests, 4 assertions)
```

---

## **4. Running a Specific Test Method**

To run a specific test method, use the `--filter` option with the method name:
```bash
vendor/bin/phpunit --filter testSuccessfulRegistrationFlow
```

### **Output Example**
```plaintext
PHPUnit 9.x by Sebastian Bergmann and contributors.

Registration View (Tests\App\Features\Auth\Views\RegistrationViewTest)
 ✔ Successful registration flow

Time: 00:00.012, Memory: 2.00 MB

OK (1 test, 3 assertions)
```

---

## **5. Filtering Tests by Name or Pattern**

To run tests that match a specific pattern in their class or method names, use the `--filter` option with a regular expression.

### **Example: Run Tests Matching "Registration"**
```bash
vendor/bin/phpunit --filter Registration
```

### **Example: Run Tests Matching "Registration" or "User"**
For **PowerShell**:
```bash
vendor/bin/phpunit --filter "Registration`|User"
```

For **Command Prompt (cmd)**:
```bash
vendor/bin/phpunit --filter "Registration^|User"
```

For **Linux/macOS**:
```bash
vendor/bin/phpunit --filter "Registration|User"
```

---

## **6. Running Tests with TestDox Output**

To display a more readable output format, use the `--testdox` option:
```bash
vendor/bin/phpunit --testdox
```

### **Output Example**
```plaintext
Registration View
 ✔ Registration form renders correctly
 ✔ Email verification success

User Entity
 ✔ Password is hashed
 ✔ Activation token generation
```

---

## **7. Generating Code Coverage Reports**

To generate a code coverage report, use the `--coverage-html` option:
```bash
vendor/bin/phpunit --coverage-html coverage
```

This will generate an HTML report in the `coverage` directory. Open `coverage/index.html` in your browser to view the report.

---

## **8. Debugging Tests**

If a test fails, PHPUnit will display the error message and the file/line where the failure occurred. Use this information to debug the issue.

### **Example: Failed Test Output**
```plaintext
1) Tests\App\Features\Auth\Views\RegistrationViewTest::testSuccessfulRegistrationFlow
Failed asserting that '<form>' contains '<input name="username">'.

Tests\App\Features\Auth\Views\RegistrationViewTest.php:45
```

---

## **9. Skipping Tests**

If a test is not ready or needs to be skipped, use the `markTestSkipped` method:
```php
public function testFeatureNotImplemented(): void
{
    $this->markTestSkipped('This feature is not implemented yet.');
}
```

---

## **10. Running Tests in a Specific Namespace**

To run all tests in a specific namespace, use the `--filter` option with the namespace:
```bash
vendor/bin/phpunit --filter "Tests\\App\\Features\\Auth"
```

---

## **11. Running Tests in Parallel**

If your PHPUnit version supports parallel testing, you can run tests faster:
```bash
vendor/bin/phpunit --parallel
```

---

## **12. Common Issues and Fixes**

### **Issue: `'User' is not recognized as an internal or external command`**
- This occurs when the `|` character is misinterpreted by the shell.
- Use the correct escaping for your shell:
  - PowerShell: `vendor/bin/phpunit --filter "Registration`|User"`
  - Command Prompt: `vendor/bin/phpunit --filter "Registration^|User"`

---

## **13. Summary of Commands**

| **Command**                                   | **Description**                                      |
|-----------------------------------------------|------------------------------------------------------|
| phpunit                          | Run all tests                                       |
| `vendor/bin/phpunit --filter Registration`    | Run tests matching "Registration"                  |
| `vendor/bin/phpunit --filter testMethodName`  | Run a specific test method                         |
| `vendor/bin/phpunit --testdox`                | Run tests with readable output                     |
| `vendor/bin/phpunit --coverage-html coverage` | Generate code coverage report                      |

---

By following this guide, you can efficiently run and manage your tests in the MVCLixo framework. Let me know if you need further assistance!