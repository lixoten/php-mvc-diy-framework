<?php

declare(strict_types=1);

namespace Core\Database\Seeders;

/**
 * Interface for database seeder classes.
 *
 * All seeder classes should implement this interface to ensure a consistent
 * `run` method for executing seeding logic.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
interface SeederInterface
{
    /**
     * Run the database seeder.
     *
     * @return void
     */
    public function run(): void;
}
