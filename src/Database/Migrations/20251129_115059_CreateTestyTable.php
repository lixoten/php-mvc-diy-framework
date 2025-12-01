<?php

declare(strict_types=1);

namespace Database\Migrations;

use Core\Database\Migrations\Migration;
use Core\Database\Schema\Blueprint;

/**
 * Generated File - Date: 20251129_115059
 * Migration for creating the 'testy' table.
 */
class CreateTestyTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('testy')) {
            return;
        }

        $this->create('testy', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('store_id')
                    ->nullable()
                    ->comment('Store this record belongs to');
            $table->foreignId('user_id')
                    ->nullable(false)
                    ->comment('User who created the record');
            $table->json('super_powers')
                    ->nullable(false)
                    ->comment('JSON encoded array of user super_powers');
            $table->char('status', 1)
                    ->default('A')
                    ->comment('Status');
            $table->string('slug', 100)
                    ->nullable(false)
                    ->comment('Unique SEO-friendly slug for the testy');
            $table->string('title', 255)
                    ->nullable(false)
                    ->comment('Title');
            $table->string('generic_text', 60)
                    ->nullable()
                    ->comment('Generic text');
            $table->text('content')
                    ->nullable()
                    ->comment('Content');
            $table->integer('image_count')
                    ->nullable()
                    ->comment('Image Count');
            $table->bigInteger('cover_image_id')
                    ->nullable()
                    ->unsigned()
                    ->comment('Cover image id');
            $table->string('generic_code')
                    ->nullable(false)
                    ->comment('Generic Code');
            $table->date('date_of_birth')
                    ->nullable()
                    ->comment('Date of Birth');
            $table->date('generic_date')
                    ->nullable()
                    ->comment('Generic Date');
            $table->string('generic_month', 50)
                    ->nullable()
                    ->comment('Generic Month');
            $table->string('generic_week', 50)
                    ->nullable()
                    ->comment('Generic Week');
            $table->time('generic_time')
                    ->nullable()
                    ->comment('Generic Time');
            $table->dateTime('generic_datetime')
                    ->nullable()
                    ->comment('Generic DateTime');
            $table->string('telephone', 20)
                    ->nullable()
                    ->comment('Telephone number');
            $table->string('states_code', 4)
                    ->nullable()
                    ->comment('States');
            $table->string('gender_id', 4)
                    ->nullable()
                    ->comment('Gender');
            $table->string('gender_other', 50)
                    ->nullable()
                    ->comment('Gender Other');
            $table->boolean('is_verified')
                    ->nullable(false)
                    ->default(false)
                    ->comment('Is Verified');
            $table->boolean('interest_soccer_ind')
                    ->nullable(false)
                    ->default(false)
                    ->comment('Soccer Interest');
            $table->boolean('interest_baseball_ind')
                    ->nullable(false)
                    ->default(false)
                    ->comment('Baseball Interest');
            $table->boolean('interest_football_ind')
                    ->nullable(false)
                    ->default(false)
                    ->comment('Football Interest');
            $table->boolean('interest_hockey_ind')
                    ->nullable(false)
                    ->default(false)
                    ->comment('Hockey Interest');
            $table->string('primary_email', 255)
                    ->nullable()
                    ->comment('Primary email');
            $table->string('secret_code_hash', 100)
                    ->nullable()
                    ->comment('Secret code hash');
            $table->decimal('balance', 10, 2)
                    ->nullable(false)
                    ->default(0.0)
                    ->comment('Balance');
            $table->decimal('generic_decimal', 10, 5)
                    ->nullable()
                    ->comment('Generic Decimal');
            $table->integer('volume_level')
                    ->nullable()
                    ->unsigned()
                    ->comment('Volume Level');
            $table->decimal('start_rating', 7, 2)
                    ->nullable()
                    ->comment('Star Rating');
            $table->integer('generic_number')
                    ->nullable(false)
                    ->default(0)
                    ->comment('Generic Number');
            $table->integer('generic_num')
                    ->nullable(false)
                    ->unsigned()
                    ->default(55)
                    ->comment('Generic Number');
            $table->string('generic_color', 20)
                    ->nullable()
                    ->comment('Generic color');
            $table->time('wake_up_time')
                    ->nullable()
                    ->comment('Wake up time');
            $table->string('favorite_week_day', 10)
                    ->nullable()
                    ->comment('Favorite weekday');
            $table->string('online_address', 255)
                    ->nullable()
                    ->comment('Online address');
            $table->string('profile_picture', 255)
                    ->nullable()
                    ->comment('profile picture');
            $table->dateTime('created_at')
                    ->nullable(false)
                    ->comment('Created Date');
            $table->dateTime('updated_at')
                    ->nullable(false)
                    ->comment('Last update');

            // CHECK Constraints
            $table->check('status IN (\'J\', \'P\',\'A\',\'S\',\'B\',\'D\')', 'chk_testy_status');
            $table->check('state_code IN (\'ca,\'nj,\'al\',\'tx\',\'ny\')', 'chk_testy_states_code');
            $table->check('gender_id IN (\'m\',\'f\',\'o\',\'nb\',\'pns\')', 'chk_testy_gender_id');
            $table->check('balance >= 0 AND balance <= 100000', 'chk_testy_balance');

            // Foreign Keys
            $table->foreign('store_id', 'store', 'id', 'fk_testy_store')
                ->onDelete('CASCADE')
                ;
            $table->foreign('user_id', 'user', 'id', 'fk_testy_user')
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
        $this->drop('testy');
    }
}
