<?php

declare(strict_types=1);

namespace Core\Form;

// use Core\Services\FormatterService;

/**
 * Sanitizes form data using formatters
 */
class DataSanitizer
{
    // public function __construct(private FormatterService $formatterService) {}

    public function sanitize(array $data, array $fields): array
    {
        foreach ($fields as $name => $field) {
            if (!isset($data[$name])) {
                continue;
            }

            $config = $field->getOptions();
            $submittedValue = $data[$name];

            // Always trim strings
            if (is_string($submittedValue)) {
                $submittedValue = trim($submittedValue);
                // Remove ASCII control characters except \n, \r, \t
                $submittedValue = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $submittedValue);
            }

            // Normalize empty string to null
            if ($submittedValue === '') {
                $submittedValue = null;
            }

            // Type casting using strategy method for extensibility
            $submittedValue = $this->castValueByType($submittedValue, $field->getType() ?? null);

            // If a 'sanitize' closure is defined, use it
            if (isset($config['sanitize']) && is_callable($config['sanitize'])) {
                $data[$name] = $config['sanitize']($submittedValue, $config, $data);
            } else {
                $data[$name] = $submittedValue;
            }
        }

        return $data;
    }


    /**
     * Cast value based on field type.
     *
     * @param mixed $value
     * @param string|null $type
     * @return mixed
     */
    protected function castValueByType(mixed $value, ?string $type): mixed
    {
        switch ($type) {
            case 'number':
            case 'range':
                if ($value !== null && $value !== '') {
                    return is_numeric($value) ? (float)$value : $value;
                }
                return $value;
            case 'checkbox':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            case 'integer':
                return is_numeric($value) ? (int)$value : $value;
            case 'float':
                return is_numeric($value) ? (float)$value : $value;
            // Add more types here as needed for extensibility
            default:
                return $value;
        }
    }
}
