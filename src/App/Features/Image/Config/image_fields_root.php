<?php

/**
 * Generated File - Date: 20251206_075530 origgggggggggggggggggggg
 * Field definitions for the image_root entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

// id
// title
// filename
// original_filename
return [
    'id' => [
        'list' => [
            'sortable'    => false,
        ],
    ],
    'title' => [
        'list' => [
            'sortable'    => false,
            'formatters' => [
                'text' => [
                    // 'xxxxxxmax_length' => 5,
                    // 'truncate_suffix' => '...',          // Defaults to ...
                    // 'null_value' => 'Nothing here',      // Replaces null value with string
                    // 'suffix'     => "Boo",               // Appends to end of text
                    // 'transform'  => 'lowercase',
                    // 'transform'  => 'uppercase',
                    // 'transform'  => 'capitalize',
                    // 'transform'  => 'title',
                    // 'transform'  => 'trim',              // notes-: assuming we did not store clean data
                    // 'transform'  => 'last2char_upper',
                ],
            ]
        ],
        'form' => [
            'type'        => 'text',
            // 'show_label'  => false,
            'placeholder' => true,
            'attributes'  => [
                'required'    => true,
                'minlength'   => 5,
                'maxlength'   => 50,
                // 'pattern'     => '/^[a-z0-9./',
                // 'style'       => 'background:yellow;',
                // 'data-char-counter'    => false,
                // 'data-live-validation' => false,
            ],
        ],
        'validators' => [
            'text' => [
                // 'ignore_allowed'   => true,
                // 'ignore_forbidden' => false,
                // 'allowed'          => [aaaa, bbbb],
                // 'forbidden'        => [fuck, dick],
            ],
        ],
    ],
    'filename' => [
        'list' => [
            'sortable'    => false,
            'formatters' => [
                'image_link' => [
                    'preset' => 'thumbs', // ✅ Use 'preset' for ImageStorageService to resolve path
                    'default_image' => '/assets/images/default-avatar.png', // ✅ Fallback
                    'alt_field' => 'generic_text', // ✅ Use this field for alt text
                    'width' => 150, // ✅ Thumbnail size
                    'height' => 150,
                    // 'link_to' => '/testy/view/{id}', // ✅ OPTIONAL: Make image clickable
                ],
            ],
        ],
        'form' => [
            'type'        => 'file',
            // 'show_label'    => false,
            // 'placeholder' => true,
            'attributes'  => [
                'required' => true,
                'accept' => 'image/*',
            ],
            'upload' => [
                // 'max_size' => 2097152, // 2MB
                'max_size' => 1000000, // 2MB
                'mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
                'subdir' => 'profiles',
            ],
            'formatters' => [
                'image_link' => [
                    'preset' => 'thumbs', // ✅ Use 'preset' for ImageStorageService to resolve path
                    'default_image' => '/assets/images/default-avatar.png', // ✅ Fallback
                    'alt_text' => 'Current Profile Picture', // ✅ Static alt text
                    'width' => 150, // ✅ Thumbnail size
                    'height' => 150,
                    'css_class' => 'img-thumbnail mb-2', // Add some Bootstrap styling
                    // 'link_to' => '/testy/view/{id}', // ✅ OPTIONAL: Make image clickable
                ],
            ],
        ],
        'validators' => [
            'file' => [
                'max_size' => 2097152, // 2MB (2 * 1024 * 1024)
                'mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
                // 'subdir' is for storage, not direct validation, so omit here
            ],
        ],
    ],
    'original_filename' => [
        'list' => [
            'sortable'    => false,
            'formatters' => [
                'text' => [
                ],
        ],
        ],
        'form' => [
            'type'        => 'display',
            // 'show_label'    => false,
            // 'placeholder' => true,
            'attributes'  => [
            ],
            'formatters' => [
                'text' => [
                ],
        ],
        ],
        'validators' => [
            'text' => [
            ],
        ],
    ],
];
//334
