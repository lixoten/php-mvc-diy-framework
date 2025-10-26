<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Services\ThemeConfigurationManagerService;
use Core\Exceptions\ThemeNotFoundException;
use Core\Session\SessionManagerInterface;

/**
 * Service for theme preview functionality
 */
class ThemePreviewService
{
    /**
     * @var SessionManagerInterface Session service
     */
    private SessionManagerInterface $sessionManager;

    /**
     * @var ThemeConfigurationManagerService Theme manager
     */
    private ThemeConfigurationManagerService $themeManager;

    /**
     * @var string Session key for preview mode
     */
    private const PREVIEW_MODE_KEY = 'theme_preview_mode';

    /**
     * @var string Session key for preview theme
     */
    private const PREVIEW_THEME_KEY = 'theme_preview_name';

    /**
     * Constructor
     *
     * @param SessionManagerInterface $sessionManager Session manager
     * @param ThemeConfigurationManagerService $themeManager Theme manager
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        ThemeConfigurationManagerService $themeManager
    ) {
        $this->sessionManager = $sessionManager;
        $this->themeManager = $themeManager;
    }

    /**
     * Enable theme preview mode
     *
     * @param string $themeName Theme to preview
     * @return bool Success status
     * @throws ThemeNotFoundException If theme not registered
     */
    public function enablePreviewMode(string $themeName): bool
    {
        // Check if theme exists
        $availableThemes = $this->themeManager->getAvailableThemes();
        if (!isset($availableThemes[$themeName])) {
            throw new ThemeNotFoundException("Theme '{$themeName}' not found");
        }

        // Save current theme for later restoration
        $currentTheme = $this->themeManager->getActiveTheme();
        $this->sessionManager->set('theme_original', $currentTheme);

        // Set preview mode and theme
        $this->sessionManager->set(self::PREVIEW_MODE_KEY, true);
        $this->sessionManager->set(self::PREVIEW_THEME_KEY, $themeName);

        // Apply the preview theme
        $this->themeManager->switchTheme($themeName);

        return true;
    }

    /**
     * Disable theme preview mode
     *
     * @return bool Success status
     */
    public function disablePreviewMode(): bool
    {
        // Check if we're in preview mode
        if (!$this->isPreviewModeActive()) {
            return false;
        }

        // Get original theme
        $originalTheme = $this->sessionManager->get('theme_original');

        // Clear preview mode
        $this->sessionManager->remove(self::PREVIEW_MODE_KEY);
        $this->sessionManager->remove(self::PREVIEW_THEME_KEY);
        $this->sessionManager->remove('theme_original');

        // Restore original theme
        if ($originalTheme) {
            $this->themeManager->switchTheme($originalTheme);
        }

        return true;
    }

    /**
     * Apply theme preview if active
     *
     * @return bool Whether preview was applied
     */
    public function applyPreviewIfActive(): bool
    {
        if (!$this->isPreviewModeActive()) {
            return false;
        }

        $previewTheme = $this->sessionManager->get(self::PREVIEW_THEME_KEY);
        if ($previewTheme) {
            try {
                $this->themeManager->switchTheme($previewTheme);
                return true;
            } catch (ThemeNotFoundException $e) {
                $this->disablePreviewMode();
                return false;
            }
        }

        return false;
    }

    /**
     * Check if theme preview mode is active
     *
     * @return bool Whether preview mode is active
     */
    public function isPreviewModeActive(): bool
    {
        return (bool) $this->sessionManager->get(self::PREVIEW_MODE_KEY, false);
    }

    /**
     * Get preview theme name if in preview mode
     *
     * @return string|null Preview theme name or null if not in preview mode
     */
    public function getPreviewTheme(): ?string
    {
        if (!$this->isPreviewModeActive()) {
            return null;
        }

        return $this->sessionManager->get(self::PREVIEW_THEME_KEY);
    }
}
