<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateAlbumsTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->create('albums', function ($table) {
            $table->bigIncrements('album_id');
            $table->bigInteger('album_store_id')->unsigned();
            $table->bigInteger('album_user_id')->unsigned();
            $table->char('album_status', 1)->default('P');
            $table->string('slug', 100)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->timestamps();

            // Add indexes for frequent queries
            $table->index('album_store_id');
            $table->index('album_user_id');
            $table->index('album_status');

            // Add foreign key to stores
            $table->foreign(
                'album_store_id',
                'stores',
                'store_id',
                'fk_albums_stores'
            )->onDelete('CASCADE');

            // Add foreign key to users
            $table->foreign(
                'album_user_id',
                'users',
                'user_id',
                'fk_albums_users'
            )->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('albums');
    }
}
