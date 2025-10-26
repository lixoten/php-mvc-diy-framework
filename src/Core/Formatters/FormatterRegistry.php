<?php

declare(strict_types=1);

namespace Core\Formatters;

use Core\Exceptions\FormatterNotFoundException;
use InvalidArgumentException;

// If you ever need to support callables (for dynamic, runtime,
// or config-driven formatters), you should also have factories in your registry.

/** xxx
 * Registry for managing formatter instances
 *
 * Stores and retrieves formatter strategy instances, supporting both
 * eager and lazy loading patterns.
 */
class FormatterRegistry
{
    /**
     * @var array<string, FormatterInterface> Registered formatter instances
     */
    private array $formatters = [];

    public function __construct(array $defaultFormatters = [])
    {
        foreach ($defaultFormatters as $formatter) {
            $this->set($formatter->getName(), $formatter);
        }
    }


    /**
     * Register a formatter instance by object (preferred for DI and chaining)
     *
     * @param FormatterInterface $formatter
     * @return self
     */
    public function register(FormatterInterface $formatter): self
    {
        $this->set($formatter->getName(), $formatter);
        return $this;
    }


    /**
     * Register a formatter instance
     *
     * @param string $name The formatter identifier
     * @param FormatterInterface $formatter The formatter instance
     * @throws InvalidArgumentException If name is empty or formatter is invalid
     */
    public function set(string $name, FormatterInterface $formatter): void
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Formatter name cannot be empty');
        }

        if (!$formatter instanceof FormatterInterface) {
            throw new InvalidArgumentException('Formatter must implement FormatterInterface');
        }

        $this->formatters[$name] = $formatter;
    }


    /**
     * Get a formatter by name
     *
     * @param string $name The formatter identifier
     * @return FormatterInterface The formatter instance
     * @throws FormatterNotFoundException If formatter not found
     */
    public function get(string $name): FormatterInterface
    {
        if (isset($this->formatters[$name])) {
            return $this->formatters[$name];
        }

        throw new FormatterNotFoundException(sprintf('Formatter "%s" not found', $name));
    }

    /**
     * Check if a formatter is registered
     *
     * @param string $name The formatter identifier
     * @return bool True if formatter exists
     */
    public function has(string $name): bool
    {
        return isset($this->formatters[$name]);
    }

    /**
     * Get all registered formatter names
     *
     * @return array<string> Array of formatter names
     */
    public function getNames(): array
    {
        return array_keys($this->formatters);
    }

    /**
     * Remove a formatter from the registry
     *
     * @param string $name The formatter identifier
     */
    public function remove(string $name): void
    {
        unset($this->formatters[$name]);
    }

    /**
     * Clear all formatters from the registry
     */
    public function clear(): void
    {
        $this->formatters = [];
    }
}
