<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;
use Core\Database\Schema\Blueprint;

/**
 * Generated File - Date: 20251109_204041
 * Migration for creating the 'gallery' table.
 */
class CreateGalleryTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('gallery')) {
            return;
        }

        $this->create('gallery', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')
                    ->nullable()
                    ->comment('Store this record belongs to');
            $table->foreignId('user_id')
                    ->nullable()
                    ->comment('Foreign key to the user who created the gallery.');
            $table->char('status', 1)
                    ->nullable(false)
                    ->default('P')
                    ->comment('P=Pending, A=Active, I=Inactive, D=Draft, R-Archived');
            $table->string('name', 255)
                    ->nullable(false)
                    ->comment('The display name of the gallery.');
            $table->string('slug', 255)
                    ->nullable(false)
                    ->comment('URL-friendly slug for the gallery.');
            $table->text('description')
                    ->nullable()
                    ->comment('A detailed description of the gallery.');
            $table->integer('image_count')
                    ->nullable()
                    ->comment('Image Count');
            $table->foreignId('cover_image_id')
                    ->nullable()
                    ->comment('Optional cover image id');
            $table->timestamps();

            // CHECK Constraints
            $table->check('status IN (\'P\', \'A\', \'I\', \'D\', \'R\')', 'chk_gallery_status');

            // Foreign Keys
            $table->foreign('store_id', 'store', 'id', 'fk_gallery_store')
                ->onDelete('CASCADE')
                ;
            $table->foreign('user_id', 'user', 'id', 'fk_gallery_user')
                ->onDelete('CASCADE')
                ;
            // $table->foreign('cover_image_id', 'image', 'id', 'fk_gallery_cover_image')
            //     ->onDelete('SET NULL')
            //     ;

            // Indexes
            $table->index('store_id');
            $table->index('user_id');
            $table->index('cover_image_id');
            $table->index(['status'], 'idx_status');
            $table->unique(['slug', 'store_id'], 'unique_slug_gallery');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down(): void
    {
        $this->drop('gallery');
    }
}
