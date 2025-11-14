<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Email formatter for sanitizing and formatting email addresses
 */
class EmailFormatter extends AbstractFormatter
{
    public function getName(): string
    {
        return 'email';
    }

    public function supports(mixed $value): bool
    {
        return is_string($value) || $value === null;
    }

    /**
     * Formats an email address, with optional masking for privacy.
     *
     * @param array{ mask?: bool } $options
     *   - 'mask': If true, masks part of the email (e.g., u***@d***.com)
     */
    public function transform(mixed $value, array $options = []): string
    {
        $options = $this->mergeOptions($options);

        if (empty($value)) {
            return '';
        }

        $text = (string)$value;

        if (isset($options['mask']) && $options['mask']) {
            // Simple masking: show first char of local part and domain
            $parts = explode('@', $text);
            if (count($parts) === 2) {
                $local = substr($parts[0], 0, 1) . str_repeat('*', max(0, strlen($parts[0]) - 1));
                $domainParts = explode('.', $parts[1]);
                $domain = substr($domainParts[0], 0, 1) . str_repeat('*', max(0, strlen($domainParts[0]) - 1));
                if (isset($domainParts[1])) {
                    $domain .= '.' . $domainParts[1];
                }
                return $local . '@' . $domain;
            }
        }

        return $text;
    }

    protected function getDefaultOptions(): array
    {
        return [
            'mask' => false,
        ];
    }
}
