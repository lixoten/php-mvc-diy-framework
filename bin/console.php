<?php

require __DIR__ . '/../vendor/autoload.php';

// Check if we can use the class
if (class_exists('Database\\Migrations\\CreateUsersTable')) {
    echo "Class exists after manual include!\n";
} else {
    echo "Class still doesn't exist after manual include!\n";
}
use Core\Database\Migrations\MigrationRepository;
use Core\Database\Migrations\MigrationRunner;

// Set environment variable - THIS IS THE MISSING PIECE
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
    'environment' => $environment, // THIS IS THE KEY ADDITION
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

        echo "Trying to run migrations...\n";

        // Debug the migration loading process
        $migrationPath = __DIR__ . '/../src/Database/Migrations';
        foreach (scandir($migrationPath) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $class = 'Database\\Migrations\\' . $name;

                echo "Looking for class: $class from file: $file\n";

                // Check if class exists
                if (class_exists($class)) {
                    echo "✓ Class exists\n";

                    // Check if it extends Migration
                    $reflection = new \ReflectionClass($class);
                    if ($reflection->isSubclassOf(\Core\Database\Migrations\Migration::class)) {
                        echo "✓ Class extends Migration\n";
                    } else {
                        echo "✗ Class does NOT extend Migration\n";
                    }
                } else {
                    echo "✗ Class NOT found\n";
                }
                echo "\n";
            }
        }



        echo "Running migrations...\n";
        $migrations = $runner->run();
        echo count($migrations) . " migrations executed.\n";
        break;

    case 'rollback':
        $steps = $arg ? (int) $arg : 1;
        echo "Rolling back {$steps} batch(es)...\n";
        $migrations = $runner->rollback($steps);
        echo count($migrations) . " migrations rolled back.\n";
        break;

    case 'seed':
        $seederClass = $arg ?? 'Database\\Seeders\\DatabaseSeeder';
        echo "Running seeder: {$seederClass}\n";

        if (!class_exists($seederClass)) {
            echo "Seeder class not found: {$seederClass}\n";
            break;
        }

        $seeder = new $seederClass($db);
        $seeder->run();
        echo "Seeding completed.\n";
        break;

    case 'help':
    default:
        echo "Migration Commands:\n";
        echo "  migrate           Run pending migrations\n";
        echo "  rollback [steps]  Roll back the last batch or specific number of batches\n";
        break;
}
