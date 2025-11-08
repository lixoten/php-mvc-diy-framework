<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * A simple example command to say hello.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class HelloCommand extends Command
{
    // The name of the command (the part after "php bin/console.php")
    // protected static $defaultName = 'app:hello'; // Keep this for reference, but we'll set it explicitly below

    // The command description shown when running "php bin/console.php list"
    protected static $defaultDescription = 'Says hello to the given name.';

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this
            ->setName('app:hello') // Explicitly set the command name here
            ->setDescription(static::$defaultDescription) // Also explicitly set description for consistency
            // Configure an argument
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the person to greet.', 'World');
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
        $name = $input->getArgument('name');

        $io->success("Hello, {$name}!");

        return Command::SUCCESS;
    }
}
