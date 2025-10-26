<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('users')) {
            return;
        }

        $this->create('users', function ($table) {
            $table->bigIncrements('user_id');
            $table->string('username', 50)->unique();
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->text('roles')->nullable(); // Will store serialized array
            $table->char('status', 1)->default('P'); // P=Pending, A=Active, S=Suspended, B=Banned, D=Deleted
            $table->string('activation_token', 64)->nullable();
            $table->string('reset_token', 64)->nullable();
            $table->timestamp('reset_token_expiry')->nullable();
            $table->timestamps();

            // Add indexes for commonly searched fields
            $table->index('username');
            $table->index('email');
            $table->index('status');
            $table->index('activation_token');
            $table->index('reset_token');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('users');
    }
}
