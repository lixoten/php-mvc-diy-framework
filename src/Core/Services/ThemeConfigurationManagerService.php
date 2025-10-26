<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Interfaces\ConfigInterface;
use Core\Exceptions\ThemeNotFoundException;
use Core\Session\SessionManagerInterface;

/**
 * Service for managing theme configuration across component types
 */
class ThemeConfigurationManagerService
{
    /**
     * @var ThemeServiceInterface[] Array of theme services by framework
     */
    private array $themeServices = [];


    /**
     * @var string|null Current active theme variant
     */
    private ?string $activeVariant = null;

    /**
     * @var ThemeAssetService Theme asset service
     */
    private ThemeAssetService $themeAssetService;



    /**
     * @var string Current active theme
     */
    private string $activeTheme;

    /**
     * @var ConfigInterface Config service
     */
    private ConfigInterface $config;

    /**
     * @var SessionManagerInterface Session service for persistent theme preference
     */
    private SessionManagerInterface $sessionManager;

    /**
     * Constructor
     *
     * @param ConfigInterface $config Config service
     * @param SessionManagerInterface $sessionManager Session service
     * @param string $defaultTheme Default theme to use
     */
    public function __construct(
        ConfigInterface $config,
        SessionManagerInterface $sessionManager,
        string $defaultTheme = 'bootstrap'
    ) {
        $this->config = $config;
        $this->sessionManager = $sessionManager;

        // Check for user preference in session first
        $userPreference = $this->sessionManager->get('user_theme');

        // Then fall back to config, then default
        $this->activeTheme = $userPreference ?? $config->get('theme.active', $defaultTheme);
    }

    /**
     * Set the theme asset service
     *
     * @param ThemeAssetService $themeAssetService
     * @return self
     */
    public function setThemeAssetService(ThemeAssetService $themeAssetService): self
    {
        // $this->themeAssetService = $themeAssetService;
        // return $this;
        $this->themeAssetService = $themeAssetService;

        // // If we already have an active variant, propagate it to the asset service
        // if ($this->activeVariant !== null && $themeAssetService !== null) {
        //     $themeAssetService->setActiveVariant($this->activeVariant);
        // }
        // If we have an active variant, propagate it to the asset service
        if (isset($this->activeVariant) && $this->activeVariant !== null) {
            $themeAssetService->setActiveVariant($this->activeVariant);
        }


        return $this;
    }



    /**
     * Set the active theme variant
     *
     * @param string|null $variantName Variant identifier or null for default
     * @return self
     */
    public function setActiveVariant(?string $variantName): self
    {
        $this->activeVariant = $variantName;

        // Persist variant choice in session
        if (isset($this->sessionManager)) {
            if ($variantName) {
                $this->sessionManager->set('theme_variant', $variantName);
            } else {
                $this->sessionManager->remove('theme_variant');
            }
        }

        return $this;
    }


    /**
     * Get the active theme variant
     *
     * @return string|null Active variant name or null if using default
     */
    public function getActiveVariant(): ?string
    {
        return $this->activeVariant;
    }



    /**
     * Get available theme variants for the active theme
     *
     * @return array<string, array<string, mixed>> Available variants with metadata
     */
    public function getAvailableVariants(): array
    {
        $variants = $this->config->get("theme.variants.{$this->activeTheme}", []);
        $result = [];

        foreach ($variants as $variantName => $variantInfo) {
            if ($variantName !== 'default') {
                $result[$variantName] = $variantInfo;
            }
        }

        return $result;
    }




    /**
     * Register a theme service
     *
     * @param string $themeName Theme identifier
     * @param ThemeServiceInterface $themeService The theme service
     * @return self
     */
    public function registerThemeService(string $themeName, ThemeServiceInterface $themeService): self
    {
        $this->themeServices[$themeName] = $themeService;
        return $this;
    }

    /**
     * Set the active theme
     *
     * @param string $themeName Theme identifier
     * @return self
     * @throws \InvalidArgumentException If theme not registered
     */
    public function setActiveTheme(string $themeName): self
    {
        if (!isset($this->themeServices[$themeName])) {
            throw new \InvalidArgumentException("Theme '$themeName' not registered");
        }

        $this->activeTheme = $themeName;
        return $this;
    }

    /**
     * Get the active theme service
     *
     * @return ThemeServiceInterface
     * @throws \RuntimeException If no theme services registered
     */
    public function getActiveThemeService(): ThemeServiceInterface
    {
        if (empty($this->themeServices)) {
            throw new \RuntimeException('No theme services registered');
        }

        return $this->themeServices[$this->activeTheme] ?? reset($this->themeServices);
    }

    /**
     * Get the active theme name
     *
     * @return string Active theme name
     */
    public function getActiveTheme(): string
    {
        return $this->activeTheme;
    }


    /**
     * Get available themes with metadata
     *
     * @return array<string, array<string, mixed>> Available themes with metadata
     */
    public function getAvailableThemes(): array
    {
        $themes = [];

        foreach ($this->themeServices as $themeName => $service) {
            $themes[$themeName] = $this->config->get("theme.metadata.{$themeName}", [
                'name' => ucfirst((string)$themeName),
                'description' => ucfirst((string)$themeName) . ' theme',
                'version' => '1.0',
                'author' => 'System',
                'thumbnail' => '/assets/images/themes/' . (string)$themeName . '.png'
            ]);
        }

        return $themes;
    }


