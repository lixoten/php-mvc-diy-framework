<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Formatters\FormatterRegistry;
use Core\Exceptions\FormatterNotFoundException;
use Core\Formatters\FormatterInterface;
use Psr\Log\LoggerInterface;

/**
 * Main service for formatting operations using the Strategy pattern
 *
 * Acts as the context in the Strategy pattern, coordinating with the
 * FormatterRegistry to apply appropriate formatting strategies.
 */
class FormatterService
{
    private FormatterRegistry $registry;
    private ?LoggerInterface $logger;

    /**
     * @var array<string, string> Cache for formatted values
     */
    private array $cache = [];

    private bool $cacheEnabled = true;

    public function __construct(
        FormatterRegistry $registry,
        ?LoggerInterface $logger
    ) {
        $this->registry = $registry;
        $this->logger = $logger;
    }


    // /**
    //  * Register a formatter (either a FormatterInterface instance or a callable).
    //  *
    //  * @param string $name The name to register the formatter under.
    //  * @param FormatterInterface|callable $formatter The formatter instance or callable.
    //  * @throws \InvalidArgumentException If the formatter is neither a FormatterInterface nor callable.
    //  */
    // public function registerFormatter(string $name, FormatterInterface|callable $formatter): void
    // {
    //     if (!($formatter instanceof FormatterInterface) && !is_callable($formatter)) {
    //         throw new \InvalidArgumentException(
    //             "Formatter must be an instance of FormatterInterface or a callable, got " . gettype($formatter)
    //         );
    //     }
    //     $this->registry->set($name, $formatter);
    // }


    /**
     * Format a value using the specified formatter
     *
     * @param string $formatterName The formatter to use
     * @param mixed $value The value to format
     * @param array<string, mixed> $options Additional formatting options
     * @return string The formatted value
     * @throws FormatterNotFoundException If formatter not found
     */
    public function format(string $formatterName, mixed $value, array $options = []): string
    {
        // Generate cache key if caching is enabled
        $cacheKey = null;
        if ($this->cacheEnabled) {
            $cacheKey = $this->generateCacheKey($formatterName, $value, $options);
            if (isset($this->cache[$cacheKey])) {
                return $this->cache[$cacheKey];
            }
        }

        try {
            $formatter = $this->registry->get($formatterName);

            if ($formatter instanceof FormatterInterface) {
                // Check if formatter supports the value type
                if (!$formatter->supports($value)) {
                    $this->logger?->warning(
                        'Formatter does not support value type',
                        [
                            'formatter' => $formatterName,
                            'value_type' => gettype($value),
                            'value' => $value
                        ]
                    );

                    return $this->getFallbackValue($value);
                }

                $result = $formatter->format($value, $options);

            // } elseif (is_callable($formatter)) {
            //     // Execute callable directly
            //     $result = $formatter($value, $options);
            } else {
                throw new \InvalidArgumentException('Invalid formatter type');
            }


            // Cache the result
            if ($this->cacheEnabled && $cacheKey !== null) {
                $this->cache[$cacheKey] = $result;
            }

            return $result;
        } catch (FormatterNotFoundException $e) {
            $this->logger?->error(
                'Formatter not found, using fallback',
                [
                    'formatter' => $formatterName,
                    'value' => $value,
                    'error' => $e->getMessage()
                ]
            );

            return $this->getFallbackValue($value);
        }
    }

    /**
     * Try to format with fallback to default if formatter not found
     *
     * @param string $formatterName Primary formatter to try
     * @param mixed $value The value to format
     * @param array<string, mixed> $options Formatting options
     * @param string $fallbackFormatter Fallback formatter name
     * @return string The formatted value
     */
    public function formatWithFallback(
        string $formatterName,
        mixed $value,
        array $options = [],
        string $fallbackFormatter = 'text'
    ): string {
        try {
            return $this->format($formatterName, $value, $options);
        } catch (FormatterNotFoundException $e) {
            $this->logger?->info(
                'Using fallback formatter',
                [
                    'primary' => $formatterName,
                    'fallback' => $fallbackFormatter,
                    'value' => $value
                ]
            );

            return $this->format($fallbackFormatter, $value, $options);
        }
    }

    /**
     * Get available formatter names
     *
     * @return array<string> Array of available formatter names
     */
    public function getAvailableFormatters(): array
    {
        return $this->registry->getNames();
    }

    /**
     * Check if a formatter is available
     *
     * @param string $formatterName The formatter name to check
     * @return bool True if formatter is available
     */
    public function hasFormatter(string $formatterName): bool
    {
        return $this->registry->has($formatterName);
    }

    /**
     * Enable or disable caching
     *
     * @param bool $enabled Whether to enable caching
     */
    public function setCacheEnabled(bool $enabled): void
    {
        $this->cacheEnabled = $enabled;

        if (!$enabled) {
            $this->clearCache();
        }
    }

    /**
     * Clear the formatting cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Generate a cache key for the given parameters
     *
     * @param string $formatterName The formatter name
     * @param mixed $value The value to format
     * @param array<string, mixed> $options Formatting options
     * @return string The cache key
     */
    private function generateCacheKey(string $formatterName, mixed $value, array $options): string
    {
        return md5($formatterName . serialize($value) . serialize($options));
    }

    /**
     * Get a fallback string representation of a value
     *
     * @param mixed $value The value to convert
     * @return string String representation of the value
     */
    private function getFallbackValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value) || is_object($value)) {
            return '[Complex Value]';
        }

        return (string) $value;
    }
}
