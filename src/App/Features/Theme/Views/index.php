<?php

declare(strict_types=1);

/**
 * Theme Selection View - Displays available themes and allows switching between them
 *
 * @var array<string, array<string, mixed>> $themes Available themes with metadata
 * @var string $activeTheme Currently active theme name
 * @var bool $previewMode Whether preview mode is active
 * @var string|null $previewTheme Name of theme being previewed (if in preview mode)
 * @var string|null $activeVariant Currently active theme variant
 * @var \Core\Services\ThemeServiceInterface $theme The active theme service
 * @var \Core\Services\ThemeAssetService $themeAssets Theme asset service
 * @var \Core\Services\ThemePreviewService $themePreview Theme preview service
 * @var \Core\Services\ThemeConfigurationManagerService $themeManager Theme configuration manager
 */

// $theme = $this->themeManager->getActiveThemeService(),
?>

<div class="<?= $theme->getElementClass('container') ?>">
    <h1>Theme Selection</h1>

    <?php if ($previewMode) : ?>
    <div class="<?= $theme->getElementClass('alert.info') ?>">
        <p><strong>Preview Mode Active:</strong>
            You are currently previewing the <?= htmlspecialchars(ucfirst($previewTheme ?? 'Unknown')) ?> theme.
            This is temporary and only affects your current session.</p>
        <div class="mt-3">
            <a href="<?= '/theme/apply/'
                . htmlspecialchars($previewTheme ?? '') ?>" class="<?= $theme->getElementClass('button.primary') ?>">
                Apply Theme Permanently
            </a>
            <a href="<?= '/theme/exit-preview?return_url='
                . urlencode($_SERVER['REQUEST_URI'] ?? '/') ?>" class="<?= $theme->getElementClass('button.secondary') ?>">
                Exit Preview Mode
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="<?= $theme->getElementClass('grid') ?>">
        <?php foreach ($themes as $themeId => $themeInfo) : ?>
        <div class="<?= $theme->getElementClass('grid.column') ?>">
            <div class="<?= $theme->getElementClass('card') ?>
                <?= $themeId === $activeTheme ? $theme->getElementClass('card.active') : '' ?>">
                <?php if (!empty($themeInfo['thumbnail'])) : ?>
                <img src="<?= htmlspecialchars($themeInfo['thumbnail']) ?>"
                     class="<?= $theme->getElementClass('card.image') ?>"
                     alt="<?= htmlspecialchars($themeInfo['name']) ?> screenshot">
                <?php endif; ?>

                <div class="<?= $theme->getElementClass('card.body') ?>">
                    <h3 class="<?= $theme->getElementClass('card.title') ?>">
                        <?= htmlspecialchars($themeInfo['name']) ?>
                        <?php if ($themeId === $activeTheme) : ?>
                        <span class="<?= $theme->getElementClass('badge.primary') ?>">Active</span>
                        <?php endif; ?>
                        <?php if ($themeId === $previewTheme) : ?>
                        <span class="<?= $theme->getElementClass('badge.info') ?>">Previewing</span>
                        <?php endif; ?>
                    </h3>
                    <p><?= htmlspecialchars($themeInfo['description']) ?></p>
                    <p>
                        <small class="<?= $theme->getElementClass('text.muted') ?>">
                            Version <?= htmlspecialchars($themeInfo['version']) ?>
                            by <?= htmlspecialchars($themeInfo['author']) ?>
                        </small>
                    </p>

                    <?php if (!empty($themeInfo['supports'])) : ?>
                    <div class="<?= $theme->getElementClass('badge.container') ?>">
                        <?php foreach ($themeInfo['supports'] as $feature) : ?>
                        <span class="<?= $theme->getElementClass('badge.secondary') ?>">
                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $feature))) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php
                    // Show variants for active theme
                    if ($themeId === $activeTheme && isset($themeManager)) :
                        $variants = $themeManager->getAvailableVariants();
                        if (!empty($variants)) :
                    ?>
                    <div class="<?= $theme->getElementClass('card.section') ?>">
                        <h4>Theme Variants</h4>
                        <div class="<?= $theme->getElementClass('variant.container') ?>">
                            <!-- Default variant option -->
                            <a href="<?= '/theme/variant/default?return_url=' . urlencode($_SERVER['REQUEST_URI']) ?>"
                               class="<?= $theme->getElementClass('badge.' .
                                    ($activeVariant === null ? 'active' : 'secondary')) ?>">
                                Default
                            </a>

                            <!-- Each available variant -->
                            <?php foreach ($variants as $variantName => $variantInfo) : ?>
                            <a href="<?= '/theme/variant/' . htmlspecialchars($variantName) .
                                '?return_url=' . urlencode($_SERVER['REQUEST_URI']) ?>"
                               class="<?= $theme->getElementClass('badge.' .
                                    ($activeVariant === $variantName ? 'active' : 'secondary')) ?>">
                                <?= htmlspecialchars($variantInfo['name'] ?? ucfirst($variantName)) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php
                        endif;
                    endif;
                    ?>
                </div>

                <div class="<?= $theme->getElementClass('card.footer') ?>">
                    <div class="<?= $theme->getElementClass('button.group') ?>">
                        <?php if ($themeId !== $previewTheme) : ?>
                        <a href="<?= '/theme/preview/' . htmlspecialchars($themeId) .
                            '?return_url=' . urlencode($_SERVER['REQUEST_URI']) ?>"
                           class="<?= $theme->getElementClass('button.secondary') ?>">
                            Preview
                        </a>
                        <?php else : ?>
                        <a href="<?= '/theme/exit-preview?return_url=' . urlencode($_SERVER['REQUEST_URI']) ?>"
                           class="<?= $theme->getElementClass('button.secondary') ?>">
                            Exit Preview
                        </a>
                        <?php endif; ?>

                        <?php if ($themeId !== $activeTheme) : ?>
                        <a href="<?= '/theme/switch/' . htmlspecialchars($themeId) .
                            '?return_url=' . urlencode($_SERVER['REQUEST_URI']) ?>"
                           class="<?= $theme->getElementClass('button.primary') ?>">
                            Activate
                        </a>
                        <?php else : ?>
                        <button class="<?= $theme->getElementClass('button.primary') ?>" disabled>Active</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>