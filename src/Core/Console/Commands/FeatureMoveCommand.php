<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Core\Services\PathResolverService;
use RuntimeException;

/**
 * Console command to move generated feature files to their final, organized locations.
 *
 * This command handles moving files like entities, repositories, migrations, and seeders
 * from a temporary generated directory to their respective application or core directories,
 * offering user confirmation for each move. Older generated copies are moved to an archive.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class FeatureMoveCommand extends Command
{
    protected static string $defaultName = 'feature:move';
    protected static string $defaultDescription = 'Moves generated feature files to their final locations.';

    private PathResolverService $pathResolverService;

    /**
     * @param PathResolverService $pathResolverService The service for resolving application paths.
     */
    public function __construct(PathResolverService $pathResolverService)
    {
        parent::__construct();
        $this->pathResolverService = $pathResolverService;
    }

    /**
     * Configures the command.
     */
    protected function configure(): void
    {
        $this
            ->setName(static::$defaultName)
            ->setDescription(static::$defaultDescription)
            ->addArgument('entity', InputArgument::REQUIRED, 'The name of the entity (e.g., "Testy").')
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                'The type of files to move (all, entity, repository, seeder, migration).',
                'all'
            );
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
        $entity = ucfirst($input->getArgument('entity')); // Ensure entity name is PascalCase
        $type = strtolower($input->getArgument('type'));

        $io->title("Moving Feature Files for '{$entity}' (Type: {$type})");

        $generatedDir = $this->pathResolverService->getGeneratedEntityPath($entity);

        if (!is_dir($generatedDir)) {
            $io->warning("No generated files found for entity '{$entity}' in {$generatedDir}");
            return Command::SUCCESS; // Not an error if nothing to move
        }

        // Map of generated file patterns to final destinations and processing flags
        $moveMap = [
            'entity' => [
                'pattern' => "{$entity}.php",
                'dest' => $this->pathResolverService->getAppFeatureEntityPath($entity),
                'rename_on_move' => false,
                'process_all_matching' => false, // Only one entity file expected
            ],
            'repositoryinterface' => [
                'pattern' => "{$entity}RepositoryInterface.php",
                'dest' => $this->pathResolverService->getAppFeatureRepositoryPath($entity),
                'rename_on_move' => false,
                'process_all_matching' => false, // Only one interface file expected
            ],
            'repository' => [
                'pattern' => "{$entity}Repository.php",
                'dest' => $this->pathResolverService->getAppFeatureRepositoryPath($entity),
                'rename_on_move' => false,
                'process_all_matching' => false, // Only one repository file expected
            ],
            'migration' => [
                'pattern' => "*Create{$entity}Table.php",
                'dest' => $this->pathResolverService->getDatabaseMigrationsPath(),
                'rename_on_move' => false, // Migrations keep timestamped names
                'process_all_matching' => false, // All matching migrations should be moved
            ],
            'seeder' => [
                'pattern' => "*{$entity}Seeder.php",
                'dest' => $this->pathResolverService->getDatabaseSeedersPath(),
                'rename_on_move' => true, // Seeders are renamed to EntitySeeder.php
                'process_all_matching' => false, // Only the latest seeder is moved
            ],
            'langmainfile' => [
                'pattern' => strtolower($entity) . "_lang" . ".php",
                'dest' => $this->pathResolverService->getLangFilePath(),
                'rename_on_move' => false,
                'process_all_matching' => false,
            ],
            'langcommonfile' => [
                'pattern' => "common_lang" . ".php",
                'dest' => $this->pathResolverService->getLangFilePath(),
                'rename_on_move' => false,
                'process_all_matching' => false,
            ],
            'configfieldsbase' => [
                'pattern' => 'base_fields.php',
                'dest' => $this->pathResolverService->getConfigFieldRenderBaseFilePath(),
                'rename_on_move' => false,
                'process_all_matching' => false,
            ],
            'configfieldsroot' => [
                'pattern' => strtolower($entity) . "_fields_root" . ".php",
                'dest' => $this->pathResolverService->getAppFeatureConfigPath($entity),
                'rename_on_move' => false,
                'process_all_matching' => false,
            ],
            'configfieldslist' => [
                'pattern' => strtolower($entity) . "_fields_list" . ".php",
                'dest' => $this->pathResolverService->getAppFeatureConfigPath($entity),
                'rename_on_move' => false,
                'process_all_matching' => false,
            ],
            'configfieldsedit' => [
                'pattern' => strtolower($entity) . "_fields_edit" . ".php",
                'dest' => $this->pathResolverService->getAppFeatureConfigPath($entity),
                'rename_on_move' => false,
                'process_all_matching' => false,
            ],
            'configviewlist' => [
                'pattern' => strtolower($entity) . "_view_list" . ".php",
                'dest' => $this->pathResolverService->getAppFeatureConfigPath($entity),
                'rename_on_move' => false,
                'process_all_matching' => false,
            ],
            'configviewedit' => [
                'pattern' => strtolower($entity) . "_view_edit" . ".php",
                'dest' => $this->pathResolverService->getAppFeatureConfigPath($entity),
                'rename_on_move' => false,
                'process_all_matching' => false,
            ],
            // Add more as needed (Controller, FormType, ListType, etc.)
        ];

        $typesToMove = [];
        if ($type === 'all') {
            $typesToMove = array_keys($moveMap);
        } elseif ($type === 'repository') {
            $typesToMove[] = 'repositoryinterface';
            $typesToMove[] = 'repository';
        } else {
            $typeKey = strtolower($type);
            if (!array_key_exists($typeKey, $moveMap)) {
                $io->error("Unknown type: {$type}. Valid types: " . implode(', ', array_keys($moveMap)));
                return Command::FAILURE;
            }
            $typesToMove[] = $typeKey;
        }

        $movedCount = 0;
        $skipAll = false;

        foreach ($typesToMove as $typeKey) {
            if ($skipAll) {
                break;
            }

            $info = $moveMap[$typeKey];
            $sourcePattern = $generatedDir . DIRECTORY_SEPARATOR . $info['pattern'];
            $allMatchingFiles = glob($sourcePattern);

            if (empty($allMatchingFiles)) {
                $io->note("No files found for type '{$typeKey}' matching pattern '{$info['pattern']}'.");
                continue;
            }

            // Sort by filename (which includes timestamp) descending, so latest is first
            rsort($allMatchingFiles);

            $filesToProcess = [];
            $filesToArchiveFromSource = [];

            if ($info['process_all_matching']) {
                // For migrations, process all found files
                $filesToProcess = $allMatchingFiles;
            } else {
                // For other types (seeder, entity, repo), only process the latest, archive others from source
                $filesToProcess[] = array_shift($allMatchingFiles); // Get the latest
                $filesToArchiveFromSource = $allMatchingFiles; // Remaining are older
            }

            // Archive older generated files from the source directory
            if (!empty($filesToArchiveFromSource)) {
                $archiveDir = $this->pathResolverService->getGeneratedEntityArchivePath($entity);
                $this->ensureDirectoryExists($archiveDir, $io);
                foreach ($filesToArchiveFromSource as $fileToArchive) {
                    if (file_exists($fileToArchive)) {
                        $archiveFilePath = $archiveDir . DIRECTORY_SEPARATOR . basename($fileToArchive);
                        if (rename($fileToArchive, $archiveFilePath)) {
                            $io->note("Archived old generated file: <comment>" . basename($fileToArchive)
                                                                               . "</comment> to <comment>"
                                                                               . basename($archiveDir) . "</comment>");
                        } else {
                            $io->warning("Failed to archive old generated file: {$fileToArchive}");
                        }
                    }
                }
            }

            foreach ($filesToProcess as $sourceFile) {
                if ($skipAll) {
                    break;
                }

                $finalFilename = basename($sourceFile);

                // Apply renaming logic if specified (e.g., for seeders)
                if ($info['rename_on_move']) {
                    if (preg_match('/^\d{8}_\d{6}_(.+\.php)$/', $finalFilename, $matches)) {
                        $finalFilename = $matches[1];
                    }
                }

                $destinationFilePath = rtrim($info['dest'], '/\\') . DIRECTORY_SEPARATOR . $finalFilename;

                // Check if a file with the FINAL FILENAME already exists in the DESTINATION
                // if (file_exists($destinationFilePath)) {
                //     $archiveDir = $this->pathResolverService->getGeneratedEntityArchivePath($entity);
                //     $this->ensureDirectoryExists($archiveDir, $io);
                //     $archiveExistingPath = $archiveDir . DIRECTORY_SEPARATOR
                //                                        . $this->addTimestampToFilename(basename($destinationFilePath));
                //     rename($destinationFilePath, $archiveExistingPath);
                //     $io->text("Archived existing destination file: <comment>" . basename($destinationFilePath)
                //                                                               . "</comment> to <comment>"
                //                                                               . basename($archiveDir) . "</comment>");
                // }

                // Ensure destination directory exists
                $this->ensureDirectoryExists(dirname($destinationFilePath), $io);

                $io->text("Move file:");
                $io->text("  From: <info>{$sourceFile}</info>");
                $io->text("  To:   <info>{$destinationFilePath}</info>");

                $answer = $io->ask('Proceed? [y/N/s] (y = yes, N = no, s = skip all)', 'n');

                if (strtolower($answer) === 'y') {
                    if (file_exists($destinationFilePath)) {
                        $archiveDir = $this->pathResolverService->getGeneratedEntityArchivePath($entity);
                        $this->ensureDirectoryExists($archiveDir, $io);
                        $archiveExistingPath = $archiveDir . DIRECTORY_SEPARATOR
                                                           . $this->addTimestampToFilename(basename($destinationFilePath));
                        rename($destinationFilePath, $archiveExistingPath);
                        $io->text("Archived existing destination file: <comment>" . basename($destinationFilePath)
                                                                                  . "</comment> to <comment>"
                                                                                  . basename($archiveDir) . "</comment>");
                    }

                    if (rename($sourceFile, $destinationFilePath)) {
                        $io->success("Moved: {$sourceFile} -> {$destinationFilePath}");
                        $movedCount++;
                    } else {
                        $io->error("Failed to move: {$sourceFile}");
                    }
                } elseif (strtolower($answer) === 's') {
                    $io->info("Skipping all remaining files.");
                    $skipAll = true;
                } else {
                    $io->note("Skipped: {$sourceFile}");
                }
            }
        }

        // Optionally remove the empty generated directory
        if (is_dir($generatedDir) && count(scandir($generatedDir)) <= 2) { // . and ..
            if (rmdir($generatedDir)) {
                $io->info("Removed empty generated directory: {$generatedDir}");
            } else {
                $io->warning("Could not remove empty generated directory: {$generatedDir}");
            }
        }

        if ($movedCount === 0) {
            $io->info("No files moved for entity '{$entity}'.");
        } else {
            $io->success("{$movedCount} files for '{$entity}' moved to their final locations.");
        }

        return Command::SUCCESS;
    }

    /**
     * Adds a timestamp prefix to a filename.
     * Used for archiving files that were previously in the final destination.
     *
     * @param string $filename The original filename (e.g., UserSeeder.php).
     * @return string The timestamped filename (e.g., 20251101_163000_UserSeeder.php).
     */
    private function addTimestampToFilename(string $filename): string
    {
        $timestamp = date('Ymd_His');
        return $timestamp . '_' . $filename;
    }

    /**
     * Ensures a directory exists, creating it if necessary.
     *
     * @param string $directoryPath The path to the directory.
     * @param SymfonyStyle $io The SymfonyStyle instance for output.
     * @throws RuntimeException If the directory cannot be created.
     */
    private function ensureDirectoryExists(string $directoryPath, SymfonyStyle $io): void
    {
        if (!is_dir($directoryPath)) {
            if (!mkdir($directoryPath, 0777, true)) {
                throw new RuntimeException("Failed to create directory: {$directoryPath}");
            }
            $io->note("Created directory: {$directoryPath}");
        }
    }
}
