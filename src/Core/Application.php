<?php

declare(strict_types=1);

namespace Core;

use DI\Container;
use DI\ContainerBuilder;
use InvalidArgumentException;

/**
 * Core application instance.
 *
 * Establishes the base path and registers fundamental paths in the DI container
 * based on configuration, NOT hardcoded assumptions.
 */
final class Application
{
    private string $basePath;
    private Container $container;
    private array $pathConfig;

    /**
     * @param string $basePath Absolute path to the project root
     */
    public function __construct(string $basePath)
    {
        if (!is_dir($basePath)) {
            throw new InvalidArgumentException("Application base path does not exist: {$basePath}");
        }

        $this->basePath = rtrim($basePath, '/\\');

        // ✅ Load path configuration from file (NOT hardcoded)
        $pathConfigFile = $this->basePath . '/src/Config/paths.php';
        if (!file_exists($pathConfigFile)) {
            throw new InvalidArgumentException("Path configuration file not found: {$pathConfigFile}");
        }

        $this->pathConfig = require $pathConfigFile;
    }

    /**
     * Bootstrap the application and build the DI container.
     */
    public function bootstrap(): Container
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);

        // ✅ Register base path and dynamically resolved paths as container parameters
        $definitions = [
            'app.base_path' => $this->basePath,
        ];

        // ✅ Dynamically resolve each configured path
        foreach ($this->pathConfig as $key => $relativePath) {
            $definitions["app.{$key}_path"] = $this->resolvePath($relativePath);
        }

        // Load user-defined DI definitions
        $userDefinitions = require $this->basePath . '/src/dependencies.php';
        $containerBuilder->addDefinitions(array_merge($definitions, $userDefinitions));

        $this->container = $containerBuilder->build();
        return $this->container;
    }

    /**
     * Resolve a relative path against the base path.
     *
     * @param string $relativePath Path relative to project root
     * @return string Absolute path
     */
    private function resolvePath(string $relativePath): string
    {
        // If already absolute, return as-is
        if (preg_match('/^(?:[A-Za-z]:\\\\|\/)/', $relativePath)) {
            return rtrim($relativePath, '/\\');
        }

        return $this->basePath . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
