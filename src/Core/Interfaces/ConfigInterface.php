<?php

namespace Core\Interfaces;

interface ConfigInterface
{
    /**
     * Get a configuration value
     *
     * @param string $key Config key (file or file.key format)
     * @param mixed $default Default value if config doesn't exist
     * @return mixed The config value
     */
    public function get(string $key, $default = null);

    /**
     * Get the current environment
     *
     * @return string
     */
    public function getEnvironment(): string;
}