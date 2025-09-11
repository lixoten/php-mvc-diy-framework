<?php

declare(strict_types=1);

return [
    'render_options' => [
        'force_captcha' => false,
        'layout_type' => 'sequential',  //CONST_L::SEQUENTIAL,    // FIELDSETS / SECTIONS / SEQUENTIAL
        'security_level' => 'low',      //CONST_SL::LOW,      // HIGH / MEDIUM / LOW
        'error_display' => 'summary',   //CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
        'html5_validation' => false,
        'css_form_theme_class' => "form-theme-christmas",
        'css_form_theme_file' => "christmas",
        'form_heading' => "Create Post Parent",
        'submit_text' => "Add Parent",
        'form_fields' => [
            'title', 'content'
        ],
    ],
];
