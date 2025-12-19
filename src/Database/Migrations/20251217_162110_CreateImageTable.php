<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;
use Core\Database\Schema\Blueprint;

/**
 * Generated File - Date: 20251217_162110
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
            $table->string('title', 255)
                    ->nullable(false)
                    ->comment('Title');
            $table->string('slug', 100)
                    ->nullable(false)
                    ->comment('Slug');
            $table->text('description')
                    ->nullable(false)
                    ->comment('Description');
            $table->string('filename', 255)
                    ->nullable()
                    ->comment('Hash filename');
            $table->string('original_filename', 255)
                    ->nullable()
                    ->comment('Original filename');
            $table->string('mime_type', 50)
                    ->nullable()
                    ->comment('MIME type (e.g., image/jpeg)');
            $table->bigInteger('file_size_bytes')
                    ->nullable()
                    ->comment('File size in bytes');
            $table->integer('width')
                    ->nullable()
                    ->comment('Original image width in pixels');
            $table->integer('height')
                    ->nullable()
                    ->comment('Original image height in pixels');
            $table->json('focal_point')
                    ->nullable()
                    ->comment('Smart crop focal point (e.g., {\"x\":0.5,\"y\":0.3})');
            $table->boolean('is_optimized')
                    ->nullable()
                    ->default(false)
                    ->comment('Whether the image has been optimized');
            $table->string('checksum', 64)
                    ->nullable()
                    ->comment('Optional file checksum for integrity');
            $table->string('alt_text', 255)
                    ->nullable()
                    ->comment('Accessibility alt text');
            $table->string('license', 100)
                    ->nullable()
                    ->comment('Usage license');
            $table->dateTime('created_at')
                    ->nullable(false)
                    ->comment('Created Date');
            $table->dateTime('updated_at')
                    ->nullable(false)
                    ->comment('Last update');
            $table->dateTime('deleted_at')
                    ->nullable()
                    ->comment('Last update');

            // CHECK Constraints
            $table->check('status IN (\'p\',\'a\',\'s\',\'b\',\'d\')', 'chk_image_status');

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
