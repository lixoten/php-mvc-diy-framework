<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use Core\Services\ThemeConfigurationManagerService;
use Core\Exceptions\AssetNotFoundException;
use Psr\Log\LoggerInterface;

/**
 * Service for managing theme-specific assets (CSS/JS)
 */
class ThemeAssetService
{
    /**
     * @var ConfigInterface Config service
     */
    private ConfigInterface $config;

    /**
     * @var ThemeConfigurationManagerService Theme manager
     */
    private ThemeConfigurationManagerService $themeManager;

    /**
     * @var array<string, array<string, array<string, array<string>|array<string, array<string>>>>>
     *                    Asset configuration by theme
     *
     * This complex type hint defines the structure:
     * - Outer string key: Theme name (e.g., 'bootstrap', 'global')
     * - Inner array<string, ...>: Keys like 'css', 'js'
     *   - If 'css': array<string, array<string>> (context => array of CSS file paths)
     *   - If 'js': array<string, array<string, array<string>>> (context => position => array of JS file paths)
     */
    private array $assetConfig;

    /**
     * @var string Base path for asset files
     */
    private string $basePath;

    /**
     * @var string Public Directory path
     */
    private string $publicDir;

    /**
     * @var array<string, array<string, mixed>> Cache for processed asset URLs
     */
    private array $assetCache = [];

    /**
     * @var LoggerInterface Logger service
     */
    private LoggerInterface $logger;

    // /**
    //  * @var string|null Current active variant
    //  */
    // private ?string $activeVariant = null;


    /**
     * Constructor
     *
     * @param ConfigInterface $config Config service
     * @param ThemeConfigurationManagerService $themeManager Theme manager
     */
    public function __construct(
        ConfigInterface $config,
        ThemeConfigurationManagerService $themeManager,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->themeManager = $themeManager;
        $this->logger = $logger;
        $this->basePath = $config->get('app.base_path', '');
        $this->publicDir = $config->get('app.paths.public');
        $this->assetConfig = $config->get('theme.assets', []);
    }

    // /**
    //  * Get the active theme variant
    //  *
    //  * @return string|null Current active variant or null if using default
    //  */
    // public function getActiveVariant(): ?string
    // {
    //     return $this->activeVariant;
    // }



    /**
     * Get CSS files for the current theme
     *
     * @param string $context Optional context (e.g., 'admin', 'front')
     * @return array<string> Array of CSS file URLs
     */
    public function getThemeCssFilesold(string $context = 'default'): array
    {
        $activeTheme = $this->themeManager->getActiveThemeService();
        $themeName = $this->themeManager->getActiveTheme();

        $key = $themeName . '_css_' . $context;

        if (isset($this->assetCache[$key])) {
            return $this->assetCache[$key];
        }

        // Get theme-specific CSS files
        $cssFiles = $this->assetConfig[$themeName]['css'][$context] ?? [];

        // Add global CSS files
        $globalCss = $this->assetConfig['global']['css'][$context] ?? [];
        $allCss = array_merge($globalCss, $cssFiles);

        // Process each CSS file (add version, base path, etc.)
        $processedFiles = [];
        foreach ($allCss as $file) {
            $processedFiles[] = $this->processAssetUrl($file['path'], 'css');
        }

        $this->assetCache[$key] = $processedFiles;

        return $processedFiles;
    }

