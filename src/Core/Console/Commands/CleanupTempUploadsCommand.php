<?php

declare(strict_types=1);

namespace Core\Console\Commands;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class CleanupTempUploadsCommand extends Command
{
    protected static $defaultName = 'cleanup:temp-uploads';
    protected static $defaultDescription = 'Cleans up old temporary upload files.';

    private string $tempUploadDir;
    private int $retentionHours; // How long to keep temporary files

    public function __construct(LoggerInterface $logger, string $tempUploadDir, int $retentionHours = 24)
    {
        parent::__construct();
        $this->logger = $logger;
        $this->tempUploadDir = $tempUploadDir;
        $this->retentionHours = $retentionHours;
    }

    protected function configure(): void
    {
        $this
            ->addOption('age', 'a', InputOption::VALUE_OPTIONAL, 'Delete files older than this many hours (default: 24)', (string) $this->retentionHours);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ageHours = (int) $input->getOption('age');
        $cutoffTime = time() - ($ageHours * 3600); // Calculate cutoff timestamp

        $output->writeln("<info>Starting temporary upload directory cleanup...</info>");
        $this->logger->info("Starting temporary upload directory cleanup for files older than {$ageHours} hours in {$this->tempUploadDir}.");

        if (!is_dir($this->tempUploadDir) || !is_readable($this->tempUploadDir)) {
            $output->writeln("<error>Error: Temporary upload directory '{$this->tempUploadDir}' does not exist or is not readable.</error>");
            $this->logger->error("Temporary upload directory '{$this->tempUploadDir}' does not exist or is not readable.");
            return Command::FAILURE;
        }

        $deletedCount = 0;
        $failedCount = 0;
        foreach (new \DirectoryIterator($this->tempUploadDir) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }

            if ($fileInfo->getMTime() < $cutoffTime) {
                $filePath = $fileInfo->getPathname();
                if (@unlink($filePath)) {
                    $deletedCount++;
                    $this->logger->debug("Deleted old temporary file: {$filePath}");
                } else {
                    $failedCount++;
                    $this->logger->error("Failed to delete old temporary file: {$filePath}");
                }
            }
        }

        $output->writeln("<info>Cleanup complete: Deleted {$deletedCount} files, failed to delete {$failedCount} files.</info>");
        $this->logger->info("Temporary upload directory cleanup finished. Deleted {$deletedCount} files, failed {$failedCount}.");

        return Command::SUCCESS;
    }
}
