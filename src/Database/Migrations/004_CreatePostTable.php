<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreatePostTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('post')) {
            return;
        }

        $this->create('post', function ($table) {
            $table->bigIncrements('id');
            $table->bigInteger('store_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->char('status', 1)->default('P');
            // $table->string('slug', 100)->unique();
            $table->string('slug', 100);
            $table->string('title', 255);
            $table->text('content');
            $table->timestamps();

            // Add indexes for frequent queries
            $table->index('store_id');
            $table->index('user_id');
            $table->index('status');

            $table->unique(['slug', 'store_id']);

            // Add foreign key to stores
            $table->foreign(
                'store_id',
                'stores',
                'store_id',
                'fk_post_stores'
            )->onDelete('CASCADE');

            // Add foreign key to users
            $table->foreign(
                'user_id',
                'users',
                'user_id',
                'fk_post_users'
            )->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('post');
    }
}
