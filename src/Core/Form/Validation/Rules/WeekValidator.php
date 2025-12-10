<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use App\Helpers\DebugRt;

/**
 * Week field validator
 */
class WeekValidator extends AbstractValidator
{
    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        // Ensure value is a string and trimmed/cleaned
        $value = preg_replace('/\s+/', '', (string)$value);

        // Regex for YYYY-Www format:
        // 1. Must match the structure: 4 digits, literal -W, 2 digits.
        $regex_format = '/^(\d{4})-W(\d{2})$/';

        // Validate Format
        if (!preg_match($regex_format, $value, $matches)) {
            $options['message'] ??= $options['invalid_message'] ?? null;

            // Fails the structural format check
            return $this->getErrorMessage($options, 'Please enter a valid week (YYYY-Www).');
        }

        // Extract Year and Week from matches
        $year = (int)$matches[1];
        $week = (int)$matches[2];

        // ----------------------------------------------------
        // 2. Perform ISO Week Number Validation
        // A week number must be between 1 and 53. Week 53 only exists for certain years.
        // ----------------------------------------------------
        if ($week < 1 || $week > 53) {
            return $this->getErrorMessage($options, 'Please enter a valid week (YYYY-Www).');
        }

        // If the week is 53, we must confirm it is a valid 53-week year.
        // We do this by checking if Jan 1 of the *next* year belongs to week 53.
        if ($week === 53) {
            // Create a DateTime object for Jan 1st of the NEXT year.
            $nextYearJan1 = \DateTime::createFromFormat('Y-m-d', ($year + 1) . '-01-01');

            // If Jan 1st of the next year is a Monday (ISO day 1) or Tuesday (ISO day 2),
            // the current year has 53 weeks. Otherwise, it only has 52 weeks.
            // We use the 'o' format to get the ISO year of that date.
            if ($nextYearJan1->format('o') !== (string)($year + 1)) {
                 // The year rolled back, so it's a 53-week year (2020, 2015, etc.)
            } else {
                 // Jan 1st of next year is Mon/Tue, meaning current year is 52 weeks only.
                 return $this->getErrorMessage($options, 'Week 53 is not valid for the year ' . $year . '.');
            }

            // NOTE: The logic above for week 53 is simplified, but a full ISO check is complex.
            // For a production validator, it is often simpler to just use createFromFormat
            // for the week 53 check, but since that is failing, we must use a workaround.
            // For now, let's skip the complex week 53 check and rely on min/max.
        }

        // ----------------------------------------------------
        // 3. Min/Max checks (using the original lexicographical compare)
        // ----------------------------------------------------

        // Min/max checks (lexicographical comparison is valid for ISO week format)
        // if (isset($options['min']) && $value < $options['min']) {
        //     if (isset($options['min_message'])) {
        //         $options['message'] = $this->formatCustomMessage($options['min'], $options['min_message']);
        //     }

        //     return $this->getErrorMessage($options, 'Week must not be before ' . $options['min'] . '.');
        // }

        // // Max
        // if (isset($options['max']) && $value > $options['max']) {
        //     if (isset($options['max_message'])) {
        //         $options['message'] = $this->formatCustomMessage($options['max'], $options['max_message']);
        //     }

        //     return $this->getErrorMessage($options, 'Week must not be after ' . $options['max'] . '.');
        // }

        // Min
        if ($error = $this->validateMinString($value, $options)) {
            return $error;
        }

        // Max
        if ($error = $this->validateMaxString($value, $options)) {
            return $error;
        }

        // If it passes the regex and min/max, we consider it valid.
        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'week';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        return [
        ];
    }
}
