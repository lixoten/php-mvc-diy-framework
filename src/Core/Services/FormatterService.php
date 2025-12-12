<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Formatters\FormatterRegistry;
use Core\Exceptions\FormatterNotFoundException;
use Core\Formatters\FormatterInterface;
use Psr\Container\ContainerInterface;
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
    private ?ContainerInterface $container;

    /**
     * @var array<string, string> Cache for formatted values
     */
    private array $cache = [];

    private bool $cacheEnabled = true;

    public function __construct(
        FormatterRegistry $registry,
        ?LoggerInterface $logger,
        ?ContainerInterface $container = null
    ) {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->container = $container;
    }



    /**
     * Format a value using the specified formatter
     *
     * @param string $formatterName The formatter to use
     * @param mixed $value The value to format
     * @param array<string, mixed> $options Additional formatting options
     * @return string The formatted value
     * @throws FormatterNotFoundException If formatter not found
     */
    public function format(
        string $formatterName,
        mixed $value,
        array $options = [],
        mixed $originalValue = null
    ): string {
        $valueToInspectForProvider = $originalValue ?? $value;

        // ✅ STEP 1: Resolve options_provider BEFORE cache key generation
        // This ensures cache keys include resolved options (label/variant)
        if (isset($options['options_provider']) && !is_array($valueToInspectForProvider)) {
            $resolvedOptions = $this->resolveOptionsProvider(
                $options['options_provider'],
                $valueToInspectForProvider,
                $options
            );

            // Merge resolved options (provider output takes precedence)
            $options = array_merge($options, $resolvedOptions);

            // ✅ Remove options_provider from options (formatters don't need it)
            unset($options['options_provider']);
        }


        // Generate cache key if caching is enabled
        $cacheKey = null;
        if ($this->cacheEnabled) {
            $cacheKey = $this->generateCacheKey($formatterName, $value, $options);
            if (isset($this->cache[$cacheKey])) {
                return $this->cache[$cacheKey];
            }
        }

        try {
            // ✅ STEP 1: Get formatter from registry
            // ✅ STEP 1: Get formatter from registry
            // ✅ STEP 1: Get formatter from registry
            // ✅ STEP 1: Get formatter from registry
            // ✅ STEP 1: Get formatter from registry
            // ✅ STEP 1: Get formatter from registry
            // ✅ STEP 1: Get formatter from registry
            // ✅ STEP 1: Get formatter from registry
            $formatter = $this->registry->get($formatterName);

            if (!($formatter instanceof FormatterInterface)) {
                throw new \InvalidArgumentException('Invalid formatter type');
            }

            // ✅ STEP 3: NOW check supports() AFTER value may have been transformed
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

            // ✅ STEP 4: Call formatter->format() to get the result
            $result = $formatter->format($value, $options, $originalValue);
            $rrr = 1;
            // if ($formatter instanceof FormatterInterface) {
            //     // Check if formatter supports the value type
            //     if (!$formatter->supports($value)) {
            //         $this->logger?->warning(
            //             'Formatter does not support value type',
            //             [
            //                 'formatter' => $formatterName,
            //                 'value_type' => gettype($value),
            //                 'value' => $value
            //             ]
            //         );

            //         return $this->getFallbackValue($value);
            //     }

            //     $result = $formatter->format($value, $options);
            // } else {
            //     throw new \InvalidArgumentException('Invalid formatter type');
            // }


            // ✅ STEP 5: Cache the result
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
     * Resolve options_provider callable to concrete options array
     *
     * Handles both service-based providers (CodeLookupService) and static methods (Enum::getFormatterOptions)
     *
     * @param array{0: class-string, 1: string} $provider [ClassName, methodName]
     * @param mixed $value The value to pass to the provider
     * @param array<string, mixed> $context Additional context (e.g., 'lookup_type')
     * @return array<string, mixed> Resolved options (e.g., ['label' => '...', 'variant' => '...'])
     */
    private function resolveOptionsProvider(array $provider, mixed $value, array $context): array
    {
        [$class, $method] = $provider;

        $resolved = []; // ✅ Initialize variable to store result

        // ✅ Service-based provider (e.g., CodeLookupServiceInterface)
        if ($this->container !== null && $this->container->has($class)) {
            try {
                $service = $this->container->get($class);

                if (method_exists($service, $method)) {
                    // CodeLookupService pattern: method(lookupType, value)
                    if (isset($context['lookup_type'])) {
                        $resolved = $service->$method($context['lookup_type'], $value); // ✅ STORE, DON'T RETURN!
                    } else {
                        // Generic pattern: method(value, context)
                        $resolved = $service->$method($value, $context); // ✅ STORE, DON'T RETURN!
                    }
                }
            } catch (\Throwable $e) {
                $this->logger?->error("Service-based options_provider failed", [
                    'service' => $class,
                    'method' => $method,
                    'error' => $e->getMessage(),
                ]);
                return []; // ✅ Only return early on error
            }
        }
        // ✅ Handle static method calls (e.g., TestyStatus::getFormatterOptions)
        elseif (is_callable($provider)) {
            try {
                // $resolved = call_user_func($provider, $value, $context);
                [$class, $method] = $provider;

                // ✅ Validate static method exists (safer than is_callable alone)
                if (!method_exists($class, $method)) {
                    throw new \BadMethodCallException(
                        "Method {$class}::{$method}() does not exist"
                    );
                }

                // ✅ Direct invocation (modern PHP 8.2+ pattern)
                $resolved = $class::$method($value, $context);
            } catch (\Throwable $e) {
                $this->logger?->error("options_provider callable failed", [
                    'provider' => $provider,
                    'error' => $e->getMessage(),
                ]);
                return [];
            }
        } else {
            // ❌ Provider not callable
            $this->logger?->warning("options_provider not callable", [
                'provider' => $provider,
                'value' => $value,
            ]);
            return [];
        }

        // ✅ NOW REACHABLE: Normalize 'translation_key' → 'label' for formatter compatibility
        if (isset($resolved['translation_key']) && !isset($resolved['label'])) {
            $resolved['label'] = $resolved['translation_key'];
            unset($resolved['translation_key']);
        }

        return $resolved;
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
