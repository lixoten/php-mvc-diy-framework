<?php

declare(strict_types=1);

/**
 * Filesystem configuration settings.
 *
 * Defines root paths for public and private storage, and base URLs for public assets.
 */
return [
    'public_html_root'         => dirname(__DIR__, 2) . '/src/public_html', // Project root /public_html
    'storage_root'             => dirname(__DIR__, 2) . '/src/storage',     // Project root /storage
    'public_base_url'          => [
        'store_images' => '/store', // Base URL prefix for store-specific images
        // Add other public base URLs if needed, e.g., 'assets' => '/assets'
    ],
    // Add other filesystem-related configurations here
    // 'uploads_max_size' => '2M',
    // 'allowed_mime_types' => ['image/jpeg', 'image/png'],
    // ✅ Default image extension for presets if not explicitly stored
    'default_image_extension' => [
        'thumbs' => 'png', // Force all thumbnails to be .jpg for consistent display
        'web'    => 'jpg', // Example for 'web' preset
        'original' => 'original', // Placeholder for original
    ],

    // ✅ Centralized image preset definitions (dimensions, quality, etc.)
    'image_presets' => [
        'thumbs' => [
            'width' => 150,
            'height' => 150,
            'quality' => 80, // Example: could define default processing quality here
        ],
        'web' => [
            'width' => 800,
            'height' => 600,
            'quality' => 85,
        ],
        'original' => [
            // Original preset typically has no fixed dimensions, or max constraints
        ],
        // Add other presets as needed
    ],

    // ✅ NEW: Image generation settings, including preferred formats for <picture> tag sources
    'image_generation' => [
        'preferred_formats' => ['avif', 'webp', 'jpg', 'png'], // Order matters for <picture> source tags (most efficient first)
        // Add other generation-specific settings here, e.g., default quality for new formats
    ],

];
