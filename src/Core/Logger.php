<?php

namespace Core;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use App\Helpers\DebugRt as Debug;

// myNotes:
// - composer require psr/log
/**
 * PSR-3 compliant logger implementation
 *
 * Provides a fully PSR-3 compliant logging system with advanced features:
 * - Message interpolation for context placeholders
 * - Configurable log levels and minimum thresholds
 * - Daily log rotation and retention policies
 * - Log sampling for high-volume environments
 * - Debug mode for development environments
 *
 * @package     Core
 * @implements  \Psr\Log\LoggerInterface
 * @see         https://www.php-fig.org/psr/psr-3/ PSR-3 Specification
 */
class Logger implements LoggerInterface
{
    /**
     * Log level constants
     */
    public const EMERGENCY = 600;
    public const ALERT     = 550;
    public const CRITICAL  = 500;
    public const ERROR     = 400;
    public const WARNING   = 300;
    public const NOTICE    = 200;
    public const INFO      = 100;
    public const DEBUG     = 0;

    private string $logDirectory;
    private int $minLevel;
    private bool $debugMode = false;
    private float $samplingRate = 1.0; // 1.0 = 100%, 0.1 = 10%

    private static $instanceCounter = 0;
    private $instanceId;

    private string $debugBuffer = '';
    private bool $collectDebugOutput = false;

    /**
     * Constructor
     *
     * @param string|null $logDirectory Directory to store log files
     * @param int $minLevel Minimum log level to record
     * @param bool $debugMode Enable debug mode for visual feedback
     * @param float $samplingRate Sampling rate for non-error logs (0-1)
     */
    public function __construct(
        string $logDirectory = null,
        int $minLevel = self::INFO,
        bool $debugMode = false,
        float $samplingRate = 1.0,
        bool $collectDebugOutput = false
    ) {
        $this->instanceId = ++self::$instanceCounter;

        $this->logDirectory = $logDirectory ?? __DIR__ . '/../../logs';
        $this->minLevel = $minLevel;
        $this->debugMode = $debugMode;
        $this->samplingRate = max(0, min(1, $samplingRate));
        $this->collectDebugOutput = $collectDebugOutput;

        // Ensure log directory exists
        if (!is_dir($this->logDirectory)) {
            if (!mkdir($this->logDirectory, 0755, true)) {
                if ($this->debugMode) {
                    $this->addDebug(
                        "Log directory is not writable: " . $this->logDirectory,
                        'error'
                    );
                }
                throw new \RuntimeException("Cannot create log directory: {$this->logDirectory}");
            }
            if ($this->debugMode) {
                $this->addDebug(
                    "Created log directory: " . $this->logDirectory,
                    'error'
                );
            }
        }

        // Check if directory is writable
        if (!is_writable($this->logDirectory)) {
            if ($this->debugMode) {
                $this->addDebug(
                    "og directory is not writable: " . $this->logDirectory,
                    'error'
                );
            }
        }
    }

    /**
     * Log with an arbitrary level.
     *
     * @param mixed $level   The log level (string from LogLevel or internal integer constant)
     * @param string|mixed $message The message to log (must be convertible to string)
     * @param array $context Additional context information that might be interpolated into message
     * @return void
     * @throws \RuntimeException When unable to write to log file
     */
    public function log($level, $message, array $context = []): void
    {
        // Convert PSR-3 string levels to our internal numeric levels
        if (is_string($level)) {
            $level = $this->convertPsrLevelToInt($level);
        }

        // Skip if below minimum level
        if ($level < $this->minLevel) {
            return;
        }

        // For non-errors, apply sampling
        if ($level < self::ERROR && mt_rand(1, 100) > ($this->samplingRate * 100)) {
            return;
        }

        // Format log message
        $levelName = $this->getLevelName($level);
        $timestamp = date('Y-m-d H:i:s');

        // Process message placeholders with context data
        $message = $this->interpolate($message, $context);

        // Format context data
        $contextData = '';
        if (!empty($context)) {
            $contextData = ' ' . json_encode($context);
        }

        $logMessage = "[$timestamp] [$levelName] $message$contextData\n";

        // Write to file
        $filename = $this->getLogFilePath();

        //error_log("did we reach");
        //Debug::p($this->debugMode);
        // Debug log writing
        if ($this->debugMode) {
            $this->addDebug(
                "<h4>Log</h4>" .
                "Writing log: " . htmlspecialchars($logMessage) .
                "<br>To file: " . $filename
            );
        }

        $result = @file_put_contents($filename, $logMessage, FILE_APPEND);

        if ($result === false) {
            if ($this->debugMode) {
                $this->addDebug(
                    "Failed to write log! Error: " . error_get_last()['message'],
                    'error'
                );
            }
        }
    }

