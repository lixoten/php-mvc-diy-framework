<?php

/**
 * Code Lookup Service Interface
 *
 * Defines the contract for a service that provides code-to-label mappings
 * and related lookup functionality from a centralized configuration source.
 *
 * This interface enables the application to work with code lookups (e.g., gender codes,
 * payment types, status codes) in a consistent and framework-neutral manner.
 *
 * Design Principles:
 * - ✅ Single Responsibility: Only handles code lookup and providing translation keys.
 * - ✅ Dependency Inversion: Depends on ConfigInterface abstractions.
 * - ✅ Framework Neutrality: Returns semantic values (e.g., 'info', 'success') that renderers map to theme-specific classes.
 * - ✅ Translatability: All labels are translation keys, to be processed by a TranslatorInterface.
 *
 * @see CodeLookupService for the concrete implementation
 * @see src/Config/app_lookups.php for the centralized code definitions
 *
 * @package Core\Interfaces
 * @author  MVC LIXO Framework
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Core\Interfaces;

interface CodeLookupServiceInterface
{
    /**
     * Get the translation key for a specific code within a type.
     *
     * This method retrieves the translation key from the config. The consumer
     * (e.g., a renderer) is responsible for passing this key to a TranslatorInterface
     * for localization.
     *
     * @param string      $type     The lookup type (e.g., 'gender', 'payment_type')
     * @param string      $code     The code to look up (e.g., 'f', 'CC')
     * @param string|null $pageName Optional page name for translation context (currently unused, kept for consistency)
     *
     * @return string The raw translation key (e.g., "gender.female", "payment_type.credit_card")
     *
     * @throws \InvalidArgumentException If the type or code does not exist in config
     *
     * @example
     * ```php
     * $labelKey = $codeLookupService->getLabel('gender', 'f');
     * // Returns: "gender.female"
     * ```
     */
    public function getLabel(string $type, string $code, ?string $pageName = null): string;

    /**
     * Get all properties for a specific code within a type.
     *
     * Returns the raw array of properties (label key, variant, icon, hex, etc.)
     * from the config. The 'label' property will be a translation key, and it's up
     * to the consumer (e.g., a formatter or renderer) to translate it for display.
     *
     * @param string $type The lookup type (e.g., 'gender', 'payment_type')
     * @param string $code The code to look up (e.g., 'f', 'CC')
     *
     * @return array<string, mixed> Associative array of properties, with 'label' as a translation key
     *
     * @throws \InvalidArgumentException If the type or code does not exist in config
     *
     * @example
     * ```php
     * $props = $codeLookupService->getProperties('gender', 'f');
     * // Returns: ['label' => 'gender.female', 'variant' => 'primary']
     * ```
     */
    public function getProperties(string $type, string $code): array;

    /**
     * Get all options for a specific type as an associative array.
     *
     * Returns an array suitable for select dropdowns or radio buttons,
     * with the code as the key and the raw translation key as the value.
     * The consumer (e.g., a renderer) is responsible for translating these labels for display.
     *
     * @param string      $type     The lookup type (e.g., 'gender', 'payment_type')
     * @param string|null $pageName Optional page name for translation context (currently unused, kept for consistency)
     *
     * @return array<string, string> Associative array [code => translation key]
     *
     * @throws \InvalidArgumentException If the type does not exist in config
     *
     * @example
     * ```php
     * $options = $codeLookupService->getOptions('gender');
     * // Returns: ['m' => 'gender.male', 'f' => 'gender.female', 'o' => 'gender.other', 'nb' => 'gender.non_binary']
     * ```
     */
    public function getOptions(string $type, ?string $pageName = null): array;

    /**
     * Get all valid codes for a specific type.
     *
     * Returns an array of all valid codes for the given type,
     * useful for validation purposes.
     *
     * @param string $type The lookup type (e.g., 'gender', 'payment_type')
     *
     * @return array<string> Array of valid code strings
     *
     * @throws \InvalidArgumentException If the type does not exist in config
     *
     * @example
     * ```php
     * $validCodes = $codeLookupService->getValidCodes('gender');
     * // Returns: ['m', 'f', 'o', 'nb']
     * ```
     */
    public function getValidCodes(string $type): array;

    /**
     * Get all valid codes for a specific type.
     *
     * Returns an array of all valid codes for the given type,
     * useful for validation purposes.
     *
     * @param string $type The lookup type (e.g., 'gender', 'payment_type')
     *
     * @return array<string> Array of valid code strings
     *
     * @throws \InvalidArgumentException If the type does not exist in config
     *
     * @example
     * ```php
     * $validCodes = $codeLookupService->getValidCodes('gender');
     * // Returns: ['m', 'f', 'o', 'nb']
     * ```
     */
    public function hasType(string $type): bool;

    /**
     * Check if a specific code exists within a type.
     *
     * @param string $type The lookup type (e.g., 'gender', 'payment_type')
     * @param string $code The code to check (e.g., 'f', 'CC')
     *
     * @return bool True if the code exists within the type, false otherwise
     *
     * @example
     * ```php
     * if ($codeLookupService->hasCode('gender', 'f')) {
     *     // Valid gender code
     * }
     * ```
     */
    public function hasCode(string $type, string $code): bool;


    /**
     * Get formatter options for a specific code (with label as translation key).
     *
     * This method is specifically designed for use in field config files as an
     * 'options_provider'. It returns an array with the label as a translation key
     * and other properties, ready for consumption by formatters. The formatter
     * is responsible for translating the 'label' property for display.
     *
     * @param string      $type     The lookup type (e.g., 'gender', 'payment_type')
     * @param mixed       $value    The code value to format (e.g., 'f', 'CC')
     * @param string|null $pageName Optional page name for translation context (currently unused, kept for consistency)
     *
     * @return array<string, mixed> Array with 'label' (translation key) and other properties
     *
     * @example
     * ```php
     * // In testy_fields_root.php:
     * 'gender_id' => [
     *     'list' => [
     *         'formatters' => [
     *             'text' => [
     *                 'options_provider' => [CodeLookupServiceInterface::class, 'getFormatterOptions'],
     *                 'options_provider_params' => ['type' => 'gender'],
     *             ],
     *         ],
     *     ],
     * ],
     *
     * // The service returns:
     * // ['label' => 'gender.female', 'variant' => 'primary']
     * // The formatter then translates 'gender.female'
     * ```
     */
    public function getFormatterOptions(string $type, mixed $value, ?string $pageName = null): array;


    /**
     * Get select options for a specific type (wrapper for getOptions).
     *
     * This method is specifically designed for use in field config files as a
     * 'options_provider'. It returns an associative array [code => translation key].
     * The actual translation for display is handled by the renderer.
     *
     * @param string      $type     The lookup type (e.g., 'gender', 'payment_type')
     * @param string|null $pageName Optional page name for translation context (currently unused, kept for consistency)
     *
     * @return array<string, string> Associative array [code => translation key]
     *
     * @example
     * ```php
     * // In testy_fields_root.php:
     * 'gender_id' => [
     *     'form' => [
     *         'type' => 'select',
     *         'options_provider' => [CodeLookupServiceInterface::class, 'getSelectOptions'],
     *         'options_provider_params' => ['type' => 'gender'],
     *     ],
     * ],
     * ```
     */
    public function getSelectOptions(string $type, ?string $pageName = null): array;
}
