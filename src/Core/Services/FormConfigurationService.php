<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Exceptions\ConfigurationException;
use Core\Exceptions\ConfigurationValidationException;
use Core\I18n\I18nTranslator;
use Core\Interfaces\ConfigInterface;
use Core\Interfaces\CacheInterface;
use Psr\Log\LoggerInterface;

/**
 * Service responsible for loading and merging form configurations
 * from various sources (default, feature-specific, page-specific)
 *
 * ✅ ROLE: High-level orchestration ONLY
 *
 * RESPONSIBILITIES:
 * - Load configurations from multiple sources (base, page-specific)
 * - Merge configurations (base → entity → page, with page having highest priority)
 * - Delegate normalization to FormConfigurationNormalizerService
 * - Delegate validation to FormConfigurationValidatorService
 * - Handle validation results (throw exception or log and continue)
 *
 * DOES NOT:
 * - Transform or normalize data (that's the normalizer's job)
 * - Validate business rules (that's the validator's job)
 * - Apply defaults or cast types (that's the normalizer's job)
 * - Check field existence (that's the validator's job)
 *
 * PHILOSOPHY: "Load, merge, delegate, orchestrate—but don't do the work yourself."
 *
 * Configuration hierarchy (highest to lowest priority):
 * 1. Page-specific: src/App/Features/{Feature}/Config/{page}_view.php
 * 2. Entity-specific: src/App/Features/{Feature}/Config/{entity}_view.php (if needed)
 * 3. Base/default: src/Config/view.form.php
 */
class FormConfigurationService
{
    public function __construct(
        protected I18nTranslator $translator,
        protected ConfigInterface $configService,
        protected LoggerInterface $logger,
        private FormConfigurationNormalizerService $normalizerService,
        private FormConfigurationValidatorService $validatorService,
        private ?CacheInterface $cache = null, // ✅ Optional cache dependency
        private int $cacheTtl = 3600, // ✅ Cache TTL (1 hour default)
    ) {
    }

