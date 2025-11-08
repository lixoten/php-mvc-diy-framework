<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateRateLimitAttemptsTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('rate_limit_attempts')) {
            return;
        }

        $this->create('rate_limit_attempts', function ($table) {
            $table->bigIncrements('id');
            $table->string('identifier', 255);     // Email, username, form name, etc.
            $table->string('action_type', 50);     // login, password_reset, registration, etc.
            $table->string('ip_address', 45);      // Support for IPv6
            $table->tinyInteger('success')->default(0);
            $table->dateTime('attempted_at');
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            // Add indexes for frequent queries
            $table->index('identifier');
            $table->index('action_type');
            $table->index('ip_address');
            $table->index('attempted_at');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('rate_limit_attempts');
    }
}
