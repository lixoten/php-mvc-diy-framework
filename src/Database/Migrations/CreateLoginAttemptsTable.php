<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateLoginAttemptsTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->create('login_attempts', function ($table) {
            $table->bigIncrements('id');
            $table->string('username_or_email', 255);
            $table->string('ip_address', 45); // Support IPv6 addresses
            $table->dateTime('attempted_at');
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            // Add indexes for faster lookups
            $table->index('username_or_email');
            $table->index('ip_address');
            $table->index('attempted_at');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('login_attempts');
    }
}
