<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

// DebugRt::j('0', '', 'BOOM on Config File');
return [
    'testy_id' => [
        'label' => '',
        'form' => [
            'attributes' => [
                'type' => 'hidden',
            ],
        ]
    ],
    'testy_user_id' => [
        'label' => '',
        'form' => [
            'attributes' => [
                'type' => 'hidden',
            ],
        ]
    ],
    'title' => [
        'label' => 'testys.title',
        'form' => [
            'foo' => 'feeeeeeee',
            'attributes' => [
                'fake' => 'one',
                'type' => 'text',
                'class' => 'foocls',
                'style' => 'border: 2px dotted green;',
                'id' => 'title',
                'placeholder' => 'testys.title.placeholder', //.Enter a testy title'
                'required' => true,
                // 'readonly' => true,
                'minlength' => 5,
                'maxlength' => 30,
                // 'style' => 'background: red;',
                'data-char-counter' => 'title-counter', // js-feature
            ],
            'show_char_counter' => true,    // js-feature
            // 'live_validation' => true,      // js-feature
        ]
    ],
    'content' => [
        'label' => 'testys.content-local',
        'form' => [
            'attributes' => [
                'type' => 'textarea',
                // 'class' => 'form-control',
                'id' => 'content',
                'placeholder' => 'testys.content.placeholder',//'Enter testy content',
                'required' => true,
                // 'disabled' => true,
                'minlength' => 10,
                'maxlength' => 2000,
                // 'style' => 'background: yellow;',
                'rows' => '6',
                'data-char-counter' => 'content-counter', // js-feature
            ],
            'show_char_counter' => true,    // js-feature
            // 'live_validation' => true,      // js-feature
        ]
    ],
    'favorite_word' => [
        'label' => 'testys.favorite_word',
        'form' => [
            'attributes' => [
                'type' => 'text',
                // 'class' => 'form-control',
                'id' => 'favorite_word',
                'placeholder' => 'testys.favorite_word.placeholder', //.Enter a testy title'
                'required' => true,
                'minlength' => 10,
                'maxlength' => 20,
                'data-char-counter' => 'favorite_word-counter', // js-feature
                // 'style' => 'background: cyan;',
            ],
            'show_char_counter' => true,    // js-feature
            // 'live_validation' => true,      // js-feature
        ]
    ],
    'date_of_birth' => [
        'label' => 'testys.date_of_birth',
        'form' => [
            'attributes' => [
                'type' => 'date',
                'id' => 'date_of_birth',
                'placeholder' => 'testys.date_of_birth.placeholder',
                'required' => true,
                'min' => '1900-01-01', // optional: earliest allowed date
                'max' => date('Y-m-d'), // optional: latest allowed date (today)
                // 'style' => 'background: cyan;',
            ],
            // 'live_validation' => true,      // js-feature
        ]
    ],
    'telephone' => [
        'label' => 'testys.telephone',
        'form' => [
            'attributes' => [
                'type' => 'text',
                'id' => 'telephone',
                'placeholder' => 'testys.telephone.placeholder',
                'required' => true,
                'minlength' => 9,
                'maxlength' => 20,
                'data-mask' => 'phone', // todo - mast does not validate.
                'data-country' => 'pt', // todo - revisit for validation -  'pattern', 'minlength', 'maxlength', or 'validators')

                // 'autocomplete' => "off",
                // 'data-char-counter' => 'telephone-counter', // js-feature
                // 'style' => 'background: cyan;',
            ],
            'show_char_counter' => true,    // js-feature // todo, do we wanna block it? at moment it shows
            // 'live_validation' => true,      // js-feature
        ]
    ],
];