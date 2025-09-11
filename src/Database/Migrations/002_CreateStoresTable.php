<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateStoresTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->create('stores', function ($table) {
            $table->bigIncrements('store_id');
            $table->bigInteger('store_user_id')->unsigned();              // Store owner
            $table->char('store_status', 1)->default('I');                // I=Inactive, A=Active, S=Suspended
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('theme', 50)->default('default');
            $table->timestamps();   // Creates both created_at and updated_at columns

            // Indexes
            $table->index('store_user_id');
            $table->index('slug');
            $table->index('store_status');

            // Foreign key to users
            $table->foreign(
                'store_user_id',      // Local column
                'users',        // Referenced table
                'user_id',      // Referenced column
                'fk_stores_users' // Constraint name
            )->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('stores');
    }
}
