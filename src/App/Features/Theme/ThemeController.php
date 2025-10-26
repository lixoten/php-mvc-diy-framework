<?php

declare(strict_types=1);

namespace App\Features\Theme;

use App\Enums\FlashMessageType;
use Core\Controller;
use Core\Services\ThemeConfigurationManagerService;
use Core\Services\ThemePreviewService;
use Core\Services\ThemeAssetService;
use Core\Exceptions\ThemeNotFoundException;
use Psr\Http\Message\ResponseInterface;

/**
 * Controller for theme-related actions
 */
class ThemeController extends Controller
{
    /**
     * @var ThemeConfigurationManagerService Theme manager service
     */
    private ThemeConfigurationManagerService $themeManager;

    /**
     * @var ThemePreviewService Theme preview service
     */
    private ThemePreviewService $themePreview;

    /**
     * @var ThemeAssetService Theme asset service
     */
    private ThemeAssetService $themeAsset;

    /**
     * Constructor
     *
     * @param array $route_params Route parameters
     * @param ThemeConfigurationManagerService $themeManager Theme manager service
     * @param ThemePreviewService $themePreview Theme preview service
     * @param ThemeAssetService $themeAsset Theme asset service
     */
    public function __construct(
        array $route_params,
        $flash,
        $view,
        $httpFactory,
        $container,
        $scrap,
        ThemeConfigurationManagerService $themeManager,
        ThemePreviewService $themePreview,
        ThemeAssetService $themeAsset,
        // Include other required dependencies from the parent controller


    ) {
        parent::__construct($route_params, $flash, $view, $httpFactory, $container, $scrap);
        $this->themeManager = $themeManager;
        $this->themePreview = $themePreview;
        $this->themeAsset = $themeAsset;
    }

    /**
     * Show theme selection page
     *
     * @return ResponseInterface Response
     */
    public function indexAction(): ResponseInterface
    {
        $themes = $this->themeManager->getAvailableThemes();
        $activeTheme = $this->themeManager->getActiveTheme();
        $previewMode = $this->themePreview->isPreviewModeActive();
        $previewTheme = $this->themePreview->getPreviewTheme();

        return $this->view('theme/index', [
            'themes' => $themes,
            'activeTheme' => $activeTheme,
            'previewMode' => $previewMode,
            'previewTheme' => $previewTheme,
            'theme' => $this->themeManager->getActiveThemeService(),
            'themePreview' => $this->themePreview,

        ]);
    }

    /**
     * Switch theme
     *
     * @return ResponseInterface Response
     */
    public function switchAction(): ResponseInterface
    {
        $themeName = $this->route_params['theme'] ?? '';
        $returnUrl = $this->request->getQueryParams()['return_url'] ?? '/theme';

        try {
            $this->themeManager->switchTheme($themeName);
            $this->flash22->add('Theme switched to ' . ucfirst($themeName), FlashMessageType::Success);
        } catch (ThemeNotFoundException $e) {
            $this->flash22->add('Theme not found: ' . $themeName, FlashMessageType::Error);
        }

        return $this->redirect($returnUrl);
    }

    /**
     * Preview theme
     *
     * @return ResponseInterface Response
     */
    public function previewAction(): ResponseInterface
    {
        $themeName = $this->route_params['textid'] ?? '';
        $returnUrl = $this->request->getQueryParams()['return_url'] ?? '/';

        try {
            $this->themePreview->enablePreviewMode($themeName);
            $this->flash22->add('Previewing ' . ucfirst($themeName) . ' theme', FlashMessageType::Info);
        } catch (ThemeNotFoundException $e) {
            $this->flash22->add('Theme not found: ' . $themeName, FlashMessageType::Error);
        }

        return $this->redirect($returnUrl);
    }

    /**
     * Exit theme preview mode
     *
     * @return ResponseInterface Response
     */
    public function exitPreviewAction(): ResponseInterface
    {
        $returnUrl = $this->request->getQueryParams()['return_url'] ?? '/';

        $this->themePreview->disablePreviewMode();
        $this->flash22->add('Exited theme preview mode', FlashMessageType::Info);

        return $this->redirect($returnUrl);
    }

    /**
     * Apply theme for current user
     *
     * @return ResponseInterface Response
     */
    public function applyAction(): ResponseInterface
    {
        // Authentication check
        if (!$this->isAuthenticated()) {
            $this->flash22->add('You must be logged in to save theme preferences', FlashMessageType::Error);
            return $this->redirect('/login');
        }

        $themeName = $this->route_params['theme'] ?? '';

        try {
            // Switch active theme
            $this->themeManager->switchTheme($themeName);

            // Save to user preferences
            $userId = $this->getCurrentUserId();
            $this->themeManager->saveUserThemePreference($userId, $themeName);

            // Exit preview mode if active
            if ($this->themePreview->isPreviewModeActive()) {
                $this->themePreview->disablePreviewMode();
            }

            $this->flash22->add('Theme preference saved', FlashMessageType::Success);
        } catch (ThemeNotFoundException $e) {
            $this->flash22->add('Theme not found: ' . $themeName, FlashMessageType::Error);
        }

        return $this->redirect('/theme');
    }

    /**
     * Get current user ID (placeholder method)
     *
     * @return int User ID
     */
    private function getCurrentUserId(): int
    {
        // This would use your actual authentication system
        // return $this->auth->getCurrentUserId();
        return 1; // Placeholder
    }

    /**
     * Check if user is authenticated (placeholder method)
     *
     * @return bool Whether user is authenticated
     */
    private function isAuthenticated(): bool
    {
        // This would use your actual authentication system
        // return $this->auth->isAuthenticated();
        return true; // Placeholder
    }

    /**
     * Set theme variant
     *
     * @param string|null $variant Variant name or 'default' for default
     * @param string|null $returnUrl URL to redirect to after setting variant
     * @return ResponseInterface
     */
    public function setVariantAction(?string $variant = null, ?string $returnUrl = null): ResponseInterface
    {
        $returnUrl = $returnUrl ?? '/';

        if ($variant === 'default') {
            $variant = null;
        }

        // Set the variant
        $this->themeManager->setActiveVariant($variant);

        // Add flash message
        if ($variant) {
            $this->flash22->add("Theme variant '{$variant}' activated.", FlashMessageType::Success);
        } else {
            $this->flash22->add('Using default theme variant.', FlashMessageType::Success);
        }

        // Redirect back
        return $this->httpFactory->createResponse(302)
            ->withHeader('Location', $returnUrl);
    }
}
