<?php

declare(strict_types=1);

// src/Config/view.php
return [
    // View engine settings
    'engine' => $_ENV['VIEW_ENGINE'] ?? 'php', // php, twig, blade, etc.

    // Path settings
    'paths' => [
        'views' => dirname(__DIR__) . '/App/Views',
        'cache' => dirname(dirname(__DIR__)) . '/storage/cache/views',
        'compiled' => dirname(dirname(__DIR__)) . '/storage/framework/views',
    ],

    // Layouts
    'layouts' => [
        'default' => 'layouts/base5simple',
        'auth' => 'layouts/auth',
        'admin' => 'layouts/admin',
        'error' => 'layouts/error',
    ],

    'js' => [
        'enabled' => $_ENV['JS_ENABLED'] ?? true, // or false to disable globally
        'flavor' => $_ENV['JS_FLAVOR'] ?? 'vanilla', // for future use - 'vanilla', 'jquery', 'alpine', etc.
        'available' => [
            'vanilla' => [
                'js' => '/assets/js/vanilla.js',
            ],
            'jquery' => [
                'js' => 'https://code.jquery.com/jquery-3.7.1.min.js',
            ],
            'alpine' => [
                'js' => 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
            ],
        ],
    ],

    // CSS Frameworks (renamed from 'themes')
    'css_frameworks' => [
        'default' => $_ENV['DEFAULT_CSS_FRAMEWORK'] ?? 'bootstrap',
        // 'form' => $_ENV['FORM_CSS_FRAMEWORK'] ?? 'bootstrap',
        'available' => [
            'bootstrap' => [
                'css' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                'js' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            ],
        ],
    ],

    'list' => [
        'options' => [
            'default_sort_key' => 'created_at',
            'default_sort_direction' => 'DESC'
        ],
        'pagination' => [
            'per_page'    => 10,
            'window_size' => 3,
        ],
        'render_options' => [
            'css_framework'         => $_ENV['LIST_CSS_FRAMEWORK'] ?? 'bootstrap',
            'title'                 =>  'list.posts.title default',//shithead2
            'show_actions'          => true,
            'show_action_add'       => false,
            'show_action_edit'      => false,
            'show_action_del'       => false,
            'show_action_status'    => false,
        ],
        'list_fields' => [
            'id'
        ],
    ],

    // Form display settings
    'form' => [
        'render_options' => [
            'from'                  => 'base_config',

            'ajax_save'         => true,     // js-feature
            'auto_save'         => false,    // js-feature Enable auto-save/draft for the whole form
            'use_local_storage' => false,    // js-feature Use localStorage for drafts

            'force_captcha'         => false,
            'security_level'        => 'low',      // HIGH / MEDIUM / LOW
            'layout_type'           => $_ENV['FORM_LAYOUT_TYPE'] ?? 'sequential',
            'error_display'         => $_ENV['FORM_ERROR_DISPLAY'] ?? 'summary',
            'html5_validation'      => false,

            'css_form_theme_class'  => "form-theme-christmas",
            'css_form_theme_file'   => "christmas",
            'default_form_theme'    => 'christmas' ?? 'default',

            'form_heading_level'    => "h2",
            'form_heading'          => "common.form.heading",
            'form_heading_class'         => null, // Do-not change. It uses ThemeService default, See note-#53
            'form_heading_wrapper_class' => null, // Do-not change. It uses ThemeService default, See note-#53

            'submit_text'           => "common.button.save",
            'submit_button_variant' => 'primary',
            'cancel_text'           => 'common.button.cancel', // Added for translation
            'cancel_button_variant' => 'secondary',



            //---???????-----------------------------------
            'css_framework'         => $_ENV['FORM_CSS_FRAMEWORK'] ?? 'bootstrap',
            'show_error_container'  => false,


            'themes'                => [
                'default' => [
                    'css' => ''
                ],
                'dotted' => [
                    'css' => '/assets/css/themes/forms/dotted.css',
                    'class' => 'form-theme-dotted'  // Class applied to form container
                ],
                'neon' => [
                    'css' => '/assets/css/themes/forms/neon.css',
                    'class' => 'form-theme-neon'  // Class applied to form container
                ],
                'christmas' => [
                    'css' => '/assets/css/themes/forms/christmas.css',
                    'class' => 'form-theme-christmas'
                ],
                'rounded' => [
                    'css' => '/assets/css/themes/forms/rounded.css',
                    'class' => 'form-theme-rounded'
                ]
            ],
        ],
        'form_fields' => [
            //'title', 'boo abstract'
        ],

    ],


    // Visual Themes (to be implemented later)
    'visual_themes' => [
        'active' => $_ENV['VISUAL_THEME'] ?? '',
        'available' => [
            'standard' => [
                'css' => '',
            ],
            'christmas' => [
                'for_framework' => 'bootstrap', // Which framework this theme works with
                'css' => '/assets/css/themes/christmas-theme.css',
            ],
            'halloween' => [
                'for_framework' => 'bootstrap',
                'css' => '/assets/css/themes/halloween-theme.css',
            ],
        ],
    ],

    // Assets
    'assets' => [
        'url' => $_ENV['ASSET_URL'] ?? '/assets',
        'version' => $_ENV['ASSET_VERSION'] ?? '1.0',
        'cache_bust' => filter_var($_ENV['ASSET_CACHE_BUST'] ?? true, FILTER_VALIDATE_BOOLEAN),
    ],

    // Caching
    'cache' => [
        'enabled' => filter_var($_ENV['VIEW_CACHE'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'ttl' => $_ENV['VIEW_CACHE_TTL'] ?? 3600, // seconds
    ],

    // Extensions
    'extensions' => [
        'enabled' => true,
        'auto_escape' => true,
    ],
];
