<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Database\Migrations\MigrationRunner;

/**
 * Console command to run a single database migration.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class MigrateOneCommand extends Command
{
    protected static $defaultName = 'migrate:one';
    protected static $defaultDescription = 'Runs a single specified database migration.';

    private MigrationRunner $migrationRunner;

    /**
     * @param MigrationRunner $migrationRunner The service to run migrations.
     */
    public function __construct(MigrationRunner $migrationRunner)
    {
        parent::__construct();
        $this->migrationRunner = $migrationRunner;
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this
            ->setName(static::$defaultName) // Explicitly set the command name here
            ->addArgument(
                'migrationClass',
                InputArgument::REQUIRED,
                'The class name of the migration to run (e.g., "CreateUsersTable").'
            )
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the migration to run even if already migrated.');
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return int The command exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $migrationClass = $input->getArgument('migrationClass');
        $force = $input->getOption('force');

        $io->title("Running Single Migration: {$migrationClass}");

        try {
            $executed = $this->migrationRunner->runSingleMigration($migrationClass, $force);

            if ($executed) {
                $io->success("Migration '{$migrationClass}' executed successfully.");
            } else {
                $io->info("Migration '{$migrationClass}' was not executed (already up to date or not found).");
            }
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error("Error running migration '{$migrationClass}': " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
