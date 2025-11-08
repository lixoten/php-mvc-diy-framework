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
    private string $baseSrcPath;

    /**
     * Constructor
     *
     * @param string $configPath Path to config directory (e.g., src/Config)
     * @param string $environment Current environment (development, production, etc)
     */
    public function __construct(string $configPath, string $environment = 'development')
    {
        $this->configPath = $configPath;
        $this->environment = $environment;

        // Store base src path (parent of Config directory)
        $this->baseSrcPath = dirname($configPath);
    }

    /**
     * Get the current configuration directory path.
     *
     * @return string The absolute path to the config directory
     */
    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    /**
     * Get the base src directory path (parent of Config directory).
     *
     * @return string The absolute path to the src directory
     */
    public function getBaseSrcPath(): string
    {
        return $this->baseSrcPath;
    }

    /**
     * Load config from feature-specific path with dot notation support
     *
     * @param string $featureName Feature name (e.g., 'Testy', 'Post')
     * @param string $key Config key in dot notation (e.g., 'view_testy_edit.render_options.ajax_save')
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function getFromFeature(string $featureName, string $key, mixed $default = null): mixed
    {
        // Parse the key to extract file and path
        $parts = explode('.', $key);
        $file = array_shift($parts);
        $configKey = implode('.', $parts);

        // Build feature-specific absolute path
        $featurePath = $this->baseSrcPath . "\\App\\Features\\{$featureName}\\Config\\{$file}.php";

        // Load the full config file
        $config = $this->loadConfigByAbsolutePath($featurePath);

        // If no nested key, return full config
        if (empty($configKey)) {
            return $config ?? $default;
        }

        // Navigate dot notation path
        return $this->getConfigValueFromArray($config, $configKey, $default);
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

        // $value = $this->getConfigValue($file, $configKey, $default);

        // Use getEnvironmentConfig for top-level config files (like 'app', 'database', 'logger')
        // and getConfigValue for nested keys within those files.
        // For simple file names, getEnvironmentConfig will load the file and handle environment sections.
        $configContent = $this->getEnvironmentConfig($file); // This loads the full config array for the file

        return $this->getConfigValueFromArray($configContent, $configKey, $default);
        //return $value;
    }


    /** {@inheritdoc} */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Get environment-specific configuration for a top-level config file.
     * This method is for files like `app.php`, `database.php`, `logger.php` that reside in $this->configPath.
     *
     * @param string $file Config file name without extension (e.g., 'app', 'database').
     * @return array|null Configuration array, or null if file not found.
     */
    private function getEnvironmentConfig(string $file): ?array
    {
        // Cache key includes the environment
        $cacheKey = $this->configPath . "/{$file}_{$this->environment}";

        // // Load and cache if not already loaded
        // if (!isset($this->configs[$cacheKey])) {
        //     $path = $this->configPath . "/{$file}.php";

        //     if (file_exists($path)) {
        //         $allConfigs = require $path;

        //         // Check if this is an environment-based config or a flat config
        //         if (is_array($allConfigs) && array_key_exists($this->environment, $allConfigs)) {
        //             // It's an environment-based config
        //             $this->configs[$cacheKey] = $allConfigs[$this->environment];
        //         } else {
        //             // It's a flat config, use as is
        //             $this->configs[$cacheKey] = $allConfigs;
        //         }
        //     } else {
        //         $this->configs[$cacheKey] = null;
        //     }
        // }
        if (!isset($this->configs[$cacheKey])) {
            $path = $this->configPath . "/{$file}.php";
            $this->configs[$cacheKey] = $this->loadFileContent($path, $this->environment);
        }
        return $this->configs[$cacheKey];
    }


    /** {@inheritdoc} */
    public function getConfigValue(string $file, string $path, $default = null)
    {
    //     $config = $this->get($file);

    //     // Navigate the path segments
    //     $segments = explode('.', $path);
    //     $current = $config;

    //     foreach ($segments as $segment) {
    //         if (!is_array($current) || !isset($current[$segment])) {
    //             //$this->logger->notice("Config fallback used: {$file}.{$path} defaulted to " .
    //             //    (is_string($default) ? $default : gettype($default)));
    //             return $default;
    //         }
    //         $current = $current[$segment];
    //     }

    //     return $current;
        return $this->get($file . '.' . $path, $default);
    }

   /**
     * Load a configuration file by its absolute path.
     * This is used for feature-specific configs that are not in the main src/Config directory.
     *
     * @param string $absoluteFilePath The full absolute path to the config file (e.g., src/App/Features/Testy/Config/field_testy.php).
     * @param string|null $key Optional dot-notation key to retrieve a specific value from the loaded file.
     * @param mixed $default Default value if key not found.
     * @return mixed The full config array, or a specific value if $key is provided.
     * @throws \RuntimeException If the file exists but returns non-array content.
     */
    public function loadConfigByAbsolutePath(string $absoluteFilePath, string $key = null, mixed $default = null): mixed
    {
        // Use the absolute path as the cache key
        $cacheKey = $absoluteFilePath;

        if (!isset($this->configs[$cacheKey])) {
            $this->configs[$cacheKey] = $this->loadFileContent($absoluteFilePath);
        }

        if ($key === null) {
            return $this->configs[$cacheKey]; // Return the full array if no specific key is requested
        }

        // If a key is provided, extract it from the loaded content
        return $this->getConfigValueFromArray($this->configs[$cacheKey], $key, $default);
    }

    /**
     * Helper to load file content and handle caching.
     *
     * @param string $filePath The absolute path to the file.
     * @param string|null $environment If provided, checks for environment-specific sections within the file.
     * @return array|null
     * @throws \RuntimeException If the file exists but returns non-array content.
     */
    private function loadFileContent(string $filePath, ?string $environment = null): ?array
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $content = require $filePath;

        if (!is_array($content)) {
            throw new \RuntimeException("Config file '{$filePath}' must return an array.");
        }

        // If an environment is specified and the config has environment sections
        if ($environment !== null && array_key_exists($environment, $content)) {
            return $content[$environment];
        }

        return $content;
    }

    /**
     * Get a configuration value from a given array using dot notation.
     *
     * @param array|null $config The configuration array to search within.
     * @param string $path The dot-notation path (e.g., 'section.key').
     * @param mixed $default Default value if path not found.
     * @return mixed
     */
    private function getConfigValueFromArray(?array $config, string $path, mixed $default = null): mixed
    {
        if ($config === null) {
            return $default;
        }

        $segments = explode('.', $path);
        $current = $config;

        foreach ($segments as $segment) {
            if (!is_array($current) || !isset($current[$segment])) {
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
