<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;
use Core\Database\Schema\Blueprint; // Added for type hinting

/**
 * Generated File - Date: 20251102_084229
 * Migration for creating the 'store' table.
 */
class CreateStoreTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('store')) {
            return;
        }

        $this->create('store', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')
                    ->nullable(false)
                    ->unsigned()
                    ->comment('User who owns this store');
            $table->char('status', 1)
                    ->nullable(false)
                    ->default('I')
                    ->comment('I=Inactive, A=Active, S=Suspended');
            $table->string('slug', 50)
                    ->nullable(false)
                    ->comment('Unique SEO-friendly slug for the store');
            $table->string('name', 100)
                    ->nullable(false)
                    ->comment('Name of the store');
            $table->text('description')
                    ->nullable()
                    ->comment('Description of the store');
            $table->string('theme', 50)
                    ->nullable(false)
                    ->default('default')
                    ->comment('Theme used by the store');
            $table->timestamps();

            // CHECK Constraints
            $table->check('status IN (\'I\',\'A\',\'S\')', 'chk_status');

            // Foreign Keys
            $table->foreign('user_id', 'user', 'id', 'fk_store_user')
                ->onDelete('CASCADE')
                ;

            // Indexes
            $table->unique('slug');
            $table->index(['user_id'], 'idx_user_id');
            $table->index(['slug'], 'idx_slug');
            $table->index(['status'], 'idx_status');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        $this->drop('store');
    }
}
