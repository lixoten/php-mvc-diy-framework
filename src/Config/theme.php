<?php

return [
    // Active theme
    'active' => $_ENV['DEFAULT_THEME'] ?? 'bootstrap',

    // Default fallback theme
    'default' => 'bootstrap',


    // Theme variants (visual styles)
    'variants' => [
        'bootstrap' => [
            'default' => null, // Default bootstrap styling
            'christmas' => [
                'name' => 'Christmas',
                'description' => 'Festive winter theme with snowflakes',
                'css' => '/assets/themes/bootstrap/css/christmas-theme.css'
            ],
            'halloween' => [
                'name' => 'Halloween',
                'description' => 'Spooky dark theme',
                'css' => '/assets/themes/bootstrap/css/halloween-theme.css'
            ]
        ],
        'material' => [
            'default' => null,
            'christmas' => [
                'name' => 'Christmas',
                'description' => 'Festive winter theme for Material',
                'css' => '/assets/themes/material/css/christmas-theme.css'
            ]
        ],
        'vanilla' => [
            'default' => null
        ]
    ],


    // ✅ Form theme configuration (framework-neutral form styling)
    'form_themes' => [
        // Base path for all form theme CSS files
        'base_path' => '/assets/css/themes/forms',

        // Available form themes (framework-neutral)
        'available' => [
            'neon' => [
                'name' => 'Neon Cyberpunk',
                'description' => 'Glowing neon theme with cyberpunk aesthetics',
                'css' => 'neon.css',
                'preview_url' => '/theme/preview/form/neon',
                'framework_neutral' => true, // ✅ Works with all frameworks
                'tags' => ['futuristic', 'dark', 'glow'],
            ],
            // EXAMPLE of  'framework_neutral' => false,
            // 'neon-bootstrap' => [
            //     'name' => 'Neon Cyberpunk (Bootstrap Enhanced)',
            //     'framework_neutral' => false, // ⚠️ Bootstrap-specific
            //     'framework' => 'bootstrap',
            //     'css' => 'neon-bootstrap.css', // ⚠️ Uses .input-group, .form-floating
            //     'base_theme' => 'neon', // ✅ Extends neutral theme
            // ],
            'christmas' => [
                'name' => 'Christmas Holiday',
                'description' => 'Festive winter theme for forms',
                'css' => 'christmas.css',
                'preview_url' => '/theme/preview/form/christmas',
                'framework_neutral' => true,
                'tags' => ['seasonal', 'festive', 'holiday'],
            ],
            'retro' => [
                'name' => 'Retro 80s',
                'description' => 'Vintage 1980s aesthetic with bold colors',
                'css' => 'retro.css',
                'preview_url' => '/theme/preview/form/retro',
                'framework_neutral' => true,
                'tags' => ['vintage', 'colorful', '80s'],
            ],
            'minimal' => [
                'name' => 'Minimal Clean',
                'description' => 'Clean, minimalist form styling',
                'css' => 'minimal.css',
                'preview_url' => '/theme/preview/form/minimal',
                'framework_neutral' => true,
                'tags' => ['clean', 'simple', 'modern'],
            ],
        ],

        // Default form theme (null = no form theme by default)
        'default' => null,

        // ✅ OPTIONAL: Cache busting version for form themes
        'version' => '1.0.0',
    ],



    // Theme metadata
    'metadata' => [
        'bootstrap' => [
            'name' => 'Bootstrap',
            'description' => 'Modern responsive theme based on Bootstrap 5',
            'version' => '1.0',
            'author' => 'MVC LIXO Team',
            'thumbnail' => '/assets/images/themes/bootstrap.png',
            'preview_url' => '/theme/preview/bootstrap',
            'supports' => ['responsive', 'dark_mode', 'rtl']
        ],
        'material' => [
            'name' => 'Material Design',
            'description' => 'Google Material Design theme',
            'version' => '1.0',
            'author' => 'MVC LIXO Team',
            'thumbnail' => '/assets/images/themes/material.png',
            'preview_url' => '/theme/preview/material',
            'supports' => ['responsive', 'dark_mode']
        ],
        'vanilla' => [
            'name' => 'Vanilla CSS',
            'description' => 'Minimalist pure CSS theme',
            'version' => '1.0',
            'author' => 'MVC LIXO Team',
            'thumbnail' => '/assets/images/themes/vanilla.png',
            'preview_url' => '/theme/preview/vanilla',
            'supports' => ['responsive']
        ],
    ],


    ////////////////////////////////////////////////////////////
    //// Metadata for UI ////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////
    /*
    // ✅ Build a theme selector dropdown from config:
    $availableThemes = $this->config->get('theme.form_themes.available', []);

    foreach ($availableThemes as $key => $theme) {
        echo "<option value='{$key}'>{$theme['name']} - {$theme['description']}</option>";
    }

    // output example
    // <option value='neon'>Neon Cyberpunk - Glowing neon theme with cyberpunk aesthetics</option>
    // <option value='christmas'>Christmas Holiday - Festive winter theme for forms</option>
    // <option value='retro'>Retro 80s - Vintage 1980s aesthetic</option>

    */
    ////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////

    // Theme assets
    'assets' => [
        // Global assets used by all themes
        'global' => [
            'css' => [
                'default' => [
                    [
                        'path' => '/assets/css/normalize.css'
                    ],

                    [
                        'path' => '/assets/css/common.css'
                    ],
                    [
                        'path' => '/assets/css/form-constraints.css'
                    ]
                ],
                'admin' => [
                        // 'path' => '/assets/css/normalize.css'
                        // 'path' => '/assets/css/common.css'
                        // 'path' => '/assets/css/admin.css'
                    [
                        'path' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
                    ],
                    [
                        'path' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
                    ],
                    [
                        'path' => '/assets/themes/bootstrap/css/style.css'
                    ],
                    [
                        'path' => '/assets/themes/bootstrap/css/admin.css'
                    ]
                ]
            ],
            'js' => [
                'default' => [
                    'head' => [
                        [
                            'path' => '/assets/js/modernizr.js'
                        ]
                    ],
                    'footer' => [
                        [
                            'path' => '/assets/js/common.js'
                        ]
                    ]
                ],
                'admin' => [
                    'head' => [
                        [
                            'path' => '/assets/js/modernizr.js'
                        ]
                    ],
                    'footer' => [
                        [
                            'path' => '/assets/js/common.js'
                        ],
                        [
                            'path' => '/assets/js/admin.js'
                        ]
                    ]
                ]
            ]
        ],

        // Bootstrap theme assets
        'bootstrap' => [
            'css' => [
                'default' => [
                    // '/assets/themes/bootstrap/css/bootstrap-core.css',
                    // // Variant CSS would be added dynamically
                    // '/assets/themes/bootstrap/css/font-awesome.min.css', // Local Font Awesome
                    // '/assets/themes/bootstrap/css/style.css' // Your custom styles
                    // // 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                    // // 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                    // // '/assets/themes/bootstrap/css/style.css'
                    [
                        'path' => '/assets/themes/bootstrap/css/bootstrap-core.css',
                        'media' => 'all'
                    ],
                    [
                        'path' => '/assets/themes/bootstrap/css/font-awesome.min.css'
                    ],
                    [
                        'path' => '/assets/themes/bootstrap/css/style-bootstrap.css'
                    ],
                    [
                        'path' => '/assets/themes/bootstrap/css/menu-bootstrap.css'
                    ],
                ],
                'admin' => [
                    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
                    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                    '/assets/themes/bootstrap/css/style.css',
                    '/assets/themes/bootstrap/css/admin.css'
                ]
            ],
            'js' => [
                'default' => [
                    'head' => [],
                    'footer' => [
                        // // 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                        // // '/assets/themes/bootstrap/js/theme.js'
                        // '/assets/themes/bootstrap/js/bootstrap.bundle.min.js',
                        // '/assets/themes/bootstrap/js/theme.js'
                        [
                            'path' => '/assets/themes/bootstrap/js/bootstrap.bundle.min.js'
                        ],
                        [
                            'path' => '/assets/themes/bootstrap/js/theme.js',
                            'defer' => true
                        ]
                    ]
                ],
                'admin' => [
                    'head' => [],
                    'footer' => [
                        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
                        '/assets/themes/bootstrap/js/theme.js',
                        '/assets/themes/bootstrap/js/admin.js'
                    ]
                ]
            ]
        ],

        // Material theme assets
        'material' => [
            'css' => [
                'default' => [
                    [
                        'path' => 'https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css',
                        'media' => 'all'
                    ],
                    [
                        'path' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
                    ],
                    [
                        'path' => '/assets/themes/material/css/style.css'
                    ]
                ],
                'admin' => [
                    [
                        'path' => 'https://unpkg.com/material-components-web@latest/dist/material-components-web.min.css'
                    ],
                    [
                        'path' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
                    ],
                    [
                        'path' => '/assets/themes/material/css/style.css'
                    ],
                    [
                        'path' => '/assets/themes/material/css/admin.css'
                    ]
                ]
            ],
            'js' => [
                'default' => [
                    'head' => [],
                    'footer' => [
                        [
                            'path' => 'https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js'
                        ],
                        [
                            'path' => '/assets/themes/material/js/theme.js'
                        ]
                    ]
                ],
                'admin' => [
                    'head' => [],
                    'footer' => [
                        [
                            'path' => 'https://unpkg.com/material-components-web@latest/dist/material-components-web.min.js'
                        ],
                        [
                            'path' => '/assets/themes/material/js/theme.js'
                        ],
                        [
                            'path' => '/assets/themes/material/js/admin.js'
                        ]
                    ]
                ]
            ]
        ],

        // Vanilla theme assets
        'vanilla' => [
            'css' => [
                'default' => [
                    [
                        'path' => '/assets/themes/vanilla/css/base.css'
                    ],
                    [
                        'path' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
                    ],
                    [
                        'path' => '/assets/themes/vanilla/css/style.css'
                    ]
                ],
                'admin' => [
                    [
                        'path' => '/assets/themes/vanilla/css/base.css'
                    ],
                    [
                        'path' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
                    ],
                    [
                        'path' => '/assets/themes/vanilla/css/style.css'
                    ],
                    [
                        'path' => '/assets/themes/vanilla/css/admin.css'
                    ]
                ]
            ],
            'js' => [
                'default' => [
                    'head' => [],
                    'footer' => [
                        [
                            'path' => '/assets/themes/vanilla/js/theme.js'
                        ]
                    ]
                ],
                'admin' => [
                    'head' => [],
                    'footer' => [
                        [
                            'path' => '/assets/themes/vanilla/js/theme.js'
                        ],
                        [
                            'path' => '/assets/themes/vanilla/js/admin.js'
                        ]
                    ]
                ]
            ]
        ]
    ],

    'layouts' => [
        'bootstrap' => [
            'default' => 'layouts/bootstrap/bootstrap_default',
            'minimal' => 'layouts/bootstrap_minimal',
            'admin' => 'layouts/bootstrap_admin',
        ],
        'material' => [
            'default' => 'layouts/material_default',
            'minimal' => 'layouts/material_minimal',
            'admin' => 'layouts/material_admin',
        ],
        'vanilla' => [
            'default' => 'layouts/vanilla_default',
            'minimal' => 'layouts/vanilla_minimal',
            'admin' => 'layouts/vanilla_admin',
        ],
    ],

    // Todo as needed
    // Adding more comprehensive element definitions in the global.elements section,
    // but we can easily expand that as needed when implementing other UI components.
    'global' => [
        'elements' => [
            'button.primary' => [
                'bootstrap' => 'btn btn-primary',
                'material' => 'mdc-button mdc-button--raised',
                'vanilla' => 'vanilla-button vanilla-button-primary',
                'default' => 'button primary' // Fallback for any theme
            ],
            // Layout elements
            'layout.container' => [
                'bootstrap' => 'containerLayout',
                'material' => 'mdc-layout-grid container',
                'vanilla' => 'containerLayout',
                'default' => 'containerLayout'
            ],
            'layout.header' => [
                'bootstrap' => 'header',
                'material' => 'mdc-layout-grid__cell mdc-layout-grid__cell--span-12 header',
                'vanilla' => 'header',
                'default' => 'header'
            ],
            'layout.main-content' => [
                'bootstrap' => 'main-content',
                'material' => 'mdc-layout-grid__cell mdc-layout-grid__cell--span-8 main-content',
                'vanilla' => 'main-content',
                'default' => 'main-content'
            ],
            'layout.sidebar' => [
                'bootstrap' => 'left-sidebar',
                'material' => 'mdc-layout-grid__cell mdc-layout-grid__cell--span-4 left-sidebar',
                'vanilla' => 'left-sidebar',
                'default' => 'left-sidebar'
            ],
            'layout.footer' => [
                'bootstrap' => 'footer',
                'material' => 'mdc-layout-grid__cell mdc-layout-grid__cell--span-12 footer',
                'vanilla' => 'footer',
                'default' => 'footer'
            ],
            // ... other elements

            // More element types...
        ],

        'icons' => [
            'user' => [
                'bootstrap' => '<i class="fas fa-user"></i>',
                'material' => '<i class="material-icons">person</i>',
                'default' => '<i class="icon icon-user"></i>'
            ],
            // More icons...
        ],

        'buttons' => [
            'primary' => [
                'bootstrap' => 'btn btn-primary',
                'material' => 'btn btn-primary rounded-pill',
                'vanilla' => 'vanilla-button vanilla-button-primary',
                'default' => 'button primary'
            ],
            'secondary' => [
                'bootstrap' => 'btn btn-secondary',
                'material' => 'btn btn-outline-primary rounded-pill',
                'vanilla' => 'vanilla-button vanilla-button-secondary',
                'default' => 'button secondary'
            ],
            'add' => [
                'bootstrap' => 'btn btn-primary float-end',
                'material' => 'btn btn-primary rounded-pill shadow-sm',
                'vanilla' => 'vanilla-button vanilla-button-primary vanilla-float-end',
                'default' => 'button add'
            ],
            'view' => [
                'bootstrap' => 'btn btn-info',
                'material' => 'btn btn-outline-info btn-icon rounded-circle',
                'vanilla' => 'vanilla-button vanilla-button-info',
                'default' => 'button view'
            ],
            'edit' => [
                'bootstrap' => 'btn btn-primary',
                'material' => 'btn btn-outline-primary btn-icon rounded-circle',
                'vanilla' => 'vanilla-button vanilla-button-primary',
                'default' => 'button edit'
            ],
            'delete' => [
                'bootstrap' => 'btn btn-danger',
                'material' => 'btn btn-outline-danger btn-icon rounded-circle',
                'vanilla' => 'vanilla-button vanilla-button-danger',
                'default' => 'button delete'
            ],
            'group' => [
                'bootstrap' => 'btn-group',
                'material' => 'd-flex gap-2',
                'vanilla' => 'vanilla-button-group',
                'default' => 'button-group'
            ]
        ]
    ]
];
