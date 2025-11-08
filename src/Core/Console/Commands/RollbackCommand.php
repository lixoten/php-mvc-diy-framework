<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Database\Migrations\MigrationRunner;

/**
 * Console command to rollback database migrations.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class RollbackCommand extends Command
{
    protected static $defaultName = 'rollback';
    protected static $defaultDescription = 'Rolls back the last batch of database migrations.';

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
            ->addArgument('steps', InputArgument::OPTIONAL, 'The number of migration batches to rollback.', 1);
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
        $steps = (int) $input->getArgument('steps');

        $io->title("Rolling back {$steps} batch(es) of migrations");

        try {
            $migrations = $this->migrationRunner->rollback($steps);

            if (empty($migrations)) {
                $io->info('No migrations were rolled back.');
            } else {
                $io->success(count($migrations) . ' migrations rolled back:');
                $io->listing($migrations);
            }
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Error rolling back migrations: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