    /**
     * Get all CSS files for the current theme and context
     *
     * @param string $context The context (default, admin)
     * @return array<int, string> Array of CSS file URLs
     */
    public function getThemeCssFiles(string $context = 'default'): array
    {
        $themeName     = $this->themeManager->getActiveTheme();
        $activeVariant = $this->themeManager->getActiveVariant();
        $cacheKey      = "theme_css_{$themeName}_{$context}_{$activeVariant}";

        if (isset($this->assetCache[$cacheKey])) {
            return $this->assetCache[$cacheKey];
        }

        // ✅ 1. Get global CSS (Bootstrap core, jQuery UI, etc.)
        $globalCss = $this->config->get("theme.assets.global.css.{$context}", []);

        // ✅ 2. Get theme-specific CSS (Christmas layout theme, etc.)
        $cssFiles = $this->config->get("theme.assets.{$themeName}.css.{$context}", []);

        // ✅ 3. Merge global + theme CSS
        $allCss = array_merge($globalCss, $cssFiles);

        // ✅ 4. Add variant CSS if active (dark-theme.css, light-theme.css)
        if ($activeVariant) {
            $variants = $this->config->get("theme.variants.{$themeName}", []);
            if (isset($variants[$activeVariant]['css'])) {
                $variantCss = $variants[$activeVariant]['css'];

                if (is_string($variantCss)) {
                    $allCss[] = $variantCss;
                } elseif (is_array($variantCss)) {
                    $allCss = array_merge($allCss, $variantCss);
                }
            }
        }

        // // ✅ 5. Process URLs (add base path, cache busting)
        // $processedCss = array_map(
        //     fn($file) => $this->processAssetUrl($file['path'], 'css'),
        //     $allCss
        // );

        // ✅ 5. Process URLs + Validate file existence
        $processedCss = [];
        foreach ($allCss as $file) {
            $cssPath = $file['path'] ?? '';

            // ✅ NEW: Validate CSS file exists before adding to output
            if ($this->validateCssFileExists($cssPath)) {
                $processedCss[] = $this->processAssetUrl($cssPath, 'css');
            }
        }

        $this->assetCache[$cacheKey] = $processedCss;
        return $processedCss;
    }




    /**
     * Get JS files for the current theme
     *
     * @param string $context Optional context (e.g., 'admin', 'front')
     * @param string $position Position ('head', 'footer')
     * @return array<string> Array of JS file URLs
     */
    public function getThemeJsFiles(string $context = 'default', string $position = 'footer'): array
    {
        // $activeTheme = $this->themeManager->getActiveThemeService();
        $themeName = $this->themeManager->getActiveTheme();



        $key = $themeName . '_js_' . $context . '_' . $position;

        if (isset($this->assetCache[$key])) {
            return $this->assetCache[$key];
        }

        // Get position-specific JS files for the theme
        $jsFiles = $this->assetConfig[$themeName]['js'][$context][$position] ?? [];

        // Add global JS files for this position
        $globalJs = $this->assetConfig['global']['js'][$context][$position] ?? [];
        $allJs = array_merge($globalJs, $jsFiles);

        // Process each JS file
        $processedFiles = [];
        foreach ($allJs as $file) {
            if (is_string($file)) {
                $processedFiles[] = $this->processAssetUrl($file, 'js');
            } elseif (is_array($file) && isset($file['path'])) {
                $processedFiles[] = $this->processAssetUrl($file['path'], 'js');
            }
        }

        $this->assetCache[$key] = $processedFiles;

        return $processedFiles;
    }

    /**
     * Get a theme-specific asset (image, font, etc.)
     *
     * @param string $assetPath Relative path to the asset
     * @param string|null $themeName Optional theme name, uses active theme if not specified
     * @return string Full URL to the asset
     * @throws AssetNotFoundException If asset doesn't exist
     */
    public function getThemeAsset(string $assetPath, ?string $themeName = null): string
    {
        $themeName = $themeName ?? $this->themeManager->getActiveTheme();

        // Build physical path to check if asset exists
        $basePath = $this->config->get('app.public_dir', './public');
        $themeAssetsPath = "/assets/themes/{$themeName}/";
        $physicalPath = $basePath . $themeAssetsPath . $assetPath;

        // Check if asset exists
        if (!file_exists($physicalPath)) {
            // Try fallback to default theme
            $defaultTheme = $this->config->get('theme.default', 'bootstrap');
            $fallbackPath = $basePath . "/assets/themes/{$defaultTheme}/" . $assetPath;

            if ($themeName !== $defaultTheme && file_exists($fallbackPath)) {
                // Use default theme asset as fallback
                $themeAssetsPath = "/assets/themes/{$defaultTheme}/";
            } else {
                throw new AssetNotFoundException("Theme asset not found: {$assetPath}");
            }
        }

        // Build URL
        $assetUrl = $this->basePath . $themeAssetsPath . $assetPath;

        // Add cache-busting version parameter if not already present
        if (strpos($assetUrl, '?') === false) {
            $themeVersion = $this->config->get("theme.metadata.{$themeName}.version", '1.0');
            $assetUrl .= '?v=' . $themeVersion;
        }

        return $assetUrl;
    }

