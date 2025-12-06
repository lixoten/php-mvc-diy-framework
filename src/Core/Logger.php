<?php

namespace Core;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use App\Helpers\DebugRt as Debug;
use App\Helpers\DebugRt;

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
    private bool $appInDevelopment = false;

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
        $this->appInDevelopment = ($_ENV['APP_ENV'] ?? 'production') === 'development';

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
                    "Log directory is not writable: " . $this->logDirectory,
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

        // Todo Enhance to allow me to log an error, that i can then use to look up more details.
        // For Dev usage only maybe
        // if (isset($context['error_id'])) {
        //     $context['error_id'] = uniqid('ERR', true);
        //     $message .= " [Error ID: {$context['error_id']}]";
        // }


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

        // $result = @file_put_contents($filename, $logMessage, FILE_APPEND);

// Use fopen/fwrite/fflush for real-time visibility in tools like LogExpert
        // 'a' mode opens the file for writing only; places the file pointer at the end.
        $fp = @fopen($filename, 'a');

        if ($fp !== false) {
            $result = @fwrite($fp, $logMessage);

            // CRITICAL STEP: Manually flush the buffer to disk
            @fflush($fp);

            @fclose($fp);

            if ($result === false) {
                // If fwrite failed
                if ($this->debugMode) {
                    $this->addDebug(
                        "Failed to write log using fwrite! Error: " . error_get_last()['message'],
                        'error'
                    );
                }
            }
        } else {
            // If fopen failed (e.g., permissions)
            if ($this->debugMode) {
                $this->addDebug(
                    "Failed to open log file for writing! File: " . $filename,
                    'error'
                );
            }
        }

// cmd /c "msg %USERNAME% /TIME:10 hello work & exit"

// cmd /c "msg %USERNAME% /TIME:10 WARNING Detected! Line: %L% & exit"











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



    /** {@inheritdoc} */
    public function isAppInDevelopment(): bool
    {
        return $this->appInDevelopment;
    }

    /** {@inheritdoc} */
    public function warningDev(string $plainMessage, string $devWarning, array $context = []): void
    {
        if (!$this->isAppInDevelopment()) {
            // This method should ideally not be called in production.
            // If it is, log a standard error to avoid unintended fatal errors.
            $this->error("Attempted to trigger dev fatal error in non-development environment: " . $plainMessage, $context);
            return;
        }

        // You can integrate context into the message here if desired
        $contextString = '';
        if (!empty($context)) {
            $contextString = "\nContext: " . json_encode($context, JSON_PRETTY_PRINT);
        }

        // ✅ Find the actual caller's file and line number for the message display
        $callerFile = 'unknown_file';
        $callerLine = 'unknown_line';
        // Limit the trace to avoid performance overhead and unnecessary depth
        // $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);


        ## Danger Danger not sure if index 1 will work well, might depend o how deep?
        $i = 0;
        $callerFile       = debug_backtrace()[$i]['file'];
        $callerLine  = debug_backtrace()[$i]['line'];
        if (isset(debug_backtrace()[$i]['class'])) {
            $class      = debug_backtrace()[$i]['class'];
        }
        if (isset(debug_backtrace()[$i]['function'])) {
            $function   = debug_backtrace()[$i]['function'];
        }

        // // Iterate through the trace to find the first relevant caller outside the logger itself
        // foreach ($trace as $frame) {
        //     if (isset($frame['file']) && isset($frame['line'])) {
        //         // Skip frames that are internal to the logger and its direct wrappers
        //         // Adjust this condition if you have other internal wrapper methods that should be skipped
        //         if (str_contains($frame['file'], 'src' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Logger.php')) {
        //             continue;
        //         }
        //         // If you have a specific wrapper in AbstractFieldType, ensure to skip it too.
        //         // Example: if (isset($frame['class']) && $frame['class'] === 'Core\\Form\\Field\\Type\\AbstractFieldType' && $frame['function'] === 'logDevWarning') {
        //         //     continue;
        //         // }

        //         // This frame should be the actual initiator of the warning in your application logic
        //         $callerFile = $frame['file'];
        //         $callerLine = $frame['line'];
        //         break;
        //     }
        // }



        $fullErrorMessage = str_repeat('#', 100) . "<br />\n";
        $fullErrorMessage .= '#### ---------- waaaaaaDEV MODE BUG - CATCH MY EYE ----------' . "<br />\n";
        $fullErrorMessage .= str_repeat('#', 100) . "<br />\n";
        $fullErrorMessage .= "Dev Error #: \"{$devWarning}\" look it up" . "<br />\n";
        $fullErrorMessage .= $plainMessage . $contextString . "<br /><br />\n\n";
        $fullErrorMessage .= "Initiated by: {$callerFile} on line {$callerLine}<br />\n";
        $fullErrorMessage .= str_repeat('#', 100) . "<br /><br />\n\n";

        // error_log(strip_tags($fullErrorMessage)); // Log plain text to actual error log

        // trigger_error($fullErrorMessage, E_USER_WARNING);
        DebugRt::j('0', '', $fullErrorMessage, trace: false, code: $devWarning);
        // Execution stops here due to E_USER_ERROR
    }

    /** {@inheritdoc} */
    public function errorDev(string $plainMessage, string $devError, array $context = []): void
    {
        if (!$this->isAppInDevelopment()) {
            // This method should ideally not be called in production.
            // If it is, log a standard error to avoid unintended fatal errors.
            $this->error("Attempted to trigger dev fatal error in non-development environment: " . $plainMessage, $context);
            return;
        }

        // You can integrate context into the message here if desired
        $contextString = '';
        if (!empty($context)) {
            $contextString = "\nContext: " . json_encode($context, JSON_PRETTY_PRINT);
        }

        $fullErrorMessage = str_repeat('#', 89) . "<br />";
        $fullErrorMessage .= '#### ---------- DEV MODE BUG - CATCH MY EYE ----------' . "<br />";
        $fullErrorMessage .= str_repeat('#', 100) . "<br /><br />";
        $fullErrorMessage .= "Dev Error #: \"{$devError}\" look it up" . "<br />";
        $fullErrorMessage .= $plainMessage . $contextString . "<br /><br />";
        $fullErrorMessage .= str_repeat('#', 100) . "<br /><br />";

        trigger_error($fullErrorMessage, E_USER_ERROR);
        // Execution stops here due to E_USER_ERROR
    }
}
