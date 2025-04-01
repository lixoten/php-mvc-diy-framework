<?php

use Core\Database\Migrations\MigrationRepository;
use Core\Database\Migrations\MigrationRunner;
use App\Helpers\DebugRt as Debug;
use Database\Seeders\UsersSeeder;

require_once __DIR__ . '/../vendor/autoload.php';

// Set environment variable
$environment = 'development';

// Load .env file if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    // Use environment from .env if defined
    $environment = $_ENV['APP_ENV'] ?? $environment;
}

// Initialize container
$containerBuilder = new \DI\ContainerBuilder();
// Define environment BEFORE loading dependencies.php
$containerBuilder->addDefinitions([
    'environment' => $environment,
]);
$containerBuilder->addDefinitions(__DIR__ . '/../src/dependencies.php');
$container = $containerBuilder->build();

// Get database connection
$db = $container->get('db');
$logger = $container->get('logger');

// Parse command line arguments
$command = $argv[1] ?? 'help';
$arg = $argv[2] ?? null;

// Migration repository and runner
$repository = new MigrationRepository($db);
$runner = new MigrationRunner(
    $db,
    $repository,
    __DIR__ . '/../src/Database/Migrations',
    'Database\\Migrations',
    $logger
);

echo "==== DEBUG INFO ====\n";

// 1. Check if migrations table exists
try {
    $tableExists = $db->query("SHOW TABLES LIKE 'migrations'");
    echo "Migrations table exists: " . (!empty($tableExists) ? "Yes" : "No") . "\n";

    // Create table if it doesn't exist
    if (empty($tableExists)) {
        echo "Creating migrations table...\n";
        $repository->createRepository();
        echo "Table created.\n";
    }
} catch (\Exception $e) {
    echo "Error checking migrations table: " . $e->getMessage() . "\n";
}

// 2. Check available migration files
echo "\nAvailable migration files:\n";
$migrationPath = __DIR__ . '/../src/Database/Migrations';
$files = scandir($migrationPath);
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        echo "- $file\n";
    }
}

// 3. Check tracked migrations
try {
    $executed = $repository->getMigratedFiles();
    echo "\nAlready executed migrations: " . count($executed) . "\n";
    foreach ($executed as $migration) {
        echo "- $migration\n";
    }
} catch (\Exception $e) {
    echo "Error getting executed migrations: " . $e->getMessage() . "\n";
}

echo "==================\n\n";

// Execute command
switch ($command) {
    case 'migrate':
        echo "Running migrations...\n";

        // Check for --force flag
        $force = in_array('--force', $argv) || in_array('-f', $argv);

        // Use the runner's built-in run method
        //$migrations = $runner->run();
        $migrations = $runner->run($force);  // Pass force parameter to run()

        if (empty($migrations)) {
            echo "No migrations were executed.\n";
        } else {
            echo count($migrations) . " migrations executed:\n";
            foreach ($migrations as $migration) {
                echo "- $migration\n";
            }
        }
        break;

    case 'rollback':
        $steps = $arg ? (int) $arg : 1;
        echo "Rolling back {$steps} batch(es)...\n";
        $migrations = $runner->rollback($steps);
        echo count($migrations) . " migrations rolled back.\n";
        break;

    case 'seed':
        if (!$arg) {
            echo "Error: Seeder name is required\n";
            echo "Usage: php bin/console.php seed [seeder_name]\n";
            break;
        }
        // if ($arg === 'UsersSeeder' || $arg === 'Database\\Seeders\\UsersSeeder') {
        //     // Direct execution of UsersSeeder
        //     //D:\xampp\htdocs\my_projects\mvclixo\src\Database\Seeder\UsersSeeder.php
        //     $seeder = new UsersSeeder();
        //     $seeder->run($db);
        //     echo "UsersSeeder executed successfully!\n";
        // } else {
        $className = $arg ?? 'DatabaseSeeder';

        $seederClass = (strpos($className, '\\') === false)
        ? 'Database\\Seeders\\' . $className  // Add namespace when needed
        : $className;
        // Debug::p($seederClass);
        echo "Running seeder: {$seederClass}\n";

        if (class_exists($seederClass)) {
            //Debug::p(111);
            $seeder = new $seederClass($db);
            $seeder->run();
            echo "Seeding completed.\n";
        } else {
            echo "Seeder class not found: {$seederClass}\n";
        }
        // }
        break;

    case 'help':
    default:
        echo "Migration Commands:\n";
        echo "  migrate           Run pending migrations\n";
        echo "  rollback [steps]  Roll back the last batch or specific number of batches\n";
        echo "  seed [class]      Run database seeders\n";
        break;
}
