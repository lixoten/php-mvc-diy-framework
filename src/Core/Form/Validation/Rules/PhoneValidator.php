<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use Core\Services\RegionContextService;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;

/**
 * Uber_Phone
 * Validates international phone numbers using libphonenumber.
 */
class PhoneValidator extends AbstractValidator
{
    private RegionContextService $regionContextService;

    public function __construct(RegionContextService $regionContextService)
    {
        $this->regionContextService = $regionContextService;
    }

    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // Uber_Phone
        $phoneUtil = PhoneNumberUtil::getInstance();
        $regionCode = $options['region'] ?? $this->regionContextService->getRegion();

        try {
            // $value = "351912345678"; // this is a hardcoded test
            // $regionCode = "SS";
            $numberProto = $phoneUtil->parse((string)$value, $regionCode);
            $region = $phoneUtil->getRegionCodeForNumber($numberProto); // returns 'AD'
            $isValid = $phoneUtil->isValidNumber($numberProto); // returns false (too many digits)

            if (isset($region) && !$isValid) {
                $example = $phoneUtil->getExampleNumber($region);
                $options['message'] ??= $options['invalid_region_message'] ?? null;

                return $this->getErrorMessage(
                    $options,
                    "Please enter a valid international phone number for
                    this Country { $region } (e.g., {$example}). Region Error."
                );
            }

            if (!$phoneUtil->isValidNumber($numberProto)) {
                $options['message'] ??= $options['invalid_message'] ?? null;

                return $this->getErrorMessage(
                    $options,
                    'Please enter a valid international phone number (e.g., +15551234567). Invalid Error.'
                );
            }
        } catch (NumberParseException $e) {
            $options['message'] ??= $options['invalid_parse_message'] ?? null;

            return $this->getErrorMessage(
                $options,
                'Please enter a valid international phone number (e.g., +15551234567). Parse Error.'
            );
        }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'phone';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
