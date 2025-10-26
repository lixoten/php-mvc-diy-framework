<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateRememberTokensTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('remember_tokens')) {
            return;
        }

        $this->create('remember_tokens', function ($table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('selector', 64);
            $table->string('hashed_validator', 64);
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index('selector');

            // Fixed: Using the correct parameter order for foreign()
            $table->foreign(
                'user_id',      // Local column
                'users',        // Referenced table
                'user_id',      // Referenced column
                'fk_remember_tokens_users' // Constraint name
            );
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('remember_tokens');
    }
}
