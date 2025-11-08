<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;
use Core\Database\Schema\Blueprint;

class CreateRememberTokenTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('remember_token')) {
            return;
        }

        $this->create('remember_token', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned()->nullable(false); 
            $table->string('selector', 64);
            $table->string('hashed_validator', 64);
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index('selector');

            // Fixed: Using the correct parameter order for foreign()
            $table->foreign(
                'user_id',      // Local column
                'user',         // Referenced table
                'id',           // Referenced column
                'fk_remember_token_user' // Constraint name
            );
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('remember_token');
    }
}