    /**
     * Get the log file path for today
     *
     * @return string
     */
    private function getLogFilePath(): string
    {
        return $this->logDirectory . '/app-' . date('Y-m-d') . '.log';
    }

    /**
     * Set sampling rate for non-error logs
     *
     * @param float $rate Value between 0 and 1
     * @return void
     */
    public function setSamplingRate(float $rate): void
    {
        $this->samplingRate = max(0, min(1, $rate));
    }

    /**
     * Enable/disable debug mode
     *
     * @param bool $debug
     * @return void
     */
    public function setDebugMode(bool $debug): void
    {
        $this->debugMode = $debug;
    }


    /**
     * Enable/disable debug output collection
     *
     * @param bool $collect
     * @return void
     */
    public function setCollectDebugOutput(bool $collect): void
    {
        $this->collectDebugOutput = $collect;
    }


    /**
     * Get debug mode status
     *
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return $this->debugMode;
    }

    /**
     * Clean up old log files
     *
     * @param int $days Number of days to keep logs
     * @return void
     */
    public function cleanupOldLogs(int $days = 30): void
    {
        $cutoffTime = time() - ($days * 86400);

        foreach (glob($this->logDirectory . '/app-*.log') as $file) {
            if (filemtime($file) < $cutoffTime) {
                @unlink($file);
            }
        }
    }

    /**
     * Convert PSR-3 string level to internal numeric level
     *
     * @param string $level
     * @return int
     */
    private function convertPsrLevelToInt(string $level): int
    {
        $levels = [
            LogLevel::EMERGENCY => self::EMERGENCY,
            LogLevel::ALERT     => self::ALERT,
            LogLevel::CRITICAL  => self::CRITICAL,
            LogLevel::ERROR     => self::ERROR,
            LogLevel::WARNING   => self::WARNING,
            LogLevel::NOTICE    => self::NOTICE,
            LogLevel::INFO      => self::INFO,
            LogLevel::DEBUG     => self::DEBUG,
        ];

        return $levels[$level] ?? self::INFO;
    }

    /**
     * Get level name from numeric level
     *
     * @param int $level
     * @return string
     */
    private function getLevelName(int $level): string
    {
        $levels = [
            self::EMERGENCY => 'EMERGENCY',
            self::ALERT     => 'ALERT',
            self::CRITICAL  => 'CRITICAL',
            self::ERROR     => 'ERROR',
            self::WARNING   => 'WARNING',
            self::NOTICE    => 'NOTICE',
            self::INFO      => 'INFO',
            self::DEBUG     => 'DEBUG',
        ];

        foreach ($levels as $levelValue => $levelName) {
            if ($level >= $levelValue) {
                return $levelName;
            }
        }

        return 'INFO'; // Default
    }

    /**
     * System is unusable
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately
     */
    public function alert($message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Critical conditions
     */
    public function critical($message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors
     */
    public function error($message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Warning conditions
     */
    public function warning($message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Normal but significant events
     */
    public function notice($message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Interesting events
     */
    public function info($message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Detailed debug information
     */
    public function debug($message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $message Message with {placeholder} format
     * @param array $context Data to replace placeholders with
     * @return string Interpolated message with placeholders replaced
     */
    protected function interpolate(string $message, array $context): string
    {
        // Build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = (string) $val;
            }
        }

        // Interpolate replacement values into the message and return
        return strtr($message, $replace);
    }


        /**
     * Add debug output to buffer instead of echoing
     */
    private function addDebug(string $message, string $type = 'info'): void
    {
        if (!$this->debugMode) {
            return;
        }

        $bgColor = '#eef';
        $borderColor = '#aad';

        if ($type === 'error') {
            $bgColor = '#fdd';
            $borderColor = '#d00';
        } elseif ($type === 'success') {
            $bgColor = '#dfd';
            $borderColor = '#0d0';
        }

        $html = '<div style="background:' . $bgColor
            . ';padding:5px;margin:5px;border:1px solid ' . $borderColor . ';">';
        $html .= $message;
        $html .= '</div>';

        if ($this->collectDebugOutput) {
            $this->debugBuffer .= $html;
        } else {
            // Store in error log instead of echo
            error_log("[Logger Debug] " . strip_tags($message));
        }
    }

    /**
     * Get and optionally clear the debug buffer
     */
    public function getDebugOutput(bool $clear = true): string
    {
        $output = $this->debugBuffer;

        if ($clear) {
            $this->debugBuffer = '';
        }

        return $output;
    }
}
