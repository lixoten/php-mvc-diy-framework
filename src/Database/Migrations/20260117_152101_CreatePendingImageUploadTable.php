<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;
use Core\Database\Schema\Blueprint;

/**
 * Generated File - Date: 20260117_152101
 * Migration for creating the 'pending_image_upload' table for the 'Image' feature.
 */
class CreatePendingImageUploadTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('pending_image_upload')) {
            return;
        }

        $this->create('pending_image_upload', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('upload_token', 36)
                    ->nullable(false)
                    ->comment('upload_token');
            $table->foreignId('store_id')
                    ->nullable()
                    ->comment('Store this record belongs to');
            $table->foreignId('user_id')
                    ->nullable(false)
                    ->comment('User who created the record');
            $table->string('temp_path', 500)
                    ->nullable(false)
                    ->comment('temp_path');
            $table->string('original_filename', 255)
                    ->nullable(false)
                    ->comment('original_filename');
            $table->string('client_mime_type', 50)
                    ->nullable(false)
                    ->comment('client_mime_type');
            $table->bigIncrements('file_size_bytes');
            $table->dateTime('created_at')
                    ->nullable(false)
                    ->comment('Created Date');
            $table->dateTime('updated_at')
                    ->nullable(false)
                    ->comment('Last update');

            // Foreign Keys
            $table->foreign('store_id', 'store', 'id', 'fk_store')
                ->onDelete('CASCADE')
                ;
            $table->foreign('user_id', 'user', 'id', 'fk_user')
                ->onDelete('CASCADE')
                ;

            // Indexes
            $table->unique('upload_token');
            $table->index('store_id');
            $table->index('user_id');
            $table->index(['expires_at'], 'idx_expires_at');
            $table->unique(['user_id', 'store_id'], 'idx_user_store');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        $this->drop('pending_image_upload');
    }
}
