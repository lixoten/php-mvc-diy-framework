<?php

declare(strict_types=1);

namespace Core;

use App\Helpers\DebugRt as Debug;

class Logger
{
    public const DEBUG = 100;
    public const INFO = 200;
    public const WARNING = 300;
    public const ERROR = 400;
    public const CRITICAL = 500;

    private string $logDirectory;
    private int $minLevel;
    private bool $debugMode = false;

    public function __construct(
        string $logDirectory = null,
        int $minLevel = self::INFO
    ) {
        $this->logDirectory = $logDirectory ?? __DIR__ . '/../../logs';
        $this->minLevel = $minLevel;

        // Ensure log directory exists
        if (!is_dir($this->logDirectory)) {
            if (!mkdir($this->logDirectory, 0755, true)) {
                if ($this->debugMode) {
                    echo "<div style='background:#fdd;padding:5px;margin:5px;border:1px solid #d00;'>";
                    echo "Log directory is not writable: " . $this->logDirectory;
                    echo "</div>";
                }
                throw new \RuntimeException("Cannot create log directory: {$this->logDirectory}");
            }
            if ($this->debugMode) {
                echo "<div style='background:#dfd;padding:5px;margin:5px;border:1px solid #0d0;'>";
                echo "Created log directory: " . $this->logDirectory;
                echo "</div>";
            }
        }

        // Check if directory is writable
        if (!is_writable($this->logDirectory)) {
            if ($this->debugMode) {
                echo "<div style='background:#fdd;padding:5px;margin:5px;border:1px solid #d00;'>";
                echo "Log directory is not writable: " . $this->logDirectory;
                echo "</div>";
            }
        }
    }

    public function log(string $message, int $level = self::INFO, array $context = []): void
    {
        // Debug::pp([
        // "Logger instance #{$this->instanceId} handling log - Debug mode:" => ($this->debugMode ? 'TRUE' : 'FALSE'),
        //     "Log message: " => $message,
        // ], 'zzzzzzz environment', '#fea');

        if ($level < $this->minLevel) {
            return; // Skip logging if below minimum level
        }

         // For non-errors, apply sampling
        if ($level < self::ERROR && mt_rand(1, 100) > ($this->samplingRate * 100)) {
            return;
        }


        $levelName = $this->getLevelName($level);
        $timestamp = date('Y-m-d H:i:s');

        $contextData = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[$timestamp] [$levelName] $message$contextData\n";

        $filename = $this->getLogFilePath();

        // Debug log writing
        if ($this->debugMode) {
            Debug::pp([
                'Writing log: ' => htmlspecialchars($logMessage),
                'To file: ' => $filename,
            ], 'Log', '#eef');
        }

        $result = @file_put_contents($filename, $logMessage, FILE_APPEND);

        if ($result === false) {
            if ($this->debugMode) {
                echo "<div style='background:#fdd;padding:5px;margin:5px;border:1px solid #d00;'>";
                echo "Failed to write log! Error: " . error_get_last()['message'];
                echo "</div>";
            }
        }
    }

    private function getLogFilePath(): string
    {
        return $this->logDirectory . '/app-' . date('Y-m-d') . '.log';
    }

    // Configuration option to control sampling rate
    private float $samplingRate = 1.0; // 1.0 = 100%, 0.1 = 10%

    public function setSamplingRate(float $rate): void
    {
        $this->samplingRate = max(0, min(1, $rate));
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log($message, self::DEBUG, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log($message, self::INFO, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log($message, self::WARNING, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log($message, self::ERROR, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log($message, self::CRITICAL, $context);
    }

    private function getLevelName(int $level): string
    {
        return match ($level) {
            self::DEBUG => 'DEBUG',
            self::INFO => 'INFO',
            self::WARNING => 'WARNING',
            self::ERROR => 'ERROR',
            self::CRITICAL => 'CRITICAL',
            default => 'UNKNOWN'
        };
    }

    public function setDebugMode(bool $mode): void
    {
        $this->debugMode = $mode;
    }
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }


    // Add this method to your Logger class
    public function cleanupOldLogs(int $retentionDays = 30): void
    {
        $directory = $this->logDirectory;
        $cutoffTime = time() - ($retentionDays * 86400);

        if (is_dir($directory)) {
            $files = glob($directory . '/app-*.log');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    @unlink($file);
                }
            }
        }
    }
}