    /**
     * Switch theme for current session
     *
     * @param string $themeName Theme identifier
     * @return self
     * @throws ThemeNotFoundException If theme not registered
     */
    public function switchTheme(string $themeName): self
    {
        if (!isset($this->themeServices[$themeName])) {
            throw new ThemeNotFoundException("Theme '$themeName' not registered");
        }

        // Set active theme in this service
        $this->activeTheme = $themeName;

        // Persist theme choice in session
        $this->sessionManager->set('user_theme', $themeName);

        return $this;
    }

    /**
     * Save theme preference permanently for a user
     *
     * @param int $userId User ID
     * @param string $themeName Theme identifier
     * @return bool Success status
     * @throws ThemeNotFoundException If theme not registered
     */
    public function saveUserThemePreference(int $userId, string $themeName): bool
    {
        if (!isset($this->themeServices[$themeName])) {
            throw new ThemeNotFoundException("Theme '$themeName' not registered");
        }

        // This is a placeholder for user preference storage
        // Implementation would depend on your user preference repository
        // You would implement this using your user preference repository
        // $this->userPreferenceRepository->savePreference($userId, 'theme', $themeName);

        return true;
    }


    /**
     * Apply global element styles across all registered themes
     *
     * @param string $elementType Element type identifier
     * @param array<string, string> $classMap Map of theme => class
     * @return self
     */
    public function applyGlobalElementStyle(string $elementType, array $classMap = []): self
    {
        foreach ($this->themeServices as $themeName => $themeService) {
            $class = $classMap[$themeName] ?? $classMap['default'] ?? '';
            if ($class && method_exists($themeService, 'setElementClass')) {
                $themeService->setElementClass($elementType, $class);
            }
        }

        return $this;
    }

    /**
     * Apply global icon style across all registered themes
     *
     * @param string $iconName Icon identifier
     * @param array<string, string> $htmlMap Map of theme => HTML
     * @return self
     */
    public function applyGlobalIconStyle(string $iconName, array $htmlMap = []): self
    {
        foreach ($this->themeServices as $themeName => $themeService) {
            $html = $htmlMap[$themeName] ?? $htmlMap['default'] ?? '';
            if ($html) {
                $themeService->setIconHtml($iconName, $html);
            }
        }

        return $this;
    }


    /**
     * Apply button configuration for all registered themes
     *
     * @param string $buttonType Button type identifier (primary, secondary, etc.)
     * @param array<string, string> $classMap Map of theme => class
     * @return self
     */
    public function configureButtonStyle(string $buttonType, array $classMap = []): self
    {
        // Button elements have a specific prefix in the element class system
        return $this->applyGlobalElementStyle('button.' . $buttonType, $classMap);
    }

    /**
     * Load button configuration from config files
     *
     * @return self
     */
    public function loadButtonConfiguration(): self
    {
        // Load button configuration
        $buttonConfig = $this->config->get('theme.buttons', []);

        // Configure each button type
        foreach ($buttonConfig as $buttonType => $classMap) {
            $this->configureButtonStyle($buttonType, $classMap);
        }

        return $this;
    }



    /**
     * Load and apply theme configuration from config files
     *
     * @return self
     */
    public function loadThemeConfiguration(): self
    {
        // Load global theme configuration
        $globalConfig = $this->config->get('theme.global', []);

        // Apply view layouts from config
        foreach ($globalConfig['layouts'] ?? [] as $layoutName => $layoutMap) {
            $this->applyGlobalViewLayoutStyle($layoutName, $layoutMap);
        }


        // Apply element classes from config
        foreach ($globalConfig['elements'] ?? [] as $elementType => $classMap) {
            $this->applyGlobalElementStyle($elementType, $classMap);
        }

        // Apply icon styles from config
        foreach ($globalConfig['icons'] ?? [] as $iconName => $htmlMap) {
            $this->applyGlobalIconStyle($iconName, $htmlMap);
        }

        // Load button-specific configuration
        $this->loadButtonConfiguration();

        return $this;
    }


    /**
     * Apply global view layout configuration across all registered themes
     *
     * @param string $layoutName Layout name identifier
     * @param array<string, array<string, string>> $layoutMap Map of theme => layout classes
     * @return self
     */
    public function applyGlobalViewLayoutStyle(string $layoutName, array $layoutMap = []): self
    {
        foreach ($this->themeServices as $themeName => $themeService) {
            $layout = $layoutMap[$themeName] ?? $layoutMap['default'] ?? [];
            if (!empty($layout) && method_exists($themeService, 'setViewLayoutClasses')) {
                $themeService->setViewLayoutClasses($layoutName, $layout);
            }
        }

        return $this;
    }

    // // todo
    // // This would be called from ThemeController::applyAction()
    // protected function saveUserThemePreference(string $themeName, ?string $variant = null): void
    // {
    //     if (!$this->authService->isLoggedIn()) {
    //         return;
    //     }

    //     $userId = $this->authService->getCurrentUser()->getId();
    //     $this->userPreferenceRepository->saveThemePreference($userId, $themeName, $variant);
    // }
}
