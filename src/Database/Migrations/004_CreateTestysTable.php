<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateTestysTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->create('testys', function ($table) {
            $table->bigIncrements('testy_id');
            $table->bigInteger('testy_store_id')->unsigned();
            $table->bigInteger('testy_user_id')->unsigned();
            $table->char('testy_status', 1)->default('P');
            // $table->string('slug', 100)->unique();
            $table->string('slug', 100);
            $table->string('title', 255);
            $table->text('content');
            $table->string('favorite_word', 20);

            $table->date('date_of_birth')->nullable()->comment('Date of Birth');
            $table->string('telephone', 30)->nullable()->comment('Telephone number');

            $table->timestamps();

            // Add indexes for frequent queries
            $table->index('testy_store_id');
            $table->index('testy_user_id');
            $table->index('testy_status');

            $table->unique(['slug', 'testy_store_id']);

            // Add foreign key to stores
            $table->foreign(
                'testy_store_id',
                'stores',
                'store_id',
                'fk_testys_stores'
            )->onDelete('CASCADE');

            // Add foreign key to users
            $table->foreign(
                'testy_user_id',
                'users',
                'user_id',
                'fk_testys_users'
            )->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('testys');
    }
}