    /**
     * Process an asset URL to add version, base path, etc.
     *
     * @param string $url Asset URL
     * @param string $type Asset type (css, js)
     * @return string Processed URL
     */
    private function processAssetUrl(string $url, string $type): string
    {
        // If URL is external (starts with http), return as-is
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            return $url;
        }

        // Add base path if needed
        if (strpos($url, '/') !== 0) {
            $url = '/' . $url;
        }

        $url = $this->basePath . $url;

        // Add version parameter for cache-busting
        if (strpos($url, '?') === false) {
            $appVersion = $this->config->get('app.version', '1.0');
            $url .= '?v=' . $appVersion;
        }

        return $url;
    }



    /**
     * Render CSS link tags for the current theme and context
     *
     * @param string $context The context (default, admin)
     * @param array<string, mixed> $renderContext Additional context (e.g., form instance)
     * @return string The rendered HTML links
     */
    public function renderCssLinks(string $context = 'default', array $renderContext = []): string
    {
        $html = '';

        // ✅ 1. Get all primary CSS files (global + theme + variant)
        $primaryCssFiles = $this->getThemeCssFiles($context);
        foreach ($primaryCssFiles as $file) {
            $html .= '<link rel="stylesheet" href="' . htmlspecialchars($file) . '">' . PHP_EOL;
        }

        // ✅ 2. Add legacy CSS files (style.css)
        $legacyFiles = [
            '/assets/css/style.css',
        ];
        foreach ($legacyFiles as $file) {
            if ($this->validateCssFileExists($file)) {
                $html .= '<link rel="stylesheet" href="' . htmlspecialchars($this->processAssetUrl($file, 'css')) . '">' . PHP_EOL;
            }
        }

        // ✅ 3. Load visual theme CSS if configured (christmas-theme.css from themes/bootstrap/css/)
        $visualTheme = $this->config->get('view.visual_themes.active', '');
        if ($visualTheme && $visualTheme !== 'ignore') {
            $visualThemeCssPath = $this->config->get("view.visual_themes.available.{$visualTheme}.css", '');
            if (is_string($visualThemeCssPath) && $visualThemeCssPath !== '') {
                if ($this->validateCssFileExists($visualThemeCssPath)) {
                    $html .= '<link rel="stylesheet" href="' . htmlspecialchars($this->processAssetUrl($visualThemeCssPath, 'css')) . '">' . PHP_EOL;
                }
            } else {
                $this->logger->warning(
                    "ThemeAssetService: Configured visual theme '{$visualTheme}' has no valid CSS path.",
                    ['visual_theme' => $visualTheme]
                );
            }
        }

        // ✅ 4. NEW: Load form theme CSS if form is present (neon.css from css/themes/forms/)
        if (isset($renderContext['form']) && $renderContext['form'] instanceof \Core\Form\FormInterface) {
            $formThemeFile = $renderContext['form']->getCssFormThemeFile();
            if ($formThemeFile) {
                $formThemeCss = $this->getFormThemeCssUrl($formThemeFile);
                if ($formThemeCss) {
                    $html .= sprintf(
                        '<link rel="stylesheet" href="%s" data-form-theme="%s">' . PHP_EOL,
                        htmlspecialchars($formThemeCss),
                        htmlspecialchars($formThemeFile)
                    );
                }
            }
        }

        return $html;
    }



    /**
     * Render CSS link tags for the current theme and context
     *
     * @param string $context The context (default, admin)
     * @param array<string, mixed> $renderContext Additional context (e.g., form instance)
     * @return string The rendered HTML links
     */
    public function renderCssLinksold(string $context = 'default', array $renderContext = []): string
    {
        $html = '';
        $themeName      = $this->themeManager->getActiveTheme();
        $activeVariant  = $this->themeManager->getActiveVariant();

        // First add regular theme CSS
        // $assets = $this->config->get('theme.assets', []);
        // $globalCss = $assets['global']['css'][$context] ?? [];
        // $themeCss = $assets[$themeName]['css'][$context] ?? [];

        // // Combine and load all CSS assets
        // $cssFiles = array_merge($globalCss, $themeCss);


        // // Load core framework CSS files
        // foreach ($cssFiles as $file) {
        //     // Check if the file is a string before processing
        //     if (is_string($file)) {
        //         $html .= '<link rel="stylesheet" href="' . $this->processAssetUrl($file, 'css') . '">' . PHP_EOL;
        //     } elseif (is_array($file) && isset($file['path'])) {
        //         // Handle array format where the path is in a 'path' key
        //         $html .= '<link rel="stylesheet" href="' . $this->processAssetUrl($file['path'], 'css') . '">' . PHP_EOL;
        //     }
        // }

        // ✅ 1. Get and render primary CSS files (global + theme-specific) using the cached method
        $primaryCssFiles = $this->getThemeCssFiles($context);
        foreach ($primaryCssFiles as $file) {
            $html .= '<link rel="stylesheet" href="' . htmlspecialchars($file) . '">' . PHP_EOL;
        }

        // ✅ 2. Add legacy CSS files that should always be loaded
        $legacyFiles = [
            // '/assets/css/menu.css',
            '/assets/css/style.css',
        ];
        foreach ($legacyFiles as $file) {
            // Legacy files are often hardcoded paths. Process them just like other assets for consistency.
            $html .= '<link rel="stylesheet" href="' . htmlspecialchars($this->processAssetUrl($file, 'css')) . '">' . PHP_EOL;
        }

        // Visual Theme (if specified) ---
        // ✅ 3. Load and render an additional 'visual theme' CSS if configured
        $visualTheme = $this->config->get('view.visual_themes.active', '');
        if ($visualTheme && $visualTheme !== 'ignore') {
            $visualThemeCssPath = $this->config->get("view.visual_themes.available.{$visualTheme}.css", '');
            if (is_string($visualThemeCssPath) && $visualThemeCssPath !== '') {
                $html .= '<link rel="stylesheet" href="' . htmlspecialchars($this->processAssetUrl($visualThemeCssPath, 'css')) . '">' . PHP_EOL;
            } else {
                // Log a warning if the active visual theme has no valid CSS path defined
                $this->logger->warning(
                    "ThemeAssetService: Configured visual theme '{$visualTheme}' for context '{$context}' has no valid CSS path defined.",
                    ['visual_theme' => $visualTheme, 'config_key' => "view.visual_themes.available.{$visualTheme}.css"]
                );
            }
        }


        // ✅ 4. Load and render theme variant CSS if an active variant is set
        if ($activeVariant) {
            $variants = $this->config->get("theme.variants.{$themeName}", []);
            if (isset($variants[$activeVariant]['css'])) {
                $variantCss = $variants[$activeVariant]['css'];

                // Check variant CSS type before processing
                if (is_string($variantCss)) {
                    $html .= '<link rel="stylesheet" href="' . $this->processAssetUrl($variantCss, 'css') . '">' . PHP_EOL;
                } elseif (is_array($variantCss)) {
                    foreach ($variantCss as $cssFile) {
                        if (is_string($cssFile)) {
                            $html .= '<link rel="stylesheet" href="' . $this->processAssetUrl($cssFile, 'css') . '">' . PHP_EOL;
                        } else {
                            // Log warning for non-string entry in variant CSS array
                            $this->logger->warning(
                                "ThemeAssetService: Variant '{$activeVariant}' for theme '{$themeName}' in context '{$context}' contains a non-string CSS file entry.",
                                ['invalid_entry' => $cssFile, 'expected_type' => 'string']
                            );
                        }
                   }
                } else {
                    // Log warning for invalid type of variant CSS configuration
                    $this->logger->warning(
                        "ThemeAssetService: Variant '{$activeVariant}' for theme '{$themeName}' in context '{$context}' has invalid CSS configuration type.",
                        ['variant_css_config' => $variantCss, 'expected_type' => 'string|array']
                    );
                }
            } else {
                 // Log debug level, as it might just mean no specific CSS for this variant
                 $this->logger->debug(
                    "ThemeAssetService: Active variant '{$activeVariant}' for theme '{$themeName}' in context '{$context}' has no CSS defined.",
                    ['config_key' => "theme.variants.{$themeName}.{$activeVariant}.css"]
                );
            }
        }

        // ✅ 5. NEW: Load form theme CSS if form is present in render context
        if (isset($renderContext['form']) && $renderContext['form'] instanceof \Core\Form\FormInterface) {
            $formThemeFile = $renderContext['form']->getCssFormThemeFile();
            if ($formThemeFile) {
                $formThemeCss = $this->getFormThemeCssUrl($formThemeFile);
                if ($formThemeCss) {
                    $html .= sprintf(
                        '<link rel="stylesheet" href="%s" data-form-theme="%s">' . PHP_EOL,
                        htmlspecialchars($formThemeCss),
                        htmlspecialchars($formThemeFile)
                    );
                }
            }
        }

        return $html;
    }


    /**
     * Render HTML for theme JS files
     *
     * @param string $context Optional context (e.g., 'admin', 'front')
     * @param string $position Position ('head', 'footer')
     * @return string HTML script tags for JS files
     */
    public function renderJsScripts(string $context = 'default', string $position = 'footer'): string
    {
        $jsFiles = $this->getThemeJsFiles($context, $position);

        $html = '';
        foreach ($jsFiles as $file) {
            $html .= '<script src="' . htmlspecialchars($file) . '"></script>' . PHP_EOL;
        }

        return $html;
    }

    // /**
    //  * Set the active theme variant
    //  *
    //  * @param string|null $variant Variant identifier or null for default
    //  * @return self
    //  */
    // public function setActiveVariant(?string $variant): self
    // {
    //     $this->activeVariant = $variant;
    //     return $this;
    // }


    /**
     * Get form theme CSS file URL if configured
     *
     * ✅ Now reads all configuration from theme.php (no hardcoded paths)
     *
     * @param string|null $themeFile Theme name (e.g., 'neon', 'christmas')
     * @return string|null Full URL to theme CSS file, or null if not configured
     */
    public function getFormThemeCssUrl(?string $themeFile): ?string
    {
        if (!$themeFile) {
            return null;
        }

        // ✅ Read base path from config (no hardcoding)
        $basePath = $this->config->get('theme.form_themes.base_path');

        if (!$basePath) {
            $this->logger->error(
                'Form theme base path not configured in theme.php',
                ['theme_file' => $themeFile]
            );
            return null;
        }

        // ✅ Check if theme exists in config
        $themeConfig = $this->config->get("theme.form_themes.available.{$themeFile}");

        if (!$themeConfig) {
            $this->logger->warning(
                "Form theme '{$themeFile}' is not registered in theme.php",
                [
                    'theme_file' => $themeFile,
                    'available_themes' => array_keys(
                        $this->config->get('theme.form_themes.available', [])
                    ),
                ]
            );
            return null;
        }

        // ✅ Build path from config (not hardcoded)
        $themeCssFile = $themeConfig['css'] ?? "{$themeFile}.css";
        $themeCssPath = "{$basePath}/{$themeCssFile}";

        // ✅ Check if file exists
        //$physicalPath = $this->publicDir . $themeCssPath;

        $this->validateCssFileExists($themeCssPath);
        // if (!file_exists($physicalPath)) {
        //     $this->logger->warning(
        //         "Form theme CSS file not found: {$themeCssFile}",
        //         [
        //             'expected_path' => $physicalPath,
        //             'theme_file' => $themeFile,
        //             'config_key' => "theme.form_themes.available.{$themeFile}",
        //         ]
        //     );
        //     return null;
        // }

        // ✅ Return processed URL with cache busting
        return $this->processAssetUrl($themeCssPath, 'css');
    }


    // ✅ Check existence for ALL CSS files in getThemeCssFiles()
    private function validateCssFileExists(string $cssPath): bool
    {
        $physicalPath = $this->publicDir . $cssPath;

        if (!file_exists($physicalPath)) {
            $this->logger->warning(
                "CSS file not found: {$cssPath}",
                ['expected_path' => $physicalPath]
            );
            return false;
        }

        return true;
    }

}
