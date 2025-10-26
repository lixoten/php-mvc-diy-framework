<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Normalizers\PhoneNormalizer;

/**
 * Service for normalizing form data for storage.
 */
class DataNormalizerService
{
    private RegionContextService $regionContextService;

    public function __construct(RegionContextService $regionContextService)
    {
        $this->regionContextService = $regionContextService;
    }

    /**
     * Normalize form data for storage.
     *
     * @param array<string, mixed> $data
     * @param array<string, object> $fields
     * @return array<string, mixed>
     */
    public function normalize(array $data, array $fields): array
    {
        foreach ($fields as $name => $field) {
            if (!isset($data[$name])) {
                continue;
            }

            $config = $field->getOptions();
            $value = $data[$name];

            if (($field->getType() ?? null) === 'tel' && !empty($value)) {
                $region = $config['region'] ?? $this->regionContextService->getRegion();
                $normalized = PhoneNormalizer::normalizeToE164((string)$value, $region);
                if ($normalized !== null) {
                    $data[$name] = $normalized;
                }
            }
            // ...other normalization...
        }
        return $data;
    }
}