    /**
     * Load and merge form configuration for a specific page/entity context.
     *
     * ✅ This is the main orchestration method following the correct flow:
     *    1️⃣ CHECK CACHE: Return cached config if available (already normalized & validated)
     *    1️⃣ LOAD: Get raw data from config files
     *    2️⃣ MERGE: Combine base + page (higher priority wins)
     *    3️⃣ NORMALIZE: Make data safe, apply defaults, cast types
     *    4️⃣ VALIDATE: Enforce business rules on normalized data
     *    5️⃣ HANDLE VALIDATION RESULT: Throw or return
     *    6️⃣ CACHE: Store normalized & validated config
     *    7️⃣ RETURN: Normalized config
     *
     * @param string $pageKey Page identifier (e.g., 'testy_edit', 'user_login')
     * @param string $pageName Page name (e.g., 'testy', 'user')
     * @param string $pageAction Action name (e.g., 'edit', 'create')
     * @param string $pageFeature Feature name (e.g., 'Testy', 'Auth')
     * @param string $pageEntity Entity name (e.g., 'testy', 'user')
     * @return array<string, mixed> Merged, normalized, and validated configuration array.
     * @throws ConfigurationValidationException If validation fails in strict mode.
     */
    public function loadConfiguration(
        string $pageKey,
        string $pageName,
        string $pageAction,
        string $pageFeature,
        string $pageEntity,
    ): array {
        // ✅ 1️⃣ CHECK CACHE: Return early if cached config exists
        if ($this->cache !== null) {
            $cacheKey = $this->buildCacheKey($pageFeature, $pageKey);

            try {
                $cachedConfig = $this->cache->get($cacheKey);

                if ($cachedConfig !== null && is_array($cachedConfig)) {
                    $this->logger->debug('FormConfigurationService: Cache HIT', [
                        'cache_key' => $cacheKey,
                        'page_key' => $pageKey,
                    ]);

                    // ✅ Cached config is ALREADY normalized & validated
                    // ✅ NO NEED to re-normalize or re-validate
                    return $cachedConfig;
                }
            } catch (\Throwable $e) {
                // ⚠️ Cache read failure - log and continue with full load
                $this->logger->warning('FormConfigurationService: Cache read failed', [
                    'cache_key' => $cacheKey,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ✅ 2️⃣ CACHE MISS: Proceed with full load/merge/normalize/validate
        $this->logger->debug('FormConfigurationService: Cache MISS - loading from files', [
            'page_key' => $pageKey,
        ]);


        // ✅ 1️⃣ LOAD: Get raw data from config files
        $baseConfig = $this->loadBaseConfiguration();
        $pageConfig = $this->loadPageConfiguration($pageFeature, $pageKey, $pageName, $pageAction);

        // ✅ 2️⃣ MERGE: Combine base + page (higher priority wins)
        // Page-specific config takes precedence over base config
        $mergedConfig = [
            'render_options'     => $this->deepMerge(
                $baseConfig['render_options'] ?? [],
                $pageConfig['render_options'] ?? []
            ),
            'form_layout'        => $pageConfig['form_layout'] ?? ($baseConfig['form_layout'] ?? []),
            'form_hidden_fields' => $pageConfig['form_hidden_fields'] ?? ($baseConfig['form_hidden_fields'] ?? []),
            'form_extra_fields'  => $pageConfig['form_extra_fields'] ?? ($baseConfig['form_extra_fields'] ?? []),
            // ⚠️ Add other top-level configuration keys here if your forms use them,
            // following the same page > base > default empty array hierarchy.
        ];

        // ✅ 3️⃣ NORMALIZE: Make data safe, apply defaults, cast types
        // ⚠️ This can log WARNINGS but NEVER throws exceptions
        $normalizedConfig = $this->normalizerService->normalize($mergedConfig); // this line is good

        // ✅ 4️⃣ VALIDATE: Enforce business rules on normalized data
        // ⚠️ This logs ERRORS but returns results (orchestrator decides whether to throw)
        $validationResult = $this->validatorService->validate(
            $normalizedConfig,
            $pageKey,
            $pageEntity,
            "{$pageFeature}/Config/{$pageKey}_view.php"
        );

        // ✅ 5️⃣ HANDLE VALIDATION RESULT
        if (!$validationResult['isValid']) {
            // ✅ Option A: Always throw (strict mode - recommended for development)
            throw new ConfigurationValidationException(
                $this->translator,
                $validationResult['errors'],              // Array of error strings
                "{$pageFeature}/Config/{$pageKey}_view.php", // Config identifier
                $pageKey,                                  // Page key
                $pageEntity                                // Entity name
            );

            // ⚠️ Option B: Log and continue (permissive mode - could be used in production)
            // $this->logger->error('Form configuration errors', $validationResult['errors']);
            // return $normalizedConfig; // Use normalized config anyway

            // ⚠️ OPTION C: Graceful degradation
            // Fall back to a safe default configuration
            // $this->logger->error('Form configuration validation failed, using safe defaults', [
            //     'config_identifier' => "{$pageFeature}/Config/{$pageKey}_view.php",
            //     'errors' => $validationResult['errors']
            // ]);
            // return $this->loadSafeDefaultConfiguration();
        }

        return $normalizedConfig;
    }

    /**
     * Load base/global form configuration.
     *
     * ✅ Loads from src/Config/view.form.php
     * ✅ Returns ALL top-level keys (not just 'render_options')
     *
     * @return array<string, mixed>
     */
    protected function loadBaseConfiguration(): array
    {
        try {
            // Loads from src/Config/view.form.php
            $config = $this->configService->get('view.form') ?? [];

            // ✅ Ensure all expected top-level configuration keys are returned from baseConfig
            // This prevents keys like 'form_layout' from being discarded prematurely.
            return [
                'render_options'     => $config['render_options'] ?? [],
                'form_layout'        => $config['form_layout'] ?? [],
                'form_hidden_fields' => $config['form_hidden_fields'] ?? [],
                'form_extra_fields'  => $config['form_extra_fields'] ?? [],
                // ⚠️ Add any other top-level configuration keys that can appear in view.form.php here.
            ];
        } catch (\Exception $e) {
            $this->logger->warning('FormConfigurationService: Failed to load base configuration', [
                'error' => $e->getMessage()
            ]);
            // ✅ Return empty structure instead of null to maintain array type consistency
            return [
                'render_options'     => [],
                'form_layout'        => [],
                'form_hidden_fields' => [],
                'form_extra_fields'  => [],
            ];
        }
    }


    private function checkConfig(array $config, string $configIdentifier, string $pageName, string $pageKey): void
    {
        // ✅ 1. Define ALL expected top-level keys for strict validation
        $allowedTopLevelKeys = ['render_options', 'form_layout', 'form_hidden_fields', 'form_extra_fields'];

        // ❌ 2. Check for unexpected top-level keys
        foreach (array_keys($config) as $key) {
            if (!in_array($key, $allowedTopLevelKeys, true)) {
                $message = "Config '{$configIdentifier}': Unexpected top-level configuration key found: '{$key}'.";
                $suggestion = "Suggestion: Only these area allowed. " . implode(', ', $allowedTopLevelKeys);
                $errorCode = 'ERR-DEV-001';
                $errors[]  = [
                    'message'    => $message,
                    'suggestion' => $suggestion,
                    'dev_code' => $errorCode,
                ];
                throw new ConfigurationValidationException(
                    $this->translator,
                    $errors,              // Array of error strings
                    $configIdentifier,                        // Config identifier
                    $pageKey,                                  // Page key
                    $pageName                                 // Entity name
                );

                $this->logger->warning(
                    $message,
                    [
                        'suggestion' => $suggestion,
                    ]
                );
            }
        }

        // $requiredTopLevelKeys = ['render_options', 'form_layout', 'form_hidden_fields', 'form_extra_fields'];
        // foreach ($requiredTopLevelKeys as $key) {
        //     if (!isset($config[$key]) || !is_array($config[$key])) {
        //         $message = "Config '{$configIdentifier}': Missing or invalid top-level key: '{$key}'. Expected " .
        //                    "an array.";
        //         $suggestion = "Suggestion: Add missing '{$key}' section.";
        //         $errorCode = 'ERR-DEV-002';
        //         $errors[]  = [
        //             'message'    => $message,
        //             'suggestion' => $suggestion,
        //             'dev_code' => $errorCode,
        //         ];
        //         $this->logger->warning(
        //             $message,
        //             [
        //                 'suggestion' => $suggestion,
        //             ]
        //         );
        //     }
        // }
    }

    /**
     * Load page-specific form configuration.
     *
     * ✅ Example: src/App/Features/Testy/Config/testy_view_edit.php
     * ✅ Returns the full page config (not just render_options)
     *
     * @param string $pageFeature Feature name (e.g., 'Testy')
     * @param string $pageKey Page identifier (e.g., 'testy_edit')
     * @param string $pageName Entity/page name (e.g., 'testy')
     * @param string $pageAction Action name (e.g., 'edit')
     * @return array<string, mixed>
     */
    protected function loadPageConfiguration(
        string $pageFeature,
        string $pageKey,
        string $pageName,
        string $pageAction
    ): array {
        try {
            // ✅ Build config key: testy_view_edit
            $configKey = "{$pageName}_view_{$pageAction}";

            // ✅ Load from feature-specific config directory
            $config = $this->configService->getFromFeature($pageFeature, $configKey) ?? [];
            // $this->checkConfig($config, 'view.form', $pageName, $pageKey);

            return $config;
        } catch (\Exception $e) {
            $this->logger->debug('FormConfigurationService: No page-specific configuration found', [
                'feature' => $pageFeature,
                'page' => $pageKey,
                'error' => $e->getMessage()
            ]);
            // ✅ Return empty array (not structure) - normalizer will handle structure
            return [];
        }
    }

    /**
     * Deep merge multiple arrays, with later arrays taking precedence.
     *
     * ✅ Used to merge base render_options with page render_options
     * ✅ Nested arrays are recursively merged
     *
     * @param array<string, mixed> ...$arrays
     * @return array<string, mixed>
     */
    protected function deepMerge(array ...$arrays): array
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    // ✅ Recursively merge nested arrays
                    $result[$key] = $this->deepMerge($result[$key], $value);
                } else {
                    // ✅ Later value overwrites earlier value
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Build cache key for a specific page configuration.
     *
     * ✅ Format: form_config:{feature}:{page_key}
     * ✅ Example: form_config:Testy:testy_edit
     *
     * @param string $pageFeature Feature name
     * @param string $pageKey Page identifier
     * @return string Cache key
     */
    protected function buildCacheKey(string $pageFeature, string $pageKey): string
    {
        return "form_config:{$pageFeature}:{$pageKey}";
    }
}
