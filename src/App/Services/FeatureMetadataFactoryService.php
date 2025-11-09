<?php

declare(strict_types=1);

namespace App\Services;

use Core\Interfaces\ConfigInterface;
use Core\Context\CurrentContext;
use App\Enums\Url;

/**
 * Service for creating FeatureMetadataService instances based on route context and config.
 *
 * @package App\Services
 */
class FeatureMetadataFactoryService
{
    private ConfigInterface $config;
    private CurrentContext $currentContext;

    /**
     * @param ConfigInterface $config
     * @param CurrentContext $currentContext
     */
    public function __construct(
        ConfigInterface $config,
        CurrentContext $currentContext
    ) {
        $this->config = $config;
        $this->currentContext = $currentContext;
    }

    /**
     * Create a FeatureMetadataService for the given view key, using config and route context.
     *
     * @param string $viewKey
     * @return FeatureMetadataService
     */
    public function createFor(string $viewKey): FeatureMetadataService
    {
        // fixme shit2 - ok
        $metadataConfig = $this->config->getFromFeature($viewKey, "metadata_{$viewKey}.metadata");

        $routeType = $this->currentContext->getRouteType();


        $routeMap = $metadataConfig['route_map'] ?? [];
        $routeDefaults = $routeMap[$routeType] ?? $routeMap['core'] ?? [];

        $pageName = $metadataConfig['pageName'] ?? null;
        $entityName = $metadataConfig['entityName'] ?? null;
        $ownerForeignKey = (string) ($metadataConfig['owner_foreign_key'] ?? $routeDefaults['owner_foreign_key']
                                                                                                               ?? 'id');

        $rawBase = $metadataConfig['base_url_enum'] ?? $routeDefaults['base_url_enum'] ?? Url::CORE_TESTY;
        $baseUrlEnum = $this->resolveUrlEnum($rawBase);

        $rawEdit = $metadataConfig['edit_url_enum'] ?? $routeDefaults['edit_url_enum'] ?? null;
        $editUrlEnum = $rawEdit !== null ? $this->resolveUrlEnum($rawEdit) : null;

        $rawList = $metadataConfig['list_url_enum'] ?? $routeDefaults['list_url_enum'] ?? null;
        $listUrlEnum = $rawList !== null ? $this->resolveUrlEnum($rawList) : null;

        // --- MISSING CODE STARTS HERE ---
        $rawCreate = $metadataConfig['create_url_enum'] ?? $routeDefaults['create_url_enum'] ?? null;
        $createUrlEnum = $rawCreate !== null ? $this->resolveUrlEnum($rawCreate) : null;

        $rawView = $metadataConfig['view_url_enum'] ?? $routeDefaults['view_url_enum'] ?? null;
        $viewUrlEnum = $rawView !== null ? $this->resolveUrlEnum($rawView) : null;

        $rawDelete = $metadataConfig['delete_url_enum'] ?? $routeDefaults['delete_url_enum'] ?? null;
        $deleteUrlEnum = $rawDelete !== null ? $this->resolveUrlEnum($rawDelete) : null;

        $rawDeleteConfirm = $metadataConfig['delete_confirm_url_enum'] ?? $routeDefaults['delete_confirm_url_enum']
                                                                                                                ?? null;
        $deleteConfirmUrlEnum = $rawDeleteConfirm !== null ? $this->resolveUrlEnum($rawDeleteConfirm) : null;
        // --- MISSING CODE ENDS HERE ---

        $redirectAfterSave = $metadataConfig['redirect_after_save'] ?? null;
        $redirectAfterAdd = $metadataConfig['redirect_after_add'] ?? null;


        return new FeatureMetadataService(
            $pageName,
            $entityName,
            $ownerForeignKey,
            $baseUrlEnum,
            $editUrlEnum,
            $listUrlEnum,
            $createUrlEnum,
            $viewUrlEnum,
            $deleteUrlEnum,
            $deleteConfirmUrlEnum,
            $redirectAfterSave,
            $redirectAfterAdd,
        );
    }

    /**
     * Convert a string or Url enum to a Url enum instance.
     *
     * Accepts an Url instance, an enum case name (e.g. 'CORE_TESTY'),
     * a normalized name ('core_testy', 'core-testy'), or a backed value (future-proof).
     *
     * @param string|Url $value
     * @return Url
     */
    private function resolveUrlEnum(string|Url $value): Url
    {
        if ($value instanceof Url) {
            return $value;
        }

        $needle = (string) $value;

        // Exact case name
        foreach (Url::cases() as $case) {
            if ($case->name === $needle) {
                return $case;
            }
        }

        // Normalized name (allow lower/alternate separators)
        $normalized = strtoupper(str_replace(['-', ' ', '/'], '_', $needle));
        foreach (Url::cases() as $case) {
            if ($case->name === $normalized) {
                return $case;
            }
        }

        // If Url becomes backed in future, allow matching by backing value
        foreach (Url::cases() as $case) {
            if ($case instanceof \BackedEnum && (string) $case->value === $needle) {
                return $case;
            }
        }

        // Fallback
        return Url::CORE_TESTY;
    }


    /**
     * Normalize enum-backed or scalar value to string, or return null.
     *
     * @param mixed $value
     * @return string|null
     */
    private function normalizeEnumToString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        // Backed enums (string/int)
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        // Unit enums (no backing value) — use the case name
        if ($value instanceof \UnitEnum) {
            return (string) $value->name;
        }

        // Objects implementing __toString
        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        // Scalars (int, float, bool)
        if (is_scalar($value)) {
            return (string) $value;
        }

        throw new \InvalidArgumentException(
            'FeatureMetadataFactoryService: Unable to convert provided value to string.'
        );
    }
}
