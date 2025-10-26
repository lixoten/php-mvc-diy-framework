<?php

declare(strict_types=1);

namespace Core\Formatters;

use App\Helpers\DebugRt;
use Core\Services\RegionContextService;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

/**
 * PhoneNumberFormatter
 *
 * Formats phone numbers using libphonenumber with support for multiple output styles.
 *
 * Supported formats:
 * - FORMAT_DEFAULT: Region-aware (national or international)
 * - FORMAT_DASHES: Digits separated by dashes (region-agnostic)
 * - FORMAT_DOTS: Digits separated by dots (region-agnostic)
 * - FORMAT_SPACES: Digits separated by spaces (region-agnostic)
 * - FORMAT_INTERNATIONAL: Always international format
 * - FORMAT_INTERNATIONAL_DASHES: International format, digits separated by dashes
 *
 * @package Core\Formatters
 * @author   Your Name <your@email.com>
 * @see      https://github.com/google/libphonenumber
 *
 * @method string format(mixed $value, array{format?: string, region?: string} $options = [])
 *     Formats a phone number string according to the specified options.
 *
 * @throws \libphonenumber\NumberParseException If the phone number cannot be parsed.
 */
class PhoneNumberFormatter extends AbstractFormatter
{
    private RegionContextService $regionContextService;

    public function __construct(RegionContextService $regionContextService)
    {
        $this->regionContextService = $regionContextService;
    }

    // Define available formats as public constants for discoverability and type safety
    public const FORMAT_DEFAULT = 'default';
    public const FORMAT_DASHES = 'dashes';
    public const FORMAT_DOTS = 'dots';
    public const FORMAT_SPACES = 'spaces';
    public const FORMAT_INTERNATIONAL = 'international';
    public const FORMAT_INTERNATIONAL_DASHES = 'international_dashes';


    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'phone';
    }

    /** {@inheritdoc} */
    public function supports(mixed $value): bool
    {
        return is_string($value) || is_numeric($value);
    }

    /**
     * Formats a phone number string.
     *
     * @param mixed $value The phone number to format.
     * @param array{format?: string, region?: string} $options Formatting options.
     *        - 'format': The desired output format.
     *        - 'region': Optional region code (e.g., 'US') for parsing numbers without a country code.
     * @return string The formatted phone number.
     */
    public function transform(mixed $value, array $options = []): string
    {
        $options = $this->mergeOptions($options);

        if (empty($value)) {
            return '';
        }

        $phoneUtil  = PhoneNumberUtil::getInstance();
        $region = $options['region'] ?? $this->regionContextService->getRegion();

        try {
            // $value = "+15556614567"; // invalid number always
            // $value = "+16612529078";
            $numberProto = $phoneUtil->parse((string)$value, $region);
            $numberCountry = $phoneUtil->getRegionCodeForNumber($numberProto);

            switch ($options['format']) {
                case self::FORMAT_DASHES:
                    $formatted = $phoneUtil->format($numberProto, PhoneNumberFormat::NATIONAL);
                    $formatted = preg_replace('/[^\d]+/', ' ', $formatted);
                    $formatted = preg_replace('/\s+/', ' ', $formatted);
                    $formatted = trim($formatted);
                    $formatted = preg_replace('/\s+/', '-', $formatted);
                    // return trim($formatted);
                    return $formatted;
                case self::FORMAT_DOTS:
                    $formatted = $phoneUtil->format($numberProto, PhoneNumberFormat::NATIONAL);
                    $formatted = preg_replace('/[^\d]+/', ' ', $formatted);
                    $formatted = preg_replace('/\s+/', ' ', $formatted);
                    $formatted = trim($formatted);
                    $formatted = preg_replace('/\s+/', '.', $formatted);
                    // return trim($formatted);
                    return str_replace(' ', '.', $formatted);
                case self::FORMAT_SPACES:
                    $formatted = $phoneUtil->format($numberProto, PhoneNumberFormat::NATIONAL);
                    $formatted = preg_replace('/[^\d]+/', ' ', $formatted);
                    $formatted = preg_replace('/\s+/', ' ', $formatted);
                    $formatted = trim($formatted);
                    return $formatted;
                case self::FORMAT_DEFAULT:
                default:
                    // Use region logic as before
                    $numberCountry = $phoneUtil->getRegionCodeForNumber($numberProto);
                    // if (strtoupper($numberCountry) === strtoupper($region)) {
                    if ($numberCountry !== null && strtoupper($numberCountry) === strtoupper($region)) {
                        $libFormat = PhoneNumberFormat::NATIONAL;
                    } else {
                        $libFormat = PhoneNumberFormat::INTERNATIONAL;
                    }
            }


            return $phoneUtil->format($numberProto, $libFormat);
        } catch (NumberParseException $e) {
            // Fallback: return sanitized digits
            return $this->sanitize($value);
        }
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
            'format' =>  self::FORMAT_DEFAULT
        ];
    }
}
