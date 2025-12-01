<?php

/**
 * Code Lookup Service
 *
 * Concrete implementation of CodeLookupServiceInterface that provides centralized
 * code-to-label mapping functionality by reading from a configuration file
 * (src/Config/app_lookups.php).
 *
 * This service is the single, generic solution for handling all simple code lookups
 * (gender, payment types, status codes, etc.) without requiring dedicated enum
 * classes or provider services for each code type.
 *
 * Design Principles:
 * - ✅ Single Responsibility: Only handles code lookup and providing translation keys.
 * - ✅ Dependency Inversion: Depends on ConfigInterface abstractions.
 * - ✅ Open/Closed: New code types are added via config, not by modifying this class.
 * - ✅ Framework Neutrality: Returns semantic values that renderers map to theme-specific classes.
 * - ✅ Testability: All dependencies injected, fully unit testable.
 *
 * Usage Example:
 * ```php
 * // Get translation key for a label
 * $labelKey = $codeLookupService->getLabel('gender', 'f'); // Returns "gender.female"
 *
 * // Get options for select box (keys => translation keys)
 * $options = $codeLookupService->getOptions('gender'); // ['m' => 'gender.male', 'f' => 'gender.female', ...]
 *
 * // Get properties for formatter (includes label as translation key)
 * $props = $codeLookupService->getProperties('gender', 'f'); // ['label' => 'gender.female', 'variant' => 'primary']
 * ```
 *
 * @see CodeLookupServiceInterface for method documentation
 * @see src/Config/app_lookups.php for centralized code definitions
 *
 * @package Core\Services
 * @author  MVC LIXO Framework
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\CodeLookupServiceInterface;
use Core\Interfaces\ConfigInterface;
use InvalidArgumentException;

class CodeLookupService implements CodeLookupServiceInterface
{
    /**
     * Cached lookups array loaded from config.
     *
     * Structure:
     * [
     *     'gender' => [
     *         'm'  => ['label' => 'gender.male', 'variant' => 'info'],
     *         'f'  => ['label' => 'gender.female', 'variant' => 'primary'],
     *         ...
     *     ],
     *     'payment_type' => [...],
     *     ...
     * ]
     *
     * @var array<string, array<string, array<string, mixed>>>
     */
    private array $lookups = [];

    /**
     * Constructor - Injects dependencies and loads lookup configuration.
     *
     * @param ConfigInterface     $configService Configuration service for loading app_lookups.php
     */
    public function __construct(
        private readonly ConfigInterface $configService,
    ) {
        $this->loadLookups();
    }

    /**
     * Load the centralized lookup configuration from app_lookups.php.
     *
     * This method is called once during construction to cache all lookup data.
     * If the config file doesn't exist or returns invalid data, an exception is thrown.
     *
     * @throws \RuntimeException If app_lookups config cannot be loaded or is invalid
     *
     * @return void
     */
    private function loadLookups(): void
    {
        $lookups = $this->configService->get('app_lookups');

        if (!is_array($lookups)) {
            throw new \RuntimeException(
                'Configuration file "app_lookups.php" must return an array. ' .
                'Expected structure: [\'type\' => [\'code\' => [\'label\' => \'key\', ...]]]'
            );
        }

        $this->lookups = $lookups;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(string $type, string $code, ?string $pageName = null): string
    {
        $properties = $this->getProperties($type, $code);

        if (!isset($properties['label'])) {
            throw new InvalidArgumentException(
                "Code '{$code}' in type '{$type}' does not have a 'label' property defined."
            );
        }

        $translationKey = $properties['label'];

        // Translate the label key using I18nTranslator
        // return $this->translator->get($translationKey, pageName: $pageName);
        return $translationKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties(string $type, string $code): array
    {
        $this->validateType($type);

        if (!isset($this->lookups[$type][$code])) {
            throw new InvalidArgumentException(
                "Code '{$code}' does not exist in lookup type '{$type}'. " .
                "Valid codes: " . implode(', ', array_keys($this->lookups[$type]))
            );
        }

        return $this->lookups[$type][$code];
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices(string $type, ?string $pageName = null): array
    {
        $this->validateType($type);

        $choices = [];

        foreach ($this->lookups[$type] as $code => $properties) {
            // Use getLabel to ensure consistent translation
            // $choices[$code] = $this->getLabel($type, $code, $pageName);
            $choices[$code] = $properties['label'] ?? (string) $code; // Fallback to code if label not set
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidCodes(string $type): array
    {
        $this->validateType($type);

        return array_keys($this->lookups[$type]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasType(string $type): bool
    {
        return isset($this->lookups[$type]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasCode(string $type, string $code): bool
    {
        return $this->hasType($type) && isset($this->lookups[$type][$code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatterOptions(string $type, mixed $value, ?string $pageName = null): array
    {
        // Cast value to string for lookup (DB codes are strings)
        $code = (string) $value;

        // Get all properties for this code
        $properties = $this->getProperties($type, $code);

        // Translate the label
        //$translatedLabel = $this->getLabel($type, $code, $pageName);

        // Return properties with translated label
        // return array_merge($properties, ['label' => $translatedLabel]);
        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectChoices(string $type, ?string $pageName = null): array
    {
        // Semantic wrapper around getChoices() for use in field config 'options_provider'
        return $this->getChoices($type, $pageName);
    }

    /**
     * Validate that a lookup type exists in the configuration.
     *
     * @param string $type The lookup type to validate
     *
     * @throws InvalidArgumentException If the type does not exist
     *
     * @return void
     */
    private function validateType(string $type): void
    {
        if (!$this->hasType($type)) {
            throw new InvalidArgumentException(
                "Lookup type '{$type}' does not exist in configuration. " .
                "Available types: " . implode(', ', array_keys($this->lookups))
            );
        }
    }
}
