<?php

declare(strict_types=1);

namespace Core\I18n;

use App\Helpers\DebugRt;

error_reporting(E_ALL);
// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// set_error_handler(function ($severity, $message, $file, $line) {
//     throw new \ErrorException($message, 0, $severity, $file, $line);
// });

class I18nTranslator implements TranslatorInterface
{
    // /**
    //  * @var array<string, mixed> The loaded translations, typically grouped by feature/namespace.
    //  */
    // protected array $translations;

    /**
     * @param array<string, mixed> $translations Namespace-grouped translations
     * @param string $namespaceDelimiter Delimiter for extracting namespace from name
     */
    public function __construct(
        protected array $translations,
        protected string $namespaceDelimiter = '_'
    ) {
        if (empty($translations)) {
            throw new \InvalidArgumentException('Translations array cannot be empty');
        }
    }

    /**
     * Get a translation with optional placeholder replacements.
     *
     * @param string $key Translation key (e.g., 'form.hints.minlength')
     * @param string $name Context name (e.g., 'testy_list')
     * @param array<string, mixed> $replacements Placeholder values (e.g., ['min' => 5])
     * @return string
     */
    public function get(string $key, string $name, array $replacements = []): string
    {
        if (!empty($replacements)) {
            $rrr = $replacements;
        }

        $resolvePath = fn(array $array, string $path): mixed => (function (array $array, string $path): mixed {
            $parts = explode('.', $path);
            $current = $array;
            foreach ($parts as $part) {
                if (!is_array($current) || !isset($current[$part])) {
                    return null; // Path not found
                }
                $current = $current[$part];
            }
            return $current;
        })($array, $path);

        $current = null;
        $useEntity = explode('_', $name)[0];
        $fullSpecificKeyParts = [];

        // Build the specific key path dynamically, handling 'base' replacement
        foreach (explode('.', $key) as $part) {
            $fullSpecificKeyParts[] = $part;
        }
        $specificLookupKey = implode('.', $fullSpecificKeyParts);

        // 1. Attempt to find the specific translation using the dynamically resolved key
        $resolvedValue = $resolvePath($this->translations, $specificLookupKey);
        if (is_string($resolvedValue)) {
            $current = $resolvedValue . ' -' . substr($specificLookupKey, 0, 4);
        }

        /////////////////////////////////////////////////////



        if (is_string($current)) {
            return $this->replacePlaceholders($current, $replacements);
        }


        return 'NF_' . $key;
    }


    /**
     * Replace placeholders in a translation string.
     *
     * @param string $translation The translation string
     * @param array<string, mixed> $replacements Key-value pairs for replacement
     * @return string
     */
    private function replacePlaceholders(string $translation, array $replacements): string
    {
        if (empty($replacements)) {
            return $translation;
        }

        foreach ($replacements as $key => $value) {
            // Support both {key} and %s/%d style placeholders
            $translation = str_replace('{' . $key . '}', (string)$value, $translation);
        }

        // For sprintf-style placeholders (%d, %s, etc.)
        if (!empty($replacements) && preg_match('/%[sdf]/', $translation)) {
            $translation = vsprintf($translation, array_values($replacements));
        }

        return $translation;
    }


    // /**
    //  * Resolve a dot-path against an array, return null when not found.
    //  */
    // private function resolvePath(array $parts): mixed
    // {
    //     $langNamespace = array_shift($parts);

    //     // 1. ✅ Safely retrieve the base array for the namespace
    //     // Check if the namespace exists AND is an array before proceeding
    //     if (!array_key_exists($langNamespace, $this->translations) || !is_array($this->translations[$langNamespace])) {
    //         return null; // Namespace not found or not an array
    //     }

    //     $current = $this->translations[$langNamespace];
    //     foreach ($parts as $part) {
    //         // If $current is not an array (meaning we've hit a scalar value too early in the path)
    //         // or if the key 'part' does not exist in the current array, then the path cannot be resolved.
    //         if (!is_array($current) || !array_key_exists($part, $current)) {
    //             return null; // Path segment not found at this level or path leads to scalar prematurely
    //         }
    //         $current = $current[$part];
    //     }
    //     return $current;
    // }

}

