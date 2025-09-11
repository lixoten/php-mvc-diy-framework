<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->create('posts', function ($table) {
            $table->bigIncrements('post_id');
            $table->bigInteger('post_store_id')->unsigned();
            $table->bigInteger('post_user_id')->unsigned();
            $table->char('post_status', 1)->default('P');
            // $table->string('slug', 100)->unique();
            $table->string('slug', 100);
            $table->string('title', 255);
            $table->text('content');
            $table->timestamps();

            // Add indexes for frequent queries
            $table->index('post_store_id');
            $table->index('post_user_id');
            $table->index('post_status');

            $table->unique(['slug', 'post_store_id']);
            
            // Add foreign key to stores
            $table->foreign(
                'post_store_id',
                'stores',
                'store_id',
                'fk_posts_stores'
            )->onDelete('CASCADE');

            // Add foreign key to users
            $table->foreign(
                'post_user_id',
                'users',
                'user_id',
                'fk_posts_users'
            )->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('posts');
    }
}
