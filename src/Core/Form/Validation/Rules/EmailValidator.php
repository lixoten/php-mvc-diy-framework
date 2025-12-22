<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use Core\Services\IdnConverterService;

/**
 * Email validator service
 */
class EmailValidator extends AbstractValidator
{
    public function __construct(
        private IdnConverterService $idnConverter,
    ) {
        $this->idnConverter = $idnConverter;
    }

    /** {@inheritdoc} */
    public function validate($value, array $options = []): ?string
    {
        // Use the inherited method
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        $email = (string) $value;


        // // --- START UNICODE FIX ---
        // if (strpos($email, '@') !== false) {
        //     [$local, $domain] = explode('@', $email, 2);

        //     // Convert international domain to Punycode (IDNA 2008 standard)
        //     // This turns "例子.中国" into "xn--fsqu46a.xn--fiqs8s"
        //     $punyDomain = idn_to_ascii($domain, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);

        //     if ($punyDomain !== false) {
        //         $emailForFilter = $local . '@' . $punyDomain;
        //     } else {
        //         $emailForFilter = $email; // Fallback if IDN conversion fails
        //     }
        // } else {
        //     $emailForFilter = $email;
        // }
        // // --- END UNICODE FIX ---
        $emailForFilter = $this->idnConverter->normalizeEmail($email);

        $length = mb_strlen((string)$value);

        // 1. Basic email format validation with Unicode support
        // Ensure FILTER_FLAG_EMAIL_UNICODE is properly applied
        $isValid = filter_var($emailForFilter, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);

        if ($isValid === false) {
            return $this->getErrorMessage($options, 'validation.invalid');
        }


        // 2. Min length validation
        if ($error = $this->validateMinLength($length, $options)) {
            return $error;
        }

        // 3. Max length validation
        if ($error = $this->validateMaxLength($length, $options)) {
            return $error;
        }

        // Example: Suppose you want to allow only emails that start with "user" before the @ symbol.
        // user in config: 'pattern' => '/^user[a-z0-9._%+-]*@/'
        // 4. Custom pattern check (if provided)
        if ($error = $this->validatePattern($email, $options)) {
            return $error;
        }

        // // Extract domain
        // $domain = substr(strrchr((string)$value, "@"), 1);

        // 5. Allowed / Forbidden domains check
        // Extract the domain part for these checks
        $domain = substr($email, strpos($email, '@') + 1);

        // Allowed domains
        if ($error = $this->validateAllowedValues($domain, $options, 'string')) {
            return $error;
        }

        // Forbidden domains
        if ($error = $this->validateForbiddenValues($domain, $options, 'string')) {
            return $error;
        }

        return null;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'email';
    }

    /** {@inheritdoc} */
    protected function getDefaultOptions(): array
    {
        // Define all possible options and their default values
        return [
            'minlength' => null, // int
            'maxlength' => null, // int
            'pattern' => null,   // string (regex)
            'allowed' => [],     // array<string> for allowed domains
            'forbidden' => [],   // array<string> for forbidden domains
            'message' => null,   // string (general error message)
            'invalid_message' => null, // string (for basic email format)
            'minlength_message' => null,
            'maxlength_message' => null,
            'pattern_message' => null,
            'allowed_message' => null,
            'forbidden_message' => null,
            'ignore_allowed' => false,
            'ignore_forbidden' => false,
        ];
    }
}
