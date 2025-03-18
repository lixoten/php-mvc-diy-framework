# MVC Migrations: Complete Reference Guide

# MVC Migrations System

A lightweight database migration system for your MVC application.

## Installation

The migration system comes included with your MVC framework.

## Basic Usage

### Running Migrations

```bash
# Run all pending migrations
php bin/console.php migrate

# Roll back the last batch of migrations
php bin/console.php rollback

# Roll back multiple batches
php bin/console.php rollback 3  # Rolls back 3 batches
```

### Creating Migrations

1. Create a new PHP file in Migrations folder
2. Name it using one of these patterns:
   - Simple: `CreateUsersTable.php`
   - Timestamped: `20250318000001_CreatePostsTable.php` (preferred for teams)

## Migration File Templates

### Basic Table Creation

```php
<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->drop('users');
    }
}
```

### Adding Columns to Existing Tables

```php
<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class AddProfileFieldsToUsers extends Migration
{
    public function up(): void
    {
        $this->alter('users', function ($table) {
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
        });
    }

    public function down(): void
    {
        $this->alter('users', function ($table) {
            $table->dropColumn('address');
            $table->dropColumn('phone');
            $table->dropColumn('birth_date');
        });
    }
}
```

## Schema Builder Reference

### Table Creation and Modification

```php
// Create a new table
$this->create('table_name', function ($table) {
    // Define columns here
});

// Drop a table
$this->drop('table_name');

// Alter an existing table
$this->alter('table_name', function ($table) {
    // Modify columns here
});
```

### Column Types

```php
$table->id();                               // Auto-incrementing primary key
$table->integer('count');                   // Integer column
$table->bigInteger('large_number');         // BIGINT column
$table->string('name', 100);                // VARCHAR with specified length
$table->text('description');                // TEXT column
$table->boolean('is_active');               // BOOLEAN column
$table->decimal('price', 8, 2);             // DECIMAL column (8 digits, 2 decimal places)
$table->datetime('occurred_at');            // DATETIME column
$table->timestamp('processed_at');          // TIMESTAMP column
$table->timestamps();                       // Creates created_at and updated_at columns
$table->date('birth_date');                 // DATE column
$table->time('start_time');                 // TIME column
$table->json('settings');                   // JSON column
$table->enum('status', ['active', 'inactive', 'pending']); // ENUM column
```

### Column Modifiers

```php
$table->string('email')->unique();          // Add UNIQUE constraint
$table->integer('age')->nullable();         // Allow NULL values
$table->string('status')->default('active');// Set default value
$table->integer('order')->unsigned();       // Make column UNSIGNED
$table->string('code')->comment('Tracking code for orders'); // Add comment
```

### Advanced Column Usage

```php
// Combined modifiers
$table->string('username', 50)
      ->unique()
      ->comment('User login name');

// Foreign keys
$table->integer('user_id')->unsigned();
$table->foreign('user_id')
      ->references('id')
      ->on('users')
      ->onDelete('cascade');

// Adding indexes
$table->index('last_name');
$table->index(['city', 'state']); // Compound index
```

## Advanced Features

### Raw SQL Execution

```php
public function up(): void
{
    // Execute raw SQL statements
    $this->db->execute("
        CREATE TABLE complex_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            json_data JSON,
            FULLTEXT INDEX (text_column)
        )
    ");
    
    // Run multiple statements
    $this->db->execute("INSERT INTO settings VALUES ('version', '1.0')");
}
```

### Data Migrations

```php
public function up(): void
{
    // First create the table
    $this->create('roles', function ($table) {
        $table->id();
        $table->string('name')->unique();
        $table->timestamps();
    });
    
    // Then seed it with initial data
    $this->db->execute("
        INSERT INTO roles (name, created_at) VALUES 
        ('admin', NOW()),
        ('user', NOW()),
        ('guest', NOW())
    ");
}
```

### Complex Schema Changes

```php
public function up(): void
{
    // Rename a table
    $this->db->execute("RENAME TABLE old_name TO new_name");
    
    // Copy data between tables
    $this->db->execute("
        INSERT INTO new_users (id, username, email)
        SELECT id, name, email FROM users
    ");
    
    // Add foreign key after table creation
    $this->db->execute("
        ALTER TABLE comments
        ADD CONSTRAINT fk_comments_posts
        FOREIGN KEY (post_id) REFERENCES posts(id)
        ON DELETE CASCADE
    ");
}
```

## How Migrations Work

1. Migration files are PHP classes with `up()` and `down()` methods
2. When you run `migrate`, the system:
   - Checks which migrations have already been run
   - Runs `up()` on each pending migration in order
   - Records which migrations have been applied
3. When you run `rollback`, the system:
   - Looks up which migrations were in the last batch
   - Runs `down()` on each of those migrations in reverse order
   - Removes records of those migrations

## Best Practices

1. **One Change Per Migration**: Keep migrations focused on a single logical change
2. **Always Define Down**: Make sure migrations can be reversed
3. **Use Timestamps**: If working in a team, use timestamp prefixes
4. **Test Both Ways**: Test both applying and rolling back your migrations
5. **Avoid Raw Column Drops**: If dropping columns in production, preserve data first
6. **Backup First**: Always back up production databases before migrations
7. **Keep Migrations Fast**: Avoid long-running migrations when possible
8. **Use Transactions**: Wrap complex migrations in transactions

## Troubleshooting

If migrations fail, check:

1. Database connection settings
2. Syntax errors in migration files
3. Missing migrations table (run `migrate` to create it)
4. Permissions issues (database user needs ALTER/CREATE privileges)

## Migration System Structure

- `MigrationRunner`: Manages execution of migrations
- `MigrationRepository`: Tracks which migrations have run
- `Migration`: Base class for all migrations
- `Schema/Blueprint`: Fluent API for table definitions
- `Schema/Column`: Individual column definitions

## Example: Complete User Management Schema

```php
<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateUserManagementSchema extends Migration
{
    public function up(): void
    {
        // Users table
        $this->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Roles table
        $this->create('roles', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        
        // Permissions table
        $this->create('permissions', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        
        // Role-User pivot table
        $this->create('role_user', function ($table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->primary(['user_id', 'role_id']);
            
            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
                
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
                
            $table->timestamps();
        });
        
        // Permission-Role pivot table
        $this->create('permission_role', function ($table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->primary(['permission_id', 'role_id']);
            
            // Foreign keys
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
                
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
                
            $table->timestamps();
        });
        
        // Seed initial roles
        $this->db->execute("
            INSERT INTO roles (name, description, created_at) VALUES
            ('admin', 'Full system access', NOW()),
            ('manager', 'Manage content and users', NOW()),
            ('user', 'Standard user access', NOW())
        ");
    }

    public function down(): void
    {
        // Drop tables in reverse order to avoid foreign key constraints
        $this->drop('permission_role');
        $this->drop('role_user');
        $this->drop('permissions');
        $this->drop('roles');
        $this->drop('users');
    }
}
```