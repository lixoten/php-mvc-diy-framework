<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use App\Helpers\DebugRt as Debug;

class ConfigService implements ConfigInterface
{
    private $configs = [];
    private $configPath;
    private $environment;

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
    }

    /**
     * Get a configuration value
     *
     * @param string $key Config key (file or file.key format)
     * @param mixed $default Default value if config doesn't exist
     * @return mixed The config value
     */
    public function get(string $key, $default = null)
    {
        // If it's a config group (like "logger"), return the environment-specific config
        if (!str_contains($key, '.')) {
            return $this->getEnvironmentConfig($key);
        }

        // Handle dot notation keys
        $parts = explode('.', $key);
        $file = $parts[0];
        $configKey = $parts[1];

        $config = $this->getEnvironmentConfig($file);
        return $config[$configKey] ?? $default;
    }

    /**
     * Get the current environment
     *
     * @return string
     */
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
    private function getEnvironmentConfig(string $file): array
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
                $this->configs[$cacheKey] = [];
            }
        }

        return $this->configs[$cacheKey];
    }

        /**
     * Get nested config value with fallback and logging
     */
    public function getConfigValue(string $file, string $path, $default = null) {
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
}
