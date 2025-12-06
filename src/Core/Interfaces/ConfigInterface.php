<?php

declare(strict_types=1);

namespace Core\Interfaces;

interface ConfigInterface
{
    /**
     * Get a configuration value..
     *
     * Usage:
     * - If $key is a single word (e.g. 'logger'), loads config/logger.php and returns the array.
     * - If $key is dot notation (e.g. 'logger.level'), loads config/logger.php and returns $config['level'].
     * - If the key or f    ile does not exist, returns $default.
     * - If needing folders the $key is a single word (e.g. 'list_fields/posts'), loads config/list_fields/posts.php and returns the array.
     * - If $key uses a slash (e.g. 'list_fields/posts'), loads config/list_fields/posts.php and returns the array.
     * - If $key uses both slash and dot (e.g. 'list_fields/posts.title'), loads config/list_fields/posts.php and returns $config['title'].
     *
     *
     * @param string $key    Config key (file or file.key format)
     * @param mixed $default Default value if config doesn't exist
     *
     * @return mixed         The config value
     */
    public function get(string $key, $default = null): mixed;


    /**
     * Load config from feature-specific path with dot notation support.
     *
     * Loads config files from: src/App/Features/{FeatureName}/Config/
     *
     * Usage:
     * - `getFromFeature('Testy', 'view_testy_edit')` loads entire config file
     * - `getFromFeature('Testy', 'view_testy_edit.render_options.ajax_save')` returns nested value
     * - `getFromFeature('Testy', 'view_testy_edit.metadata.entityName', 'testy')` with fallback
     *
     * @param string $featureName Feature name (e.g., 'Testy', 'Post', 'User')
     * @param string $key Config key in dot notation (e.g., 'view_testy_edit.render_options.ajax_save')
     * @param mixed $default Default value if key not found
     *
     * @return mixed The config value or default
     */
    public function getFromFeature(string $featureName, string $key, mixed $default = null): mixed;



    /**
     * Get nested config value with fallback and logging..
     */
    public function getConfigValue(string $file, string $path, $default = null);


    /**
     * Get the current environment..
     *
     * @return string
     */
    public function getEnvironment(): string;


    /**
     * Get the current configuration directory path.
     *
     * @return string The absolute path to the config directory
     */
    public function getConfigPath(): string;

    /**
     * Get the base src directory path (parent of Config directory).
     *
     * @return string The absolute path to the src directory
     */
    public function getBaseSrcPath(): string;
}
