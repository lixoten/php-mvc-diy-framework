- [[#Introduction|Introduction]]
- [[#Core Components|Core Components]]
- [[#Available Commands|Available Commands]]
	- [[#Available Commands#Run Migrations|Run Migrations]]
	- [[#Available Commands#Run Migrations SEEDER|Run Migrations SEEDER]]
	- [[#Available Commands#Run check the foreign key constraints for the xxxx table|Run check the foreign key constraints for the xxxx table]]
	- [[#Available Commands#Roll Back Migrations|Roll Back Migrations]]
- [[#Creating New Migrations|Creating New Migrations]]
- [[#Available Column Types|Available Column Types]]
- [[#Column Modifiers|Column Modifiers]]
- [[#Foreign Key Constraints|Foreign Key Constraints]]
- [[#Rollback Behavior|Rollback Behavior]]
- [[#System Requirements|System Requirements]]
- [[#Cleanup Recommendations|Cleanup Recommendations]]
- [[#Quick Cheat Run Migrations|Quick Cheat Run Migrations]]


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
# XDEBUG-Run console.php migrate in XDEBUG
# Steps
# -- Open in tab, Then `menu > Run > Start Debugging
# -- Run command below in terminal.
php -dxdebug.mode=debug -dxdebug.start_with_request=yes bin/console.php migrate

# Run all pending migrations
php bin/console.php migrate


# Force re-run all migrations
php bin/console.php migrate --force
```
### Run Migrations SEEDER
```bash
# Show all available seeders
php bin/console.php seed

# Run specific seeder
php bin/console.php seed UsersSeeder

# Run ALL seeders at once
php bin/console.php seed --all
```
### Run check the foreign key constraints for a specific table
```bash
# constraints for the xxxx table. Specific table
php bin/console.php show:fk posts
php bin/console.php show:fk users

# constraints for all tables.
php bin/console.php show:fk
```
### Roll Back Migrations
```bash
# Roll back the most recent batch
php bin/console.php rollback

# Roll back multiple batches
php bin/console.php rollback 3

# So to rollback and rerun
php bin/console.php rollback
php bin/console.php migrate
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

## Foreign Key Constraints

Add foreign key relationships between tables:

```php
// Basic foreign key
$table->foreign('user_id')->references('id')->on('users');

// With custom name and delete behavior
$table->foreign('post_user_id', 'users', 'user_id', 'fk_posts_users')
      ->onDelete('CASCADE');

// Available referential actions
->onDelete('CASCADE')    // Delete related records when parent is deleted
->onDelete('SET NULL')   // Set foreign key to NULL when parent is deleted
->onDelete('RESTRICT')   // Prevent deletion of parent if child records exist
->onDelete('NO ACTION')  // Similar to RESTRICT

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


## Quick Cheat Run Migrations
```bash
# XDEBUG-Run console.php migrate in XDEBUG
# Steps
# -- Open in tab, Then `menu > Run > Start Debugging
# -- Run command below in terminal.
php -dxdebug.mode=debug -dxdebug.start_with_request=yes bin/console.php migrate

# Migration Commands:
php bin/console.php migrate                # Run all pending migrations
php bin/console.php migrate:one 'Database\Migrations\CreateTestysTable' --force # Recreate a Single Migration
php bin/console.php migrate --force        # Force run migrations (even if they might have been run before)
php bin/console.php rollback               # Roll back the most recent batch of migrations
php bin/console.php rollback 3             # Roll back the 3 most recent batches
php bin/console.php seed AlbumsSeeder      # Run the AlbumsSeeder
php bin/console.php show:fk albums         # Show foreign keys for the albums table
php bin/console.php help                   # Display help information

# Table Data Seeder
php bin/console.php seed PostsSeeder
```
