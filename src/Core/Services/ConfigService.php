<?php
namespace Core\Services;

use Core\Interfaces\ConfigInterface;

class ConfigService implements ConfigInterface
{
    private $configs = [];
    private $basePath;
    private $environment;

    /**
     * Constructor
     *
     * @param string $basePath Base path to config directory
     * @param string $environment Current environment (development, production, etc)
     */
    public function __construct(string $basePath, string $environment = 'development')
    {
        $this->basePath = $basePath;
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
        $cacheKey = "{$file}_{$this->environment}";

        // Load and cache if not already loaded
        if (!isset($this->configs[$cacheKey])) {
            $path = $this->basePath . "/Config/{$file}.php";

            if (file_exists($path)) {
                $allConfigs = require $path;

                // Get environment-specific config or fall back to production
                $this->configs[$cacheKey] = $allConfigs[$this->environment]
                    ?? $allConfigs['production']
                    ?? [];
            } else {
                $this->configs[$cacheKey] = [];
            }
        }

        return $this->configs[$cacheKey];
    }
}