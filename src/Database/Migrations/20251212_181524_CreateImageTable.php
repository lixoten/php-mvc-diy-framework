<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;
use Core\Database\Schema\Blueprint;

/**
 * Generated File - Date: 20251212_181524
 * Migration for creating the 'image' table.
 */
class CreateImageTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('image')) {
            return;
        }

        $this->create('image', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')
                    ->nullable()
                    ->comment('Store this record belongs to');
            $table->foreignId('user_id')
                    ->nullable(false)
                    ->comment('User who created the record');
            $table->char('status', 1)
                    ->default('a')
                    ->comment('Status');
            $table->string('slug', 100)
                    ->nullable(false)
                    ->comment('Unique SEO-friendly slug for the image');
            $table->string('title', 255)
                    ->nullable(false)
                    ->comment('Title');
            $table->string('generic_text', 60)
                    ->nullable()
                    ->comment('Generic text');
            $table->dateTime('created_at')
                    ->nullable(false)
                    ->comment('Created Date');
            $table->dateTime('updated_at')
                    ->nullable(false)
                    ->comment('Last update');

            // CHECK Constraints
            $table->check('status IN (\'j\', \'p\',\'a\',\'s\',\'b\',\'d\')', 'chk_image_status');

            // Foreign Keys
            $table->foreign('store_id', 'store', 'id', 'fk_image_store')
                ->onDelete('CASCADE')
                ;
            $table->foreign('user_id', 'user', 'id', 'fk_image_user')
                ->onDelete('CASCADE')
                ;

            // Indexes
            $table->index('store_id');
            $table->index('user_id');
            $table->unique('slug');
            $table->index(['status'], 'idx_status');
            $table->unique(['slug', 'store_id'], 'unique_slug_store');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        $this->drop('image');
    }
}
