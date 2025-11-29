<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Form\Validation\Rules\RadioGroupValidator.php
declare(strict_types=1);

namespace Core\Form\Validation\Rules;

/**
 * Radio Group field validator
 *
 * Validates that a selection is made within a radio button group,
 * especially if the field is marked as required.
 */
class RadioGroupValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'radio_group';
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value The submitted value for the radio group.
     * @param array<string, mixed> $options Validation options.
     *                                      Expected to contain 'required' (bool) and 'choices' (array<string, string>).
     * @return ?string Null if validation passes, otherwise an error message.
     */
    public function validate(mixed $value, array $options = []): ?string
    {
        // Message options setup
        //$options['message'] ??= $options['required_message'] ?? null;
        //$options['invalid_message'] ??= $options['message'] ?? null;

        // If validation should be skipped (e.g., optional field with no value), return null.
        // This relies on AbstractValidator::shouldSkipValidation correctly checking $options['required'].
        if ($this->shouldSkipValidation($value, $options)) {
            return null;
        }
        // $value = "g";

        // If value is empty after shouldSkipValidation, it means it's either an optional field
        // with an empty value (which should have been skipped, indicating an issue if we reach here),
        // or a required field with an empty value (which the 'required' validator will catch).
        // This validator focuses on content validation if a value is present.
        if ($value === null || $value === '') {
            return null; // No content to validate if value is empty/null, let 'required' validator handle empty required fields.
        }

        // Type validation - value must be scalar (string, int, float, bool)
        // Radio button values are typically string or numeric.
        if (!is_scalar($value)) {
            return $this->getErrorMessage($options, 'validation.invalid');
        }

        // validate against choices
        if ($error = $this->validateAgainstChoices($value, $options)) {
            return $error;
        }

        return null; // Validation passed successfully
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultOptions(): array
    {
        return [];
    }
}
