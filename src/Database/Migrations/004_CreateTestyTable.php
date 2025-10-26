<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateTestyTable extends Migration
{
    /**
     * Run the migration
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('testy')) {
            return;
        }

        $this->create('testy', function ($table) {
            $table->bigIncrements('id');
            $table->bigInteger('store_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->char('status', 1)->default('P');
            // $table->string('slug', 100)->unique();
            $table->string('slug', 100);
            $table->string('title', 255);
            $table->text('content');
            $table->string('generic_text', 50);

            $table->date('date_of_birth')->nullable()->comment('Date of Birth');

            $table->date('generic_date')->nullable()->comment('Generic Date');
            $table->string('generic_month')->nullable()->comment('Generic Month');
            $table->string('generic_week')->nullable()->comment('Generic Week');
            $table->time('generic_time')->nullable()->comment('Generic Time');
            $table->dateTime('generic_datetime')->nullable()->comment('Generic DateTime'); // datetime-local

            $table->string('telephone', 20)->nullable()->comment('Telephone number');


            // Add gender_id with CHECK constraint for allowed values
            $table->string('gender_id', 4)
                ->nullable()
                ->comment('Gender: m=Male, f=Female, o=Other, np=Non-binary')
                ->check("gender_id IN ('m','f','o','nb')");

            // Add gender_other for custom gender text if 'o' (Other) is selected
            $table->string('gender_other', 50)
                ->nullable()
                ->comment('If gender_id is o (Other), specify here');

            // Add is_verified flag (0 = not verified, 1 = verified)
            $table->boolean('is_verified')
                ->default(false)
                ->comment('Is Verified');

            // Add interest flags (0 = no, 1 = yes)
            $table->boolean('interest_soccer_ind')
                ->default(false)
                ->comment('Interested in Soccer');
            $table->boolean('interest_baseball_ind')
                ->default(false)
                ->comment('Interested in Baseball');
            $table->boolean('interest_football_ind')
                ->default(false)
                ->comment('Interested in Football');
            $table->boolean('interest_hockey_ind')
                ->default(false)
                ->comment('Interested in Hockey');


            $table->string('primary_email', 255)
                ->nullable(false)
                ->comment('Primary email address');

            $table->string('secret_code_hash', 100)
                ->nullable(false)
                ->comment('Secret code for verification or access');

            $table->decimal('balance', 10, 2)
                ->default(0.00)
                ->comment('Account balance, max 100,000')
                ->check('balance >= 0 AND balance <= 100000');

            $table->decimal('generic_decimal', 10, 5)
                ->nullable()
                ->comment('Generic Decimal value, can be negative or positive');

            $table->integer('volume_level')->unsigned()
                ->nullable()
                ->comment('Volume Level');

            $table->decimal('start_rating', 7, 2)
                ->nullable()
                ->comment('Star Rating');

            // $table->integer('generic_number')->unsigned()
            $table->integer('generic_number')
                ->default(0)
                ->comment('Generic Number');
                // ->check('generic_number >= 0 AND generic_number <= 20');

            $table->integer('generic_num')->unsigned()
                ->default(55)
                ->comment('Generic Number');

            $table->string('generic_color', 20)
                ->nullable()
                ->comment('Favorite color');

            $table->time('wake_up_time')
                ->nullable()
                ->comment('Wake up time');

            $table->string('favorite_week_day', 10)
                ->nullable()
                ->comment('Favorite day of the week, e.g., Monday');

            $table->string('online_address', 255)
                ->nullable()
                ->comment('Online address, e.g., website or profile URL');



            $table->string('profile_picture', 255)
                ->nullable()
                ->comment('Path to uploaded profile picture');




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
                'fk_testy_stores'
            )->onDelete('CASCADE');

            // Add foreign key to users
            $table->foreign(
                'user_id',
                'users',
                'user_id',
                'fk_testy_users'
            )->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $this->drop('testy');
    }
}
