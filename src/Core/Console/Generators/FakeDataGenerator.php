<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Faker\Factory;
use Faker\Generator;
use RuntimeException;

/**
 * Service for generating realistic fake data based on schema field definitions.
 *
 * It uses a combination of explicit database types, `check` constraints, and
 * intelligent field name heuristics to produce more relevant sample data.
 *
 * @package   MVC LIXO Framework
 * @author    GitHub Copilot
 * @copyright Copyright (c) 2025
 */
class FakeDataGenerator
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    /**
     * Generates a fake value for a given field definition.
     *
     * @param string $fieldName The name of the field.
     * @param array<string, mixed> $fieldConfig The configuration for the field.
     * @return mixed The generated fake value.
     */
    public function generateValue(string $fieldName, array $fieldConfig): mixed
    {
        // 1. Handle nullable fields with a probability of returning null
        $nullable = $fieldConfig['nullable'] ?? false;
        if ($nullable && $this->faker->boolean(20)) { // 20% chance of being null
            return null;
        }

        // 2. Handle explicit default values from schema
        if (array_key_exists('default', $fieldConfig)) {
            // Special handling for array defaults like `[]` which are often strings in schema config
            if (($fieldConfig['db_type'] ?? 'string') === 'array' && is_string($fieldConfig['default'])) {
                // Assuming default is '[]' or similar JSON string
                $decoded = json_decode($fieldConfig['default'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
            return $fieldConfig['default'];
        }



        //$this->faker->userName();
        // 3. Apply smart field name heuristics for more realistic data
        // These are patterns to "guess" the field's intent beyond just its db_type
        if (str_contains($fieldName, 'email')) {
            return $this->faker->unique()->safeEmail();
        }
        if (str_contains($fieldName, 'username')) {
            return $this->faker->unique()->userName();
        }
        // ✅ Generalized hash detection
        if ((str_contains($fieldName, 'password') || str_contains($fieldName, 'secret_code')) && str_contains($fieldName, 'hash')) {
            // Return a consistent bcrypt hash for 'password' or 'secret_code_hash'
            // This is a hash for 'password'
            return '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        }
        if ($fieldName === 'slug') { // Exact match for slug is a strong indicator
            return $this->faker->unique()->slug();
        }
        if (str_contains($fieldName, 'title') || str_contains($fieldName, 'name')) {
            return $this->faker->sentence(mt_rand(3, 7));
        }
        if (str_contains($fieldName, 'content') || str_contains($fieldName, 'description')) {
            // Use field length if available, otherwise a reasonable paragraph length
            $maxLength = $fieldConfig['length'] ?? null;
            if ($maxLength && $maxLength < 255) {
                return $this->faker->text($maxLength);
            }
            return $this->faker->paragraph(mt_rand(2, 5));
        }
        if (str_contains($fieldName, 'url') || str_contains($fieldName, 'link') || str_contains($fieldName, 'address') || str_contains($fieldName, 'website')) {
            return $this->faker->url();
        }
        if (str_contains($fieldName, 'phone') || str_contains($fieldName, 'telephone')) {
            return $this->faker->phoneNumber();
        }
        if (str_contains($fieldName, 'ip_address')) {
            return $this->faker->ipv4();
        }
        if (str_contains($fieldName, 'uuid') || str_contains($fieldName, 'guid')) {
            return $this->faker->uuid();
        }
        if (str_contains($fieldName, 'created_at') || str_contains($fieldName, 'updated_at') || str_contains($fieldName, 'timestamp')) {
            return $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s');
        }
        // ✅ Generalized date of birth detection
        if (($fieldConfig['db_type'] ?? 'string') === 'date' && str_contains($fieldName, 'birth')) {
            return $this->faker->date('Y-m-d', '-18 years');
        }
        if (str_contains($fieldName, 'gender')) {
            // If check constraint exists, use it, otherwise default to common genders
            if (isset($fieldConfig['check'])) {
                $enumOptions = $this->parseEnumCheckConstraint($fieldConfig['check']);
                return $this->faker->randomElement($enumOptions);
            }
            return $this->faker->randomElement(['m', 'f', 'o', 'nb']);
        }
        if (str_contains($fieldName, 'count')) {
            return $this->faker->numberBetween(0, 10);
        }
        if ((str_contains($fieldName, 'profile') && str_contains($fieldName, 'picture')) || str_contains($fieldName, 'image_url') || str_contains($fieldName, 'avatar')) {
            return 'pictures/' . $this->faker->md5() . '.jpg';
        }
        if (str_contains($fieldName, 'balance') || str_contains($fieldName, 'amount') || str_contains($fieldName, 'price')) {
            // Check for balance range from 'check' constraint
            if (isset($fieldConfig['check'])) {
                if (preg_match('/(\w+) >= (\d+\.?\d*) AND (\w+) <= (\d+\.?\d*)/', $fieldConfig['check'], $matches)) {
                    return $this->faker->randomFloat(2, (float)$matches[2], (float)$matches[4]);
                }
            }
            return $this->faker->randomFloat(2, 0, 100000);
        }
        if (str_contains($fieldName, 'volume_level')) {
            return $this->faker->numberBetween(0, 100);
        }
        if (str_contains($fieldName, 'star_rating') || str_contains($fieldName, 'rating')) {
            return $this->faker->randomFloat(1, 1, 5);
        }
        if (str_contains($fieldName, 'week_day')) {
            return $this->faker->dayOfWeek();
        }
        if (str_contains($fieldName, 'color')) {
            return $this->faker->colorName();
        }
        if (str_contains($fieldName, 'generic_text')) {
            $maxLength = $fieldConfig['length'] ?? null;
            if ($maxLength) {
                return $this->faker->text($maxLength);
            }
            return $this->faker->words(2, true);
        }
        if (str_contains($fieldName, 'code')) {
            return $this->faker->bothify('???-###');
        }
        // ✅ Generalized boolean indicator detection
        if (str_starts_with($fieldName, 'is_') || str_ends_with($fieldName, '_ind')) {
            return $this->faker->boolean();
        }
        if (str_contains($fieldName, 'status')) {
            if (isset($fieldConfig['check'])) {
                $enumOptions = $this->parseEnumCheckConstraint($fieldConfig['check']);
                return $this->faker->randomElement($enumOptions);
            }
        }


        // 4. Handle generic db_types (fallback if no specific pattern matched)
        $dbType = $fieldConfig['db_type'] ?? 'string';
        $length = $fieldConfig['length'] ?? null;

        return match ($dbType) {
            'bigIncrements', 'increments', 'primary' => null, // Handled by DB, not seeded directly
            'foreignId', 'bigInteger', 'integer' => $this->faker->randomNumber(5, true),
            'decimal', 'float', 'double' => $this->generateDecimalOrFloat($fieldConfig),
            'boolean' => $this->faker->boolean(),
            'date' => $this->faker->date('Y-m-d'), // Generic date if not a 'birth' date
            'time' => $this->faker->time('H:i:s'),
            'dateTime', 'timestamp' => $this->faker->dateTimeThisYear()->format('Y-m-d H:i:s'), // Generic datetime
            'string', 'char', 'text' => $this->faker->text($length ?? 50), // Use length hint
            'enum' => $this->faker->randomElement($this->parseEnumCheckConstraint($fieldConfig['check'] ?? '')),
            'array' => json_encode($this->faker->randomElements(['optionA', 'optionB', 'optionC'], $this->faker->numberBetween(1, 3))),
            default => $this->faker->word(),
        };
    }

    /**
     * Parses a SQL CHECK constraint string to extract enum values.
     * Assumes format like "field IN ('val1', 'val2', 'val3')"
     *
     * @param string $checkConstraint The CHECK constraint string.
     * @return array<string> An array of enum values.
     * @throws RuntimeException If enum values cannot be parsed from the check constraint.
     */
    private function parseEnumCheckConstraint(string $checkConstraint): array
    {
        if (preg_match("/IN\s*\(([^)]+)\)/", $checkConstraint, $matches)) {
            $values = explode(',', $matches[1]);
            return array_map(fn ($s) => trim($s, " '"), $values);
        }
        // If 'enum' db_type is used, a CHECK constraint with IN (...) is expected.
        // If not found, it's a schema definition issue.
        throw new RuntimeException("Could not parse enum values from CHECK constraint: '{$checkConstraint}'");
    }

    /**
     * Generates a fake decimal or float value, attempting to respect precision, scale, and check constraints.
     *
     * @param array<string, mixed> $fieldConfig
     * @return float
     */
    private function generateDecimalOrFloat(array $fieldConfig): float
    {
        $min = 0.0;
        $max = 1000.0;
        $scale = $fieldConfig['scale'] ?? 2;

        if (isset($fieldConfig['check'])) {
            // Attempt to parse min/max from CHECK constraint like 'balance >= 0 AND balance <= 100000'
            if (preg_match('/(\w+) >= (\d+\.?\d*) AND (\w+) <= (\d+\.?\d*)/', $fieldConfig['check'], $matches)) {
                $min = (float)$matches[2];
                $max = (float)$matches[4];
            }
        }

        return $this->faker->randomFloat($scale, $min, $max);
    }
}
