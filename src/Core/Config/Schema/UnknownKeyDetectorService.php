<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Core\Config\Schema\UnknownKeyDetectorService.php

declare(strict_types=1);

namespace Core\Config\Schema;

// TODO unittest
/**
 * Unknown Key Detector Service
 *
 * Detects typos in config arrays by checking against whitelisted keys.
 *
 * Responsibilities (SRP):
 * - Detect unknown keys in config arrays
 * - Suggest closest matches using Levenshtein distance
 * - Generate error messages
 *
 * This service is reusable across ALL schema validators.
 *
 * @package Core\Config\Schema
 */
class UnknownKeyDetectorService
{
    /**
     * Detect unknown keys in a config array
     *
     * @param array<string, mixed> $config Config array to check
     * @param array<string> $allowedKeys Whitelist of allowed keys
     * @param string $context Context for error messages (e.g., "Field 'id'.list")
     * @param string $configFilePath Config file path for error messages
     * @return array<string> Array of error messages (empty if all keys valid)
     */
    public function detectUnknownKeys(
        array $config,
        array $allowedKeys,
        string $context,
        string $configFilePath
    ): array {
        $errors = [];

        foreach (array_keys($config) as $key) {
            if (!in_array($key, $allowedKeys, true)) {
                $closestMatch = $this->findClosestMatch($key, $allowedKeys);
                $suggestion = $closestMatch ? " Did you mean '{$closestMatch}'?" : '';

                $errors[] = sprintf(
                    "Unknownzz key '%s' in %s in %s.%s",
                    $key,
                    $context,
                    $configFilePath,
                    $suggestion
                );
            }
        }

        return $errors;
    }

    /**
     * Find closest matching string using Levenshtein distance
     *
     * @param string $input Input string (typo)
     * @param array<string> $candidates Valid strings to compare against
     * @param int $maxDistance Maximum Levenshtein distance to consider a match
     * @return string|null Closest match, or null if no close match found
     */
    public function findClosestMatch(
        string $input,
        array $candidates,
        int $maxDistance = 3
    ): ?string {
        $closestMatch = null;
        $closestDistance = PHP_INT_MAX;

        foreach ($candidates as $candidate) {
            $distance = levenshtein(strtolower($input), strtolower($candidate));

            if ($distance < $closestDistance && $distance <= $maxDistance) {
                $closestMatch = $candidate;
                $closestDistance = $distance;
            }
        }

        return $closestMatch;
    }
}