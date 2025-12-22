<?php

declare(strict_types=1);

namespace Core\Services;

class IdnConverterService
{
    /**
     * Converts an email domain to Punycode (ASCII-compatible encoding).
     *
     * @param string $domain The domain part of an email (e.g., "例子.中国")
     * @return string The Punycode-encoded domain (e.g., "xn--fsqu46a.xn--fiqs8s")
     */
    public function toAscii(string $domain): string
    {
        $punyDomain = idn_to_ascii(
            $domain,
            IDNA_NONTRANSITIONAL_TO_ASCII,
            INTL_IDNA_VARIANT_UTS46
        );

        return $punyDomain !== false ? $punyDomain : $domain;
    }

    /**
     * Converts a Punycode domain back to Unicode.
     *
     * @param string $domain The Punycode domain (e.g., "xn--fsqu00a.xn--fiqs8s")
     * @return string The Unicode domain (e.g., "例子.中国")
     */
    public function toUnicode(string $domain): string
    {
        $unicodeDomain = idn_to_utf8(
            $domain,
            IDNA_NONTRANSITIONAL_TO_UNICODE,
            INTL_IDNA_VARIANT_UTS46
        );

        return $unicodeDomain !== false ? $unicodeDomain : $domain;
    }



    /**
     * Converts a full email address to Punycode if needed.
     *
     * @param string $email The email address (e.g., "user@例子.中国")
     * @return string The normalized email (e.g., "user@xn--fsqu46a.xn--fiqs8s")
     */
    public function normalizeEmail(string $email): string
    {
        if (strpos($email, '@') !== false) {
            [$local, $domain] = explode('@', $email, 2);
            return $local . '@' . $this->toAscii($domain);
        }

        return $email;
    }

    /**
     * Converts a Punycode email back to Unicode email.
     *
     * @param string $email The Punycode email (e.g., "user@xn--fsqu46a.xn--fiqs8s")
     * @return string The Unicode email (e.g., "user@例子.中国")
     */
    public function denormalizeEmail(string $email): string
    {
        if (strpos($email, '@') !== false) {
            [$local, $domain] = explode('@', $email, 2);
            return $local . '@' . $this->toUnicode($domain);
        }

        return $email;
    }


    /**
     * Ensures the string is valid UTF-8.
     *
     * @param string $string
     * @return string
     */
    public function ensureUtf8(string $string): string
    {
        // If it's already valid UTF-8, return as is.
        if (mb_check_encoding($string, 'UTF-8')) {
            return $string;
        }

        // Attempt to detect common encodings.
        // It's crucial to put 'UTF-8' first in the list for mb_detect_encoding
        // to prioritize it if it's a valid candidate, even if mb_check_encoding failed.
        $detectedEncoding = mb_detect_encoding($string, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

        // If an encoding was detected, convert from it.
        // If 'UTF-8' was detected here, but mb_check_encoding failed,
        // converting UTF-8 to UTF-8 can sometimes fix encoding issues without corruption.
        if ($detectedEncoding) {
            return mb_convert_encoding($string, 'UTF-8', $detectedEncoding);
        }

        // If no encoding could be reliably detected and mb_check_encoding failed,
        // fall back to converting from ISO-8859-1 as a last resort,
        // as this was the original heuristic.
        return mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
    }

}
