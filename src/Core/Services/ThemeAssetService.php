<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use Core\Services\ThemeConfigurationManagerService;
use Core\Exceptions\AssetNotFoundException;

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
     * @var array<string, array<string, array<string, string>>> Asset configuration by theme
     */
    private array $assetConfig;

    /**
     * @var string Base path for asset files
     */
    private string $basePath;

    /**
     * @var array<string, array<string, mixed>> Cache for processed asset URLs
     */
    private array $assetCache = [];

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
        ThemeConfigurationManagerService $themeManager
    ) {
        $this->config = $config;
        $this->themeManager = $themeManager;
        $this->basePath = $config->get('app.base_path', '');
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
    public function getThemeCssFiles(string $context = 'default'): array
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
            $processedFiles[] = $this->processAssetUrl($file, 'css');
        }

        $this->assetCache[$key] = $processedFiles;

        return $processedFiles;
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
     * @return string The rendered HTML links
     */
    public function renderCssLinks(string $context = 'default'): string
    {
        $html = '';
        $themeName      = $this->themeManager->getActiveTheme();

        $activeVariant  = $this->themeManager->getActiveVariant();




        // First add regular theme CSS
        $assets = $this->config->get('theme.assets', []);
        $globalCss = $assets['global']['css'][$context] ?? [];
        $themeCss = $assets[$themeName]['css'][$context] ?? [];

        // Combine and load all CSS assets
        $cssFiles = array_merge($globalCss, $themeCss);

        // Add legacy CSS files that should always be loaded
        $legacyFiles = [
            // '/assets/css/menu.css',
            '/assets/css/style.css',
        ];



        // Load core framework CSS files
        foreach ($cssFiles as $file) {
            // Check if the file is a string before processing
            if (is_string($file)) {
                $html .= '<link rel="stylesheet" href="' . $this->processAssetUrl($file, 'css') . '">' . PHP_EOL;
            } elseif (is_array($file) && isset($file['path'])) {
                // Handle array format where the path is in a 'path' key
                $html .= '<link rel="stylesheet" href="' . $this->processAssetUrl($file['path'], 'css') . '">' . PHP_EOL;
            }
        }

        foreach ($legacyFiles as $file) {
            $html .= '<link rel="stylesheet" href="' . $file . '">' . PHP_EOL;
        }

        // if ($this->activeVariant) {

        // If using a theme variant, load its CSS
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
                        }
                    }
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
}
