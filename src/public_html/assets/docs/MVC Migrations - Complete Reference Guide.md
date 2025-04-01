# Migration System Documentation

## Introduction
The migration system provides a way to version-control your database schema. It lets you evolve your application's database structure while maintaining the ability to recreate or roll back changes.

## Core Components

- **MigrationRunner**: Discovers and executes migrations
- **MigrationRepository**: Tracks executed migrations in the database
- **Blueprint**: Defines table structure (columns, indexes, etc.)
- **SchemaBuilder**: Converts schemas to SQL statements

## Available Commands

### Run Migrations
```bash
# Run all pending migrations
php bin/console.php migrate

# Run migrations forcefully (even if already executed)
php bin/console.php migrate --force
```

### Roll Back Migrations
```bash
# Roll back the most recent batch
php bin/console.php rollback

# Roll back multiple batches
php bin/console.php rollback 3
```

## Creating New Migrations

1. Create a PHP file in Migrations with the naming pattern `CreateXxxTable.php`
2. Extend the `Core\Database\Migrations\Migration` class
3. Implement `up()` and `down()` methods

Example:
```php
<?php

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        $this->create('products', function ($table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->drop('products');
    }
}
```

## Available Column Types

Your schema builder supports these column types:

| Method | Description |
|--------|-------------|
| `$table->id()` | Auto-incrementing BIGINT primary key |
| `$table->bigIncrements(string $name)` | Auto-incrementing BIGINT primary key with custom name |
| `$table->string(string $name, int $length = 255)` | VARCHAR column |
| `$table->integer(string $name)` | INTEGER column |
| `$table->bigInteger(string $name)` | BIGINT column |
| `$table->tinyInteger(string $name)` | TINYINT column |
| `$table->text(string $name)` | TEXT column |
| `$table->char(string $name, int $length = 1)` | CHAR column |
| `$table->boolean(string $name)` | TINYINT(1) column |
| `$table->float(string $name, int $precision, int $scale)` | FLOAT column |
| `$table->decimal(string $name, int $precision, int $scale)` | DECIMAL column |
| `$table->timestamp(string $name)` | TIMESTAMP column |
| `$table->date(string $name)` | DATE column |
| `$table->dateTime(string $name)` | DATETIME column |
| `$table->time(string $name)` | TIME column |
| `$table->json(string $name)` | JSON column |
| `$table->enum(string $name, array $values)` | ENUM column |
| `$table->timestamps()` | Add created_at and updated_at columns |

## Column Modifiers

Apply these after declaring a column:

```php
$table->string('email')->unique();         // Add unique constraint
$table->integer('views')->default(0);      // Set default value
$table->string('address')->nullable();     // Allow NULL values
$table->text('notes')->comment('User notes'); // Add comment
```

## Understanding the --force Flag

The `--force` flag allows you to re-run migrations even if they're already marked as executed in the database:

```bash
php bin/console.php migrate --force
```

When to use:
- After manually dropping tables that need to be recreated
- When a migration needs to be applied again after changes
- For development environments when testing schema changes

How it works:
- Without `--force`: Only runs migrations not recorded in the migrations table
- With `--force`: Runs all migrations regardless of execution history

## Rollback Behavior

Rolling back migrations:
- Uses the `down()` method in each migration
- Processes migrations in reverse order
- Removes entries from the migrations table
- Deletes database schema objects (tables, columns)

## System Requirements

To use migrations, ensure:

1. Database connection is properly configured
2. PHP PDO extension is enabled
3. Proper directory structure exists:
   - Migrations for migration files
   - Migrations for migration system
   - Schema for schema definition

## Cleanup Recommendations

For future maintenance:
- Remove unused `xxx` prefixed methods (xxxrun, xxxrunMigration)
- Add optional verbosity levels for debugging output
- Use consistent method naming (log vs. logMigration)

The migration system follows industry best practices while maintaining a clean architecture specific to your MVCLIXO framework.

Similar code found with 1 license type