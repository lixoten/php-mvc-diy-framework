<?php

declare(strict_types=1);

namespace Core\Formatters;

use Core\Services\IdnConverterService;

/**
 * Email formatter for formatting (and optionally masking) email addresses.
 *
 * @note This class does NOT validate or sanitize email addresses.
 *       All email arriving here are valid emails strings.
 */
class EmailFormatter extends AbstractFormatter
{
    public function __construct(
        private IdnConverterService $idnConverter,
    ) {
        // $this->idnConverter = $idnConverter;
    }

    public function getName(): string
    {
        return 'email';
    }

    public function supports(mixed $value): bool
    {
        return is_string($value) || $value === null;
    }


    public function transform(mixed $value, array $options = [], mixed $originalValue = null): string
    {
        $options = $this->mergeOptions($options);

        if (empty($value)) {
            return '';
        }

        // ✅ Cast to string and ensure UTF-8 encoding for robust multi-byte string handling.
        $value = (string) $value;
        $value = $this->idnConverter->ensureUtf8($value);

        // If masking is enabled, we need to operate on the user-friendly (Unicode) version
        // of the email, and then denormalize any Punycode TLDs that might have been
        // preserved or introduced during masking.
        if (isset($options['mask']) && $options['mask']) {
            // ✅ First, ensure the email (especially the domain part) is denormalized to Unicode
            // before applying the masking logic for user readability.
            $denormalizedForMasking = $this->idnConverter->denormalizeEmail($value);
            // ✅ Mask the now-denormalized email. The maskEmail method will handle
            // multi-byte characters and domain segments appropriately.
            return $this->maskEmail($denormalizedForMasking);
        }

        // If not masking, always normalize to Punycode for internal consistency
        // and then denormalize for display (converting Punycode domain back to Unicode).
        // This ensures IDN domains are always shown in Unicode when not masked.
        return $this->idnConverter->denormalizeEmail(
            $this->idnConverter->normalizeEmail($value)
        );
    }

    private function maskEmail(string $email): string
    {
        // ✅ This method now receives an email where the domain is already denormalized to Unicode
        // (if it was an IDN). It can now mask the Unicode characters directly.
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email; // Not a valid email format for masking
        }

        [$local, $domain] = $parts;

        // ✅ Use mb_strlen for multi-byte safe length calculation of the local part.
        $localLength = mb_strlen($local, 'UTF-8');
        $domainSegments = explode('.', $domain);

        // Special case: "a@b.com" (single character local, single character domain segment, 3-char TLD)
        // ✅ Check lengths using multi-byte safe functions for consistency.
        if (
            $localLength === 1 &&
            count($domainSegments) === 2 &&
            mb_strlen($domainSegments[0], 'UTF-8') === 1 &&
            mb_strlen($domainSegments[1], 'UTF-8') === 3
        ) {
            return $email; // Return unmasked
        }

        // ✅ Mask local part (multi-byte safe using mb_substr).
        $localMasked = mb_substr($local, 0, 1, 'UTF-8');
        if ($localLength > 1) {
            $localMasked .= str_repeat('*', $localLength - 1);
        }

        // Mask domain part, preserving dots.
        // The domain is now in its Unicode form (if it was an IDN).
        $domainMaskedSegments = [];
        if (count($domainSegments) > 1) {
            $tld = array_pop($domainSegments); // TLD might be Unicode (e.g., '中国')
            foreach ($domainSegments as $segment) {
                if ($segment === '') {
                    $domainMaskedSegments[] = '';
                    continue;
                }
                // ✅ Use mb_strlen and mb_substr for domain segments as well (now operating on Unicode).
                $segmentLength = mb_strlen($segment, 'UTF-8');
                $masked = mb_substr($segment, 0, 1, 'UTF-8');
                if ($segmentLength > 1) {
                    $masked .= str_repeat('*', $segmentLength - 1);
                }
                $domainMaskedSegments[] = $masked;
            }
            $domainMasked = implode('.', $domainMaskedSegments) . '.' . $tld;
        } else { // Handle single-part domain (e.g., 'localhost' or '例子' if it was a single domain)
            $segment = $domainSegments[0];
            // ✅ Use mb_strlen and mb_substr for single-part domain (now operating on Unicode).
            $segmentLength = mb_strlen($segment, 'UTF-8');
            $domainMasked = mb_substr($segment, 0, 1, 'UTF-8');
            if ($segmentLength > 1) {
                $domainMasked .= str_repeat('*', $segmentLength - 1);
            }
        }

        return $localMasked . '@' . $domainMasked;
    }


    /**
     * Formats an email address, with optional masking for privacy.
     *
     * @param mixed $value The email address to format.
     * @param array{ mask?: bool } $options
     *   - 'mask': If true, masks part of the email (e.g., u***@e****.com or u***@l******).
     * @param mixed $originalValue The original value (unused).
     * @return string The formatted (optionally masked) email address.
     *
     */
    public function xxxtransform(mixed $value, array $options = [], mixed $originalValue = null): string
    {
        $options = $this->mergeOptions($options);

        if (empty($value)) {
            return '';
        }

        // Normalize Unicode email to Punycode before masking
        $normalizedEmail = $this->idnConverter->normalizeEmail($value);

        $text = $normalizedEmail; // Work with ASCII

        // // But display the Unicode version
        // $displayEmail = $this->idnConverter->denormalizeEmail($normalizedEmail);


        // // $text = (string)$value;
        // $text = $displayEmail;



        if (isset($options['mask']) && $options['mask']) {
            $text = $this->maskEmail($text); // Mask ASCII
        }

        return $this->idnConverter->denormalizeEmail($text);
    }



    /**
     * Masks an email address for privacy.
     *
     * Shows the first character of the local part and the first character of the domain part,
     * replacing remaining characters with asterisks. The TLD (last segment after the final dot) is preserved.
     *
     * Special handling for very short emails:
     * - If the email is of the form "a@b.com" (i.e., local part and domain part are each a single character,
     *   and the TLD is three characters), the email is returned unmasked for readability.
     *
     * Examples:
     * - user@example.com      → u***@e*****.com
     * - user@sub.example.com → u***@s***********.com
     * - a@b.com              → a@b.com (no masking applied)
     * - user@localhost       → u***@l*******
     *
     *  DOTS ARE FUCKING VISIBLE and should be visible
     *
     * @param string $email The email address to mask.
     * @return string The masked email address.
     */


    protected function getDefaultOptions(): array
    {
        return [
            'mask' => false,
        ];
    }
}
