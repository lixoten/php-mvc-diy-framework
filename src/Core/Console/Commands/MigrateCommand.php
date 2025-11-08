<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Database\Migrations\MigrationRunner;

/**
 * Console command to run all pending database migrations.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class MigrateCommand extends Command
{
    protected static $defaultName = 'migrate';
    protected static $defaultDescription = 'Runs all pending database migrations.';

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
            ->setDescription(static::$defaultDescription) // Explicitly set description
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
        $force = $input->getOption('force');

        $io->title('Running Migrations');

        try {
            // The MigrationRunner's constructor already handles checking/creating the migrations table
            // and logging debug info.
            $migrations = $this->migrationRunner->run($force);

            if (empty($migrations)) {
                $io->info('No new migrations were executed.');
            } else {
                $io->success(count($migrations) . ' migrations executed:');
                $io->listing($migrations);
            }
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Error running migrations: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
