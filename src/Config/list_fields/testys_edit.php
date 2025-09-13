<?php

declare(strict_types=1);

use App\Helpers\DebugRt;

// DebugRt::j('0', '', 'BOOM on Config File');
return [
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
                'maxlength' => 12,
                // 'style' => 'background: red;',
                'data-char-counter' => 'title-counter',
            ],
            'show_char_counter' => true, // js-feature
            'live_validation' => true,
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
                'data-char-counter' => 'content-counter',
            ],
            'show_char_counter' => true, // js-feature
            'live_validation' => true,
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
                'data-char-counter' => 'favorite_word-counter',
                // 'style' => 'background: cyan;',
            ],
            'show_char_counter' => true, // js-feature
            'live_validation' => true,
        ]
    ],
];
