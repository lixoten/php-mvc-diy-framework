<?php

declare(strict_types=1);

namespace Core\Normalizers;

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

/**
 * Utility for normalizing phone numbers to E.164.
 */
final class PhoneNormalizer
{
    /**
     * Normalize a phone number to E.164 format.
     *
     * @param string $value
     * @param string $region
     * @return string|null
     */
    public static function normalizeToE164(string $value, string $region): ?string
    {
        $phoneUtil = PhoneNumberUtil::getInstance();

        // If the value looks like an international number and doesn't start with '+', add '+'
        if ($value !== '' && $value[0] !== '+' && preg_match('/^\d{11,15}$/', $value)) {
            $value = '+' . $value;
        }

        try {
            $numberProto = $phoneUtil->parse($value, $region);
            if ($phoneUtil->isValidNumber($numberProto)) {
                return $phoneUtil->format($numberProto, PhoneNumberFormat::E164);
            }
        } catch (NumberParseException $e) {
            // Optionally log or handle error
        }
        return null;
    }
}
