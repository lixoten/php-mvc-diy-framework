<?php

/* //TODO - revisit
Suggestions (Optional Enhancements)
--Type Declarations:
You could add type hints for return values (e.g., : mixed for get() in PHP 8+).

Error Logging:
--If a config file is missing, consider logging a warning (if you have a logger).

Config File Validation:
--Optionally, validate config file structure on load for early error detection.

PSR-11 Compliance:
--If you ever want to use this as a container, consider implementing Psr\Container\ContainerInterface (not required, just a thought).

Thread Safety:
--If you ever use this in a threaded environment (rare in PHP), be aware of the static cache.
*/
declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use App\Helpers\DebugRt;

class ConfigService implements ConfigInterface
{
    private $configs = [];
    private $configPath;
    private $environment;
    private $testOverrides = [];

    /**
     * Constructor
     *
     * @param string $configPath Path to config directory
     * @param string $environment Current environment (development, production, etc)
     */
    public function __construct(string $configPath, string $environment = 'development')
    {
        $this->configPath = $configPath;
        $this->environment = $environment;
        // DebugRt::j('1', '', $environment);
    }


    /** {@inheritdoc} */
    public function get(string $key, $default = null): mixed
    {
        // Check for test override first
        if (isset($this->testOverrides[$key])) {
            return $this->testOverrides[$key];
        }

        // DebugRt::p($key);

        // If it's a config group (like "logger"), return the environment-specific config
        if (!str_contains($key, '.')) {
            return $this->getEnvironmentConfig($key);
        }
        // DebugRt::p($key);

        // Handle dot notation keys
        $parts = explode('.', $key);
        $file = array_shift($parts);
        $configKey = implode('.', $parts);

        $value = $this->getConfigValue($file, $configKey, $default);
        return $value;
    }


    /** {@inheritdoc} */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Get environment-specific configuration
     *
     * @param string $file Config file name without extension
     * @return array Configuration array
     */
    private function getEnvironmentConfig(string $file): ?array
    {
        // Cache key includes the environment
        $cacheKey = $this->configPath . "/{$file}_{$this->environment}";

        // Load and cache if not already loaded
        if (!isset($this->configs[$cacheKey])) {
            $path = $this->configPath . "/{$file}.php";

            if (file_exists($path)) {
                $allConfigs = require $path;

                // Check if this is an environment-based config or a flat config
                if (is_array($allConfigs) && array_key_exists($this->environment, $allConfigs)) {
                    // It's an environment-based config
                    $this->configs[$cacheKey] = $allConfigs[$this->environment];
                } else {
                    // It's a flat config, use as is
                    $this->configs[$cacheKey] = $allConfigs;
                }
            } else {
                $this->configs[$cacheKey] = null;
            }
        }

        return $this->configs[$cacheKey];
    }


    /** {@inheritdoc} */
    public function getConfigValue(string $file, string $path, $default = null)
    {
        $config = $this->get($file);

        // Navigate the path segments
        $segments = explode('.', $path);
        $current = $config;

        foreach ($segments as $segment) {
            if (!is_array($current) || !isset($current[$segment])) {
                //$this->logger->notice("Config fallback used: {$file}.{$path} defaulted to " .
                //    (is_string($default) ? $default : gettype($default)));
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }


    ################## For phpunit Testing ########################################
    /**
     * Set test configuration overrides
     *
     * @param string $key Config key in dot notation (e.g., 'security.rate_limits.endpoints')
     * @param mixed $value Value to use during tests
     * @return void
     */
    public function setTestOverride(string $key, $value): void
    {
        $this->testOverrides[$key] = $value;
    }

    /**
     * Clear all test overrides
     *
     * @return void
     */
    public function clearTestOverrides(): void
    {
        $this->testOverrides = [];
    }
}
