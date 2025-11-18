<?php

declare(strict_types=1);

use Core\Console\Commands\FeatureMoveCommand;
use Core\Console\Commands\HelloCommand;
use Core\Console\Commands\MakeEntityCommand;
use Core\Console\Commands\MakeConfigFieldsCommand;
use Core\Console\Commands\MakeConfigViewCommand;
use Core\Console\Commands\MakeMigrationCommand;
use Core\Console\Commands\MakeRepositoryCommand;
use Core\Console\Commands\MakeSeederCommand;
use Core\Console\Commands\MigrateCommand;
use Core\Console\Commands\MigrateOneCommand;
use Core\Console\Commands\RollbackCommand;
use Core\Console\Commands\SeedCommand;
use DI\ContainerBuilder;
use Symfony\Component\Console\Application;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Set environment variable
$environment = $_ENV['APP_ENV'] ?? 'development';

// Build DI Container
$containerBuilder = new ContainerBuilder();

// --- All definitions must be added BEFORE calling build() ---
$containerBuilder->addDefinitions([
    'environment' => $environment,
    'projectRoot' => dirname(__DIR__) // Correctly define 'projectRoot' here
]);
$containerBuilder->addDefinitions(dirname(__DIR__) . '/src/dependencies.php');

$container = $containerBuilder->build(); // Build the container once

// Create Console Application
$application = new Application('MVC LIXO Console', '1.0.0');

// Register Commands
// All commands will be retrieved from the DI container to ensure dependencies are injected
$application->add($container->get(HelloCommand::class));
$application->add($container->get(MigrateCommand::class));
$application->add($container->get(MigrateOneCommand::class));
$application->add($container->get(RollbackCommand::class));
$application->add($container->get(SeedCommand::class));

$application->add($container->get(MakeRepositoryCommand::class));
$application->add($container->get(MakeEntityCommand::class));
$application->add($container->get(MakeConfigFieldsCommand::class));
$application->add($container->get(MakeConfigViewCommand::class));
$application->add($container->get(MakeSeederCommand::class));
$application->add($container->get(MakeMigrationCommand::class));
$application->add($container->get(FeatureMoveCommand::class));

try {
    $application->run();
} catch (Throwable $e) {
    // Basic error handling for console commands
    fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, $e->getTraceAsString() . PHP_EOL);
    exit(1);
}
