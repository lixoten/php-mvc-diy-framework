<?php

use Core\Database\Migrations\MigrationRepository;
use Core\Database\Migrations\MigrationRunner;
use App\Helpers\DebugRt;

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

function getSeederFiles($seederPath) {
    $files = scandir($seederPath);
    $seeders = [];
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $filename = pathinfo($file, PATHINFO_FILENAME);

            // Strip numeric prefix (001_, 002_, etc.)
            $className = preg_replace('/^\d+_/', '', $filename);

            $seeders[] = $className;
        }
    }
    return $seeders;
}


echo "==== DEBUG INFO ====\n";

// 1. Check if migrations table exists
try {
    // Better check for migrations table existence
    $tableExists = $db->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'migrations'");
    $exists = $tableExists[0]['count'] > 0;
    echo "Migrations table exists: " . ($exists ? "Yes" : "No") . "\n";

    // Create table if it doesn't exist
    if (!$exists) {
        echo "Creating migrations table...\n";
        $repository->createRepository();
        echo "Table created.\n";
    }
} catch (\Exception $e) {
    echo "Error checking migrations table: " . $e->getMessage() . "\n";
    echo "Attempting to create migrations table...\n";
    try {
        $repository->createRepository();
        echo "Migrations table created successfully.\n";
    } catch (\Exception $createException) {
        echo "Failed to create migrations table: " . $createException->getMessage() . "\n";
    }
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
    case 'migrate:one':
        if (!$arg) {
            echo "Usage: php bin/console.php migrate:one [MigrationClassName] [--force]\n";
            break;
        }

        $migrationClass = $arg;
        $force = in_array('--force', $argv, true);

        try {
            $executed = $runner->runSingleMigration($migrationClass, $force);

            if ($executed) {
                echo "Migration '{$migrationClass}' executed successfully.\n";
            } else {
                echo "Migration '{$migrationClass}' was not executed (already up to date or not found).\n";
            }
        } catch (\Throwable $e) {
            echo "Error running migration '{$migrationClass}': " . $e->getMessage() . "\n";
        }
        break;

    case 'migrate':
        echo "Running migrations...\n";

        // Check for --force flag
        $force = in_array('--force', $argv) || in_array('-f', $argv);

        $migrations = $runner->run($force);

        if (empty($migrations)) {
            echo "No migrations were executed.\n";
        } else {
            echo count($migrations) . " migrations executed:\n";
            foreach ($migrations as $migration) {
                echo "- $migration\n";
            }
        }
        break;

    case 'show:fk':
        $table = $arg ?? null;
        if (!$table) {
            echo "Please specify a table name\n";
            exit(1);
        }

        $result = $db->query(
            "SELECT
                rc.CONSTRAINT_NAME,
                kcu.TABLE_NAME,
                kcu.COLUMN_NAME,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME,
                rc.DELETE_RULE
            FROM information_schema.REFERENTIAL_CONSTRAINTS rc
            JOIN information_schema.KEY_COLUMN_USAGE kcu
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
            WHERE kcu.TABLE_NAME = ?",
            [$table]
        );

        if (empty($result)) {
            echo "No foreign keys found for table '$table'\n";
        } else {
            foreach ($result as $row) {
                echo "Constraint: {$row['CONSTRAINT_NAME']}\n";
                echo "  Column: {$row['COLUMN_NAME']}\n";
                echo "  References: {$row['REFERENCED_TABLE_NAME']}({$row['REFERENCED_COLUMN_NAME']})\n";
                echo "  On Delete: {$row['DELETE_RULE']}\n";
                echo "\n";
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
        $seederPath = __DIR__ . '/../src/Database/Seeders';

        // if (!$arg) {
        //     echo "Error: Seeder name is required\n";
        //     echo "Usage: php bin/console.php seed [seeder_name]\n";
        //     break;
        // }


        if (!$arg) {
            // Show available seeders instead of requiring name
            echo "Available seeders:\n";
            $availableSeeders = getSeederFiles($seederPath);
            foreach ($availableSeeders as $seeder) {
                echo "- $seeder\n";
            }
            echo "\nUsage: php bin/console.php seed [seeder_name]\n";
            echo "       php bin/console.php seed --all\n";
            break;
        }

        if ($arg === '--all') {
            echo "Running all seeders...\n";
            $allSeeders = getSeederFiles($seederPath);
            foreach ($allSeeders as $seederName) {
                // Find the actual file (with prefix)
                $files = scandir($seederPath);
                $actualFile = null;
                foreach ($files as $file) {
                    if (strpos($file, $seederName . '.php') !== false) {
                        $actualFile = $file;
                        break;
                    }
                }

                if ($actualFile) {
                    // LOAD FILE DIRECTLY like migrations do
                    require_once $seederPath . '/' . $actualFile;

                    $seederClass = 'Database\\Seeders\\' . $seederName;
                    echo "Running: $seederName\n";
                    $seeder = new $seederClass($db);
                    $seeder->run();
                }
            }
        }




print_r(get_declared_classes());


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


