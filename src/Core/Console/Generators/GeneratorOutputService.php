<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Core\Interfaces\ConfigInterface;
use Core\Services\PathResolverService;
use RuntimeException;

/**
 * Service responsible for managing the staging output directory for generated files.
 *
 * This service provides a consistent and testable way to determine and ensure the existence
 * of the `src/Generated/{Entity}/` directory, where all new code artifacts are temporarily
 * placed before being moved to their final destinations by the `feature:move` command.
 *
 * By centralizing this file system interaction, generators adhere to the Single Responsibility
 * Principle (SRP) and become easier to unit test without actual file system side effects.
 *
 * @package   MVC LIXO Framework
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2025
 */
class GeneratorOutputService
{
    protected ConfigInterface $config;
    private PathResolverService $pathResolverService;

    /**
     * @param ConfigInterface $config The application configuration service.
     * @param PathResolverService $pathResolverService The service for resolving application paths.
     */
    public function __construct(
        ConfigInterface $config,
        PathResolverService $pathResolverService
    ) {
        $this->config = $config;
        $this->pathResolverService = $pathResolverService;
    }

    /**
     * Get the absolute path to the staging output directory for a given entity.
     * Ensures the directory exists.
     *
     * @param string $featureName The name of the feature (e.g., 'Testy').
     * @return string The absolute path to the feature's staging directory.
     * @throws \RuntimeException If the directory cannot be created or is not a valid directory.
     */
    public function getFeatureGeneratedOutputDir(string $featureName): string
    {
        // Use PathResolverService to get the base generated path
        $outputDir = $this->pathResolverService->getGeneratedEntityPath($featureName);

        // mkdir will handle creation and subsequent is_dir check.
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new RuntimeException("Failed to create output directory: {$outputDir}");
            }
        }

        return $outputDir;
    }

    /**
     * Get a formatted timestamp for generated files.
     *
     * @return string
     */
    public function getGeneratedFileTimestamp(): string
    {
        $appTimezone = $this->config->get('app.timezone', date_default_timezone_get());

        $tz = new \DateTimeZone($appTimezone);
        $dt = new \DateTime('now', $tz);
        return $dt->format('Ymd_His');
    }
}
