<?php

declare(strict_types=1);

namespace Core\Formatters;

/**
 * Formatter that renders a status value as a Bootstrap badge.
 *
 * Example: "Published" => green badge, others => yellow badge.
 */
class BadgeStatusFormatter extends AbstractFormatter
{
    /**
     * Get the unique name identifier for this formatter.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'badge_status';
    }

    /**
     * Check if this formatter supports the given value type.
     *
     * @param mixed $value
     * @return bool
     */
    public function supports(mixed $value): bool
    {
        return is_string($value) || is_numeric($value) || $value === null;
    }

    /**
     * Format the given value as a Bootstrap badge.
     *
     * @param mixed $value
     * @param array<string, mixed> $options
     *      - 'success_value': string, value that triggers success badge (default: 'Published')
     *      - 'success_class': string, success badge class (default: 'success')
     *      - 'warning_class': string, warning badge class (default: 'warning')
     * @return string
     */
    public function format(mixed $value, array $options = []): string
    {
        $options = $this->mergeOptions($options);

        $successValue = $options['success_value'];
        $successClass = $options['success_class'];
        $warningClass = $options['warning_class'];

        $class = ($value === $successValue) ? $successClass : $warningClass;

        return '<span class="badge bg-' . $class . '">' . htmlspecialchars((string)$value) . '</span>';
    }

    /**
     * Get default options for this formatter.
     *
     * @return array<string, mixed>
     */
    protected function getDefaultOptions(): array
    {
        return [
            'success_value' => 'Published',
            'success_class' => 'success',
            'warning_class' => 'warning',
        ];
    }
}