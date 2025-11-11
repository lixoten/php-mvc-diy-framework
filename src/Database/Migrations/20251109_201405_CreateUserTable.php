<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;
use Core\Database\Schema\Blueprint;

/**
 * Generated File - Date: 20251109_201405
 * Migration for creating the 'user' table.
 */
class CreateUserTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('user')) {
            return;
        }

        $this->create('user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 50)
                    ->nullable(false)
                    ->comment('Unique username for login');
            $table->string('email', 255)
                    ->nullable(false)
                    ->comment('Unique email address for login and communication');
            $table->string('password_hash', 255)
                    ->nullable(false)
                    ->comment('Hashed password for user authentication');
            $table->json('roles')
                    ->nullable(false)
                    ->comment('JSON encoded array of user roles/permissions');
            $table->char('status', 1)
                    ->default('A')
                    ->comment('P=Pending, A=Active, S=Suspended, B=Banned, D=Deleted');
            $table->string('activation_token', 64)
                    ->nullable()
                    ->comment('Token for account activation');
            $table->string('reset_token', 64)
                    ->nullable()
                    ->comment('Token for password reset');
            $table->timestamp('reset_token_expiry')
                    ->nullable()
                    ->comment('Expiry time for password reset token');
            $table->boolean('is_green')
                    ->nullable(false)
                    ->default(false)
                    ->comment('Is Green');
            $table->boolean('is_blue')
                    ->nullable(false)
                    ->default(false)
                    ->comment('Is Blue');
            $table->boolean('is_red')
                    ->nullable(false)
                    ->default(false)
                    ->comment('Is Red');
            $table->string('generic_code')
                    ->nullable(false)
                    ->comment('Generic Code');
            $table->dateTime('created_at')
                    ->nullable(false)
                    ->comment('Timestamp when the record was created');
            $table->dateTime('updated_at')
                    ->nullable(false)
                    ->comment('Timestamp when the record was last updated');

            // CHECK Constraints
            $table->check('status IN (\'P\',\'A\',\'S\',\'B\',\'D\')', 'chk_user_status');

            // Indexes
            $table->unique('username');
            $table->unique('email');
            $table->index(['username'], 'idx_user_username');
            $table->index(['email'], 'idx_user_email');
            $table->index(['status'], 'idx_user_status');
            $table->index(['activation_token'], 'idx_user_activation_token');
            $table->index(['reset_token'], 'idx_user_reset_token');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        $this->drop('user');
    }
}
