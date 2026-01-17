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


    /** {@inheritdoc} */
    public function get(string $key, array $replacements = [], string $pageName = null, bool $htmlSafe = true): string
    {
        $keySegments =  explode('.', $key);

        if (isset($pageName)) {
            $firstPassKey = "$pageName.$key";
        } else {
            $firstPassKey = "common.$key";
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
        $fullSpecificKeyParts = [];

        // Build the specific key path dynamically, handling 'base' replacement
        foreach (explode('.', $firstPassKey) as $part) {
            $fullSpecificKeyParts[] = $part;
        }
        $specificLookupKey = implode('.', $fullSpecificKeyParts);

        // Attempt to find the specific translation using the dynamically resolved key
        $resolvedValue = $resolvePath($this->translations, $specificLookupKey);
        if (is_string($resolvedValue)) { // findme - c lang
            $current = $resolvedValue . '*';  // findme - * lang
        } //"image.filename.validation.invalid_mime"
          //   "image.title.validation.required"

        if (is_string($current)) {
            // return $this->replacePlaceholders($current, $replacements);
            return $this->applyHtmlSafety(
                $this->replacePlaceholders($current, $replacements),
                $htmlSafe
            );
        }

        // For Validation that is not found.
        if (isset($keySegments[1]) && $keySegments[1] === 'validation') {
            unset($keySegments[0]);
        }
        array_unshift($keySegments, 'common');
        $specificLookupKey = implode('.', $keySegments);
        $resolvedValue = $resolvePath($this->translations, $specificLookupKey);
        if (is_string($resolvedValue)) {
            $current = $resolvedValue . '~'; // findme - ~ lang
        }
        if (is_string($current)) {
            // return $this->replacePlaceholders($current, $replacements);
            return $this->applyHtmlSafety(
                $this->replacePlaceholders($current, $replacements),
                $htmlSafe
            );
        }

        return 'NF_' . $key;
    }


    /**
     * Apply HTML safety escaping to the translation string if enabled.
     *
     * When `$htmlSafe` is true, the string is escaped using `htmlspecialchars()` to prevent
     * XSS attacks. When false, the string is returned as-is, allowing raw HTML content.
     *
     * @param string $translation The translation string to apply HTML safety to
     * @param bool $htmlSafe Whether to escape HTML entities (default: true)
     * @return string The translation string, optionally HTML-escaped
     */
    private function applyHtmlSafety(string $translation, bool $htmlSafe): string
    {
        if ($htmlSafe === true) {
            return htmlspecialchars($translation, ENT_QUOTES, 'UTF-8');
        }

        return $translation;
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
}

