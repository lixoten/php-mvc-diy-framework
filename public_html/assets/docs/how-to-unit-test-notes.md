# Unit Testing with PHPUnit in MVCLIXO

### Install PHPUnit via Composer

```bash
# Add PHPUnit as a dev dependency
composer require --dev phpunit/phpunit ^9.5
```

### Test File tree structure
* Tests mirror the src directory structure
* Each testable class should have a corresponding test class
* Tests are suffixed with "Test" (e.g., Logger -> LoggerTest)

```
├── Tests/
│   ├── App/
│   │   ├── Features/
│   │   │   ├── About/
│   │   │   │   └── AboutControllerTest.php
│   │
│   ├── Core/
│   │   ├── LoggerTest.php
```

### Writing New Tests
Each test class should:
* Extend \PHPUnit\Framework\TestCase
* Be named with the suffix "Test"
* Follow the same namespace as the class being tested, but with "Tests\" prefix

Example:
```php
<?php
namespace Tests\Core;

use Core\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testLoggerWritesMessages(): void
    {
        // Test code goes here
    }
}
```

### Test Naming Conventions

* Test methods should start with `test`
* Method names should clearly describe what is being tested
* Follow the pattern: `testShouldDoSomethingWhenSomethingElse`


### Create XML
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"

         colors="true"
         verbose="true">
    <testsuites>
        <testsuite name="MVCLIXO Test Suite">
            <directory>Tests</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </coverage>
</phpunit>
```

### Running Unit Test - ALL
```bash
# Will run all tests
vendor/bin/phpunit 

# Will run all tests with readable output
vendor/bin/phpunit --testdox
```

### Running Unit Test - SINGLE
```bash
# Will run single test
vendor/bin/phpunit Tests/Core/LoggerTest.php

# Will run single tests with readable output
vendor/bin/phpunit Tests/Core/LoggerTest.php --testdox
```

### Unit Test Output
```terminal
Runtime:       PHP 8.2.12
Configuration: D:\xampp\htdocs\mvclixo\phpunit.xml

About Controller (Tests\App\Features\About\AboutController)
 ✔ Index action outputs hello  37 ms
 ✔ Index action calls view with correct parameters  2 ms
hello
Flash Message Service (Tests\App\Services\FlashMessageService)
 ✔ Add flash message  3 ms
 ✔ Has flash message  2 ms
 ✔ Get clears messages  1 ms

Logger (Tests\Core\Logger)
 ✔ Logger writes messages  29 ms
 ✔ Logger interpolates context  18 ms
 ✔ Log level filtering  19 ms

Time: 00:00.133, Memory: 6.00 MB

OK (8 tests, 13 assertions)
```

