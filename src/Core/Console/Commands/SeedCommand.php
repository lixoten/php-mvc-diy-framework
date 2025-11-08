<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Core\Database\Seeders\SeederRunnerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to run database seeders.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class SeedCommand extends Command
{
    protected static $defaultName = 'seed';
    protected static $defaultDescription = 'Runs database seeders.';

    private SeederRunnerService $seederRunnerService;

    /**
     * @param SeederRunnerService $seederRunnerService The service to run seeders.
     */
    public function __construct(SeederRunnerService $seederRunnerService)
    {
        parent::__construct();
        $this->seederRunnerService = $seederRunnerService;
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this
            ->setName(static::$defaultName) // Explicitly set the command name here
            ->addArgument('entity', InputArgument::OPTIONAL, 'The name of the entity for which to run the seeder (e.g., "user").') // MODIFIED: Argument name and description
            ->addOption('all', null, InputOption::VALUE_NONE, 'Run all available seeders.');
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
        $entityName = $input->getArgument('entity'); // MODIFIED: Variable name
        $runAll = $input->getOption('all');

        if ($entityName && $runAll) { // MODIFIED: Variable name
            $io->error('Cannot specify both an entity name and the --all option.');
            return Command::FAILURE;
        }

        $io->title('Running Seeders');

        try {
            if ($runAll) {
                $executedSeeders = $this->seederRunnerService->runAll($io);
                if (empty($executedSeeders)) {
                    $io->info('No seeders were executed.');
                } else {
                    $io->success('All seeders executed successfully:');
                    $io->listing($executedSeeders);
                }
            } elseif ($entityName) { // MODIFIED: Variable name
                $this->seederRunnerService->run($entityName, $io); // MODIFIED: Pass entityName
                $io->success("Seeder for '{$entityName}' executed successfully."); // MODIFIED: Variable name
            } else {
                $availableSeeders = $this->seederRunnerService->getAvailableSeeders();
                if (empty($availableSeeders)) {
                    $io->info('No seeders found.');
                } else {
                    $io->comment('Available seeders (by entity name):'); // MODIFIED: Clarified description
                    $io->listing($availableSeeders);
                    $io->text("\nUsage: php bin/console.php seed [entity_name]"); // MODIFIED: Clarified usage
                    $io->text("       php bin/console.php seed --all");
                }
            }
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Error running seeder: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
