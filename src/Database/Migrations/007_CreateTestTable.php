<?php

declare(strict_types=1);

// filepath: d:\xampp\htdocs\mvcxxlixo\src\Database\Migrations\20250318000001_CreateTestTable.php
// 20250318000001_CreateTestTable.php

namespace Database\Migrations;

use Core\Database\Migrations\Migration;

class CreateTestTable extends Migration
{
    public function up(): void
    {
        // Idempotent guard: skip if table already exists
        if ($this->schema->hasTable('test_table')) {
            return;
        }

        $this->create('test_table', function ($table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->drop('test_table');
    }
}
