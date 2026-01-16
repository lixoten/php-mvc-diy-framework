<?php

/**
 * Form field attribute schema for all field types.
 *
 * @return array<string, array<string, mixed>>
 *
 */

declare(strict_types=1);

// 29 26

return [
    'global' => [ // 1
        'id'        => ['default' => null,  'values' => 'string'],
        'class'     => ['default' => null,  'values' => 'string'],
        'style'     => ['default' => null,  'values' => 'string'],
        'title'     => ['default' => null,  'values' => 'string'],
        'lang'      => ['default' => null,  'values' => 'string'],
        'accesskey' => ['default' => null,  'values' => 'string'],
        'tabindex'  => ['default' => null,  'values' => 'int'],         // Note: tabindex accepts an integer
        'dir'       => ['default' => null,  'values' => ['ltr', 'rtl']],// Note: dir has specific values
        'hidden'    => ['default' => false, 'values' => 'bool'],        // Note: hidden is a boolean attribute

        'name'          => ['values' => 'string',  'default' => null],
        'value'         => ['values' => 'string',  'default' => null],
        'form'          => ['values' => 'string',  'default' => null],
        'list'          => ['values' => 'string',  'default' => null],

        'disabled'      => ['values' => 'bool',    'default' => false],
        'readonly'      => ['values' => 'bool',    'default' => false],
        'autofocus'     => ['values' => 'bool',    'default' => false],

        'placeholder'   => ['default' => null, 'values' => 'string'],
        'spellcheck'    => ['default' => null, 'values' => ['true', 'false']],

        'dirname'       => ['values' => 'string',  'default' => null],
        'size'          => ['values' => 'int',     'default' => null],

        'autocomplete'  => [
            'default' => null, // Default to null globally
            'values'  => [ // Comprehensive list of standard autocomplete tokens
                'on', 'off',
                'name', 'honorific-prefix', 'given-name', 'additional-name', 'family-name',
                'honorific-suffix', 'nickname', 'organization-title', 'username',
                'new-password', 'current-password', 'one-time-code',

                'organization', 'street-address', 'address-line1', 'address-line2', 'address-line3',
                'address-level4', 'address-level3', 'address-level2', 'address-level1',
                'country', 'country-name', 'postal-code',

                'cc-name', 'cc-given-name', 'cc-additional-name', 'cc-family-name',
                'cc-number',
                'cc-exp', 'cc-exp-month', 'cc-exp-year',
                'cc-csc', 'cc-type',

                'transaction-currency', 'transaction-amount',

                'language', 'bday', 'bday-day', 'bday-month', 'bday-year',
                'sex', 'url', 'photo',

                'tel', 'tel-country-code', 'tel-national', 'tel-area-code',
                'tel-local', 'tel-local-prefix', 'tel-local-suffix', 'tel-extension',

                'email', 'impp',

                'gender', 'home', 'work', 'mobile', 'fax', 'pager', 'shipping', 'billing',
            ],
        ],
        'inputmode'     => ['default' => null, 'values' =>
                                                ['text', 'numeric', 'decimal', 'email', 'tel', 'url', 'search', 'none']],


        // 'accept'          => null, // not applicable
        // 'multiple'        => null, // not applicable

        // Used in Validation
        'required'      => ['values'  => 'bool',    'default' => false],
        'min'           => ['values'  => 'numeric', 'default' => null],
        'max'           => ['values'  => 'numeric', 'default' => null],
        'step'          => ['values'  => 'int',     'default' => null],
        'maxlength'     => ['values'  => 'int',     'default' => null],
        'minlength'     => ['values'  => 'int',     'default' => null],
        'pattern'       => ['default' => null, 'values' => 'string'],
    ],
    'display' => [ // 2
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        'autocomplete'  => null, // not applicable
        'inputmode'     => null, // not applicable
        'readonly'      => null, // not applicable

        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // not applicable - use CSS is recommended
        'pattern'       => null, // not applicable

        'list'          => null, // not applicable
        'form'          => null, // not applicable
        'value'         => null, // not applicable

        'placeholder'          => null, // not applicable
        'multiple'          => null, // not applicable
        'accept'          => null, // not applicable
        'disabled'          => null, // not applicable
        'type'          => null, // not applicable
        'step'          => null, // not applicable
        //End Global set to null
    ],
    'hidden' => [ // 3
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        // fixme? do i need to add all to here?
        //End Global set to null
    ],
    'text' => [ // 4
        // Global Like if not applicable set to null
        'dir'           => null, // Not Applicable: Not content-based
        'hidden'        => null, // Not Applicable: General HTML attribute
        'lang'          => null, // Not Applicable: Not content-based
        'list'          => null, // Not Recommended: Use 'autocomplete' or other types for suggested values
        'max'           => null, // Not Applicable: Only for numeric/date/time; use 'maxlength' for text
        'min'           => null, // Not Applicable: Only for numeric/date/time; use 'minlength' for text
        'step'          => null, // Not Applicable: Only for numeric values
        'size'          => null, // valid, but not recommended. CSS is recommended
        //End Global set to null

        // 'autocomplete'  => [
        //     'default' => null, // if setting 'on' it will be included every time, and we do not want that, thus null
        //     'values'  => [
        //         'on', 'off',

        //         'name', 'honorific-prefix', 'given-name', 'additional-name', 'family-name',
        //         'honorific-suffix', 'nickname', 'organization-title', 'username',
        //         'new-password', 'current-password', 'one-time-code',

        //         'organization', 'street-address', 'address-line1', 'address-line2', 'address-line3',
        //         'address-level4', 'address-level3', 'address-level2', 'address-level1',
        //         'country', 'country-name', 'postal-code',

        //         'cc-name', 'cc-given-name', 'cc-additional-name', 'cc-family-name',
        //         'cc-number',
        //         'cc-exp', 'cc-exp-month', 'cc-exp-year',
        //         'cc-csc', 'cc-type',

        //         'transaction-currency', 'transaction-amount',

        //         'language', 'bday', 'bday-day', 'bday-month', 'bday-year',
        //         'sex', 'url', 'photo',

        //         'tel', 'tel-country-code', 'tel-national', 'tel-area-code',
        //         'tel-local', 'tel-local-prefix', 'tel-local-suffix', 'tel-extension',

        //         'email', 'impp',

        //         'gender', 'home', 'work', 'mobile', 'fax', 'pager', 'shipping', 'billing',
        //     ],
        // ],
        // 'inputmode'     => ['default' => null, 'values' =>
        //                              ['text', 'numeric', 'decimal', 'email', 'tel', 'url', 'search', 'none']],

        'data-char-counter'     => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],
        'placeholder'   =>  ['default' => false, 'values' => 'bool'],
        // 'placeholder'   => ['default' => "Enter Phoddddne", 'values' => 'string'],

        'default_validation_rules' => [
            'foofoo' => ['values' => 'bool', 'default' => true],
            'ignore_allowed'           => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'         => ['values' => 'bool', 'default' => false],
            'allowed'                  => ['values' => 'array', 'default' => ['aaaa', 'bbbb']],
            'forbidden'                => ['values' => 'array', 'default' => ['fuck', 'dick', 'cock']],
        ],
    ],
    'textarea' => [ // 5
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        'autocomplete'  => null, // is valid, but complicated. use js if needed
        //End Global set to null
        'rows'          => ['values' => 'int',     'default' => null],
        'cols'          => ['values' => 'int',     'default' => null],
        'wrap'  => ['default' => false, 'values' => 'bool'],
        'autocapitalize'  => ['default' => false, 'values' => 'bool'],

        'inputmode'     => ['default' => null, 'values' =>
                                                ['text', 'numeric', 'decimal', 'email', 'tel', 'url', 'search']],

        'data-char-counter'     => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'default_validation_rules' => [
            // 'value_kind'               => ['values' => 'string', 'default' => 'integer'],
            // 'min' => 444,
            //  'max'           => ['default' => 999, 'values' => 'numeric'],
            // 'positive_only'            => ['values' => 'bool', 'default' => false],
            // 'negative_only'            => ['values' => 'bool', 'default' => false],
            // 'zero_not_allowed'         => ['values' => 'bool', 'default' => false],
            // 'enforce_step'             => ['values' => 'bool', 'default' => false],

            'ignore_allowed'           => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'         => ['values' => 'bool', 'default' => false],
            'allowed'                  => ['values' => 'array', 'default' => ['aaaa', 'bbbb']],
            'forbidden'                => ['values' => 'array', 'default' => ['fuck', 'dick']],
        ],
    ],
    'password' => [ // 6
        // Global Like if not applicable set to null
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for 'text'/'search'
        'hidden'        => null, // Not Applicable: General HTML attribute
        'lang'          => null, // Not Applicable: Not content-based
        'list'          => null, // Not Applicable: Violates security purpose of password field
        'max'           => null, // Not Applicable: Only for numeric/date/time; use 'maxlength'
        'min'           => null, // Not Applicable: Only for numeric/date/time; use 'minlength'
        'readonly'      => null, // Not Recommended: Usually defeats purpose of a password input
        'size'          => null, // Not Recommended: Use CSS for width
        'spellcheck'    => null, // Not Recommended: Should be false for security
        'step'          => null, // Not Applicable: Only for numeric values
        //End Global set to null

        'autocomplete'  => [
            'default' => 'one-time-code',
            'values'  => ['current-password', 'new-password', 'one-time-code'],
        ],
        'inputmode'     => ['default' => 'text', 'values' => ['text', 'numeric']],

        'data-char-counter'     => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'default_validation_rules' => [
            // 'value_kind'               => ['values' => 'string', 'default' => 'integer'],
            // 'min' => 444,
            //  'max'           => ['default' => 999, 'values' => 'numeric'],

            'require_digit'     => ['values' => 'bool', 'default' => true],
            'require_uppercase' => ['values' => 'bool', 'default' => true],
            'require_lowercase' => ['values' => 'bool', 'default' => true],
            'require_special'   => ['values' => 'bool', 'default' => true],
            'ignore_allowed'    => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'  => ['values' => 'bool', 'default' => false],
            'forbidden'         => ['values' => 'array', 'default' => ['1234', 'password', 'qwerty']],
        ],
    ],
    'email' => [ // 7
        // Global Like if not applicable set to null
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for 'text'/'search'
        'hidden'        => null, // Not Applicable: General HTML attribute
        'lang'          => null, // Not Applicable: Not content-based
        'list'          => null, // Not Recommended: Better suited for 'text' inputs
        'max'           => null, // Not Applicable: Only for numeric/date/time; use 'maxlength'
        'min'           => null, // Not Applicable: Only for numeric/date/time; use 'minlength'
        'minlength'     => null, // made no sense
        'pattern'       => null, // Not Recommended: Browser enforces basic format
        'size'          => null, // Not Recommended: Use CSS for width
        'spellcheck'    => null, // Not Recommended: Should be false due to low utility
        'step'          => null, // Not Applicable: Only for numeric values
        //End Global set to null

        'multiple'      => ['default' => false, 'values' => 'bool'], //one-off

        'autocomplete'  => [
            'default'   => 'email',
            'values'    => ['on', 'off', 'email'],
        ],
        'inputmode'     => ['default' => 'email', 'values' => ['email']],

        'data-char-counter'     => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'default_validation_rules' => [
            // 'value_kind'               => ['values' => 'string', 'default' => 'integer'],
            // 'min' => 444,
            //  'max'           => ['default' => 999, 'values' => 'numeric'],

            // important!!! allowed/Forbidden here pertain to DOMAINS
            'ignore_allowed'    => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'  => ['values' => 'bool', 'default' => false],
            'allowed'           => ['values' => 'array', 'default' => ['good.com', 'heaven.org']],
            'forbidden'         => ['values' => 'array', 'default' => ['xxx.com', 'bad.com']],
        ],
    ],
    'url' => [ // 8
        // Global Like if not applicable set to null
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for 'text'/'search'
        'hidden'        => null, // Not Applicable: General HTML attribute
        'lang'          => null, // Not Applicable: Not content-based
        'list'          => null, // Not Recommended: Better suited for 'text' inputs
        'max'           => null, // Not Applicable: Only for numeric/date/time; use 'maxlength'
        'min'           => null, // Not Applicable: Only for numeric/date/time; use 'minlength'
        'pattern'       => null, // Not Recommended: Browser enforces basic format
        'size'          => null, // Not Recommended: Use CSS for width
        'spellcheck'    => null, // Not Recommended: Should be false due to low utility
        'step'          => null, // Not Applicable: Only for numeric values
        //End Global set to null

        'autocomplete'  => [
            'default'   => 'url',
            'values'    => ['on', 'off', 'url'],
        ],
        'inputmode' => [
            'default'   => 'url',
            'values'    => ['none', 'url'],
        ],
        // 'spellcheck' -- global but we take out
        'placeholder'   => ['default' => '(e.g., https://example.com/)', 'values' => 'string'],

        'default_validation_rules' => [
            // 'value_kind'               => ['values' => 'string', 'default' => 'integer'],
            // 'min' => 444,
            //  'max'           => ['default' => 999, 'values' => 'numeric'],

            // important!!! allowed/Forbidden here pertain to DOMAINS
            'ignore_allowed'    => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'  => ['values' => 'bool', 'default' => false],
            'allowed'           => ['values' => 'array', 'default' => ['rudy.aaa.com', 'rudy.bbb.org', 'uk.co']],
            'forbidden'         => ['values' => 'array', 'default' => ['ass.xxx.com', 'ass.zzz.net', 'iraq.co']],
        ],
    ],
    'tel' => [ // 9
        // Global Like if not applicable set to null
        'pattern'       => null, // is valid but do not use since with use tel library
        'minlength'     => null, // is valid but do not use since with use tel library
        'maxlength'     => null, // is valid but do not use since with use tel library

        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for 'text'/'search'
        'hidden'        => null, // Not Applicable: General HTML attribute
        'lang'          => null, // Not Applicable: Not content-based
        'list'          => null, // Not Recommended: Better suited for 'text' inputs
        'max'           => null, // Not Applicable: Only for numeric/date/time; use 'maxlength'
        'min'           => null, // Not Applicable: Only for numeric/date/time; use 'minlength'
        'size'          => null, // Not Recommended: Use CSS for width
        'spellcheck'    => null, // Not Recommended: Should be false due to low utility
        'step'          => null, // Not Applicable: Only for numeric values
        //End Global set to null

        'title'         => ['default' => 'xxxxPlease enter a valid international phone number (e.g., +15551234567)',
                                        'values' => 'string'], // one-override

        // 'data-mask'       => ['default' => 'phone',  'values' => ['phone']],
        // 'mask'          => ['default' => false, 'values' => 'bool'],
        // 'data-mask'      => ['default' => false, 'values' => 'bool'],

        'autocomplete'  => [
            'default'   => 'tel', // recommended WCAG
            'values'    => [
                'on', 'off',
                'tel', 'tel-country-code', 'tel-national', 'tel-area-code', 'tel-local', 'tel-extension'
            ],
        ],
        'inputmode' => [
            'default'   => 'tel',
            'values'    => ['none', 'text', 'numeric', 'decimal', 'tel'],
        ],
        'placeholder'   => ['default' => "Enter Phone", 'values' => 'string'],

        'data-char-counter'     => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],
        'data-intl-tel-input'   => ['default' => true, 'values' => 'bool'], // one-off

        'default_validation_rules' => [
            // 'required_message'  => ['values' => 'string', 'default' => 'zzPhone is required.'],
            // 'invalid_message'  => ['values' => 'string', 'default' => 'zzPlease enter a valid international phone ' .
            //                                                           'number (e.g., +15551234567). Invalid Error.'],
            // 'invalid_region_message' => ['values' => 'string', 'default' => 'zzPlease select a valid domain.'],
            //'invalid_parse_message'  => ['values' => 'string', 'default' => 'zzPlease enter a valid international ' .
            //                                                       'phone number (e.g., +15551234567). Parse Error.'],
        ],
    ],
    'search' => [ // 10
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        'minlength'     => null, // makes no sense on a search
        'pattern'       => null, // Omit recommended. makes no sense on a search
        //End Global set to null

        'autocomplete'  => [
            'default'   => 'on',
            'values'    => ['on', 'off'],
        ],
        'inputmode' => [
            'default'   => 'search',
            'values'    => ['search', 'text', 'none', 'url', 'decimal', 'numeric', 'tel', 'email'],
        ],
        'placeholder'   => ['default' => 'Search...', 'values' => 'string'],

        // 'incremental' -- Live search, non-standard - do not use i guess
        // 'results'     -- number of results, non-standard - do not use i guess

        'default_validation_rules' => [
        ],
    ],
    'date'   => [ // 11
        // Global Like if not applicable set to null
        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // valid, but CSS is recommended
        'pattern'       => null, // not applicable
        'step'          => null, // omit, does not work,
        'placeholder'   => null, // valid, but i say omit
        //End Global set to null

        'autocomplete'  => [
            'default' => null,
            'values'  => [
                'on', 'off', 'bday'
            ],
        ],

        // Used in Validation
        'max'         => ['default' => null, 'values' => 'string'], // Format: YYYY-MM-DD
        'min'         => ['default' => null, 'values' => 'string'], // Format: YYYY-MM-DD

        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'default_validation_rules' => [
        ],
    ],
    'datetime' => [ // 12
        // Global Like if not applicable set to null
        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // valid, but CSS is recommended
        'pattern'       => null, // not applicable
        'step'          => null, // omit, does not work,
        'placeholder'   => null, // valid, but i say omit
        //End Global set to null

        'autocomplete'  => [
            'default' => null,
            'values'  => [
                'on', 'off'
            ],
        ],

        // Used in Validation
        'max'         => ['default' => null, 'values' => 'string'], // Format: YYYY-MM-DDThh:mm
        'min'         => ['default' => null, 'values' => 'string'], // Format: YYYY-MM-DDThh:mm

        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'default_validation_rules' => [
            // 'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease enter a valid date and time " .
            //                                                            "(YYYY-MM-DDTHH:MM or YYYY-MM-DDTHH:MM:SS).'],
            // 'min_message'       => ['values' => 'string', 'default' => 'zzDate-Time  must not be before ___.'],
            // 'max_message'       => ['values' => 'string', 'default' => 'zzDate-Time  must not be after ___.'],
        ],
    ],
    'month' => [ // 13
        // Global Like if not applicable set to null
        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // valid, but CSS is recommended
        'pattern'       => null, // not applicable
        'step'          => null, // omit, does not work,
        'placeholder'   => null, // valid, but i say omit
        //End Global set to null

        'autocomplete'  => [
            'default' => null,
            'values'  => [
                'on', 'off'
            ],
        ],

        // Used in Validation
        'min'         => ['default' => null, 'values' => 'string'], // Format: YYYY-MM
        'max'         => ['default' => null, 'values' => 'string'], // Format: YYYY-MM

        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'default_validation_rules' => [
        ],
    ],
    'week'   => [ // 14
        // Global Like if not applicable set to null
        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // valid, but CSS is recommended
        'pattern'       => null, // not applicable
        'step'          => null, // omit, does not work,
        'placeholder'   => null, // valid, but i say omit
        //End Global set to null

        'autocomplete'  => [
            'default' => null,
            'values'  => [
                'on', 'off'
            ],
        ],

        // Used in Validation
        'max'         => ['default' => null, 'values' => 'string'], // Format: YYYY-Www
        'min'         => ['default' => null, 'values' => 'string'], // Format: YYYY-Www

        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'default_validation_rules' => [
        ],
    ],
    'time'  => [ // 15
        // Global Like if not applicable set to null
        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // valid, but CSS is recommended
        'pattern'       => null, // not applicable
        'step'          => null, // omit, does not work,
        'placeholder'   => null, // valid, but i say omit
        //End Global set to null

        'autocomplete'  => [
            'default' => null,
            'values'  => [
                'on', 'off'
            ],
        ],

        // Used in Validation
        'max'         => ['default' => null, 'values' => 'string'], // Format: hh:mm
        'min'         => ['default' => null, 'values' => 'string'], // Format: hh:mm

        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'default_validation_rules' => [
        ],
    ],
    'number' => [ // 16
        // Global Like if not applicable set to null
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for 'text'/'search'
        'hidden'        => null, // Not Applicable: General HTML attribute
        'lang'          => null, // Not Applicable: Not content-based
        'maxlength'     => null, // Not Applicable: Limits magnitude ('max'), not character count
        'minlength'     => null, // Not Applicable: Limits character count
        'pattern'       => null, // Not Applicable: Number type already enforces pattern
        'readonly'      => null, // Not Applicable: Cannot be set if intended for user input
        'size'          => null, // Not Recommended: Use CSS for width
        'spellcheck'    => null, // Not Applicable: Numbers are not checked
        //End Global set to null

        'autocomplete'  => [
            'default'   => null,
            'values'    => [
                'on', 'off',
                'postal-code',
                'tel', 'tel-country-code', 'tel-national', 'tel-area-code',
                'tel-local', 'tel-local-prefix', 'tel-local-suffix', 'tel-extension',
                'cc-number', 'cc-exp', 'cc-exp-month', 'cc-exp-year',
                'cc-csc', 'cc-type', 'transaction-currency', 'transaction-amount'
            ],

        ],
        'inputmode' => [
            'default'   => 'numeric',
            'values'    => ['none', 'text', 'numeric', 'decimal', 'tel', 'search', 'email', 'url'],
        ],
        // 'placeholder'   => ['default' => null, 'values' => 'string'], // example placeholder="e.g., 99.95" min="0"

        // 'data-char-counter'     => ['default' => false, 'values' => 'bool'], // fixme not sure id needed
        // 'data-live-validation'  => ['default' => false, 'values' => 'bool'], // fixme not sure id needed

        'default_validation_rules' => [
            'positive_only'            => ['values' => 'bool', 'default' => false],
            'negative_only'            => ['values' => 'bool', 'default' => false],
            'zero_not_allowed'         => ['values' => 'bool', 'default' => false],
            'enforce_step'             => ['values' => 'bool', 'default' => false],

            'ignore_allowed'           => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'         => ['values' => 'bool', 'default' => false],
            'allowed'                  => ['values' => 'array', 'default' => ['111']],
            'forbidden'                => ['values' => 'array', 'default' => ['444', '888']],
        ],
    ],
    'decimal' => [ // 17
        // Global Like if not applicable set to null
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for 'text'/'search'
        'hidden'        => null, // Not Applicable: General HTML attribute
        'lang'          => null, // Not Applicable: Not content-based
        'maxlength'     => null, // Not Applicable: Limits magnitude ('max'), not character count
        'minlength'     => null, // Not Applicable: Limits character count
        'pattern'       => null, // Not Applicable: Number type already enforces pattern
        'readonly'      => null, // Not Applicable: Cannot be set if intended for user input
        'size'          => null, // Not Recommended: Use CSS for width
        'spellcheck'    => null, // Not Applicable: Numbers are not checked
        //End Global set to null

        'autocomplete'  => [
            'default'   => null,
            'values'    => [
                'on', 'off',
                'postal-code',
                'tel', 'tel-country-code', 'tel-national', 'tel-area-code',
                'tel-local', 'tel-local-prefix', 'tel-local-suffix', 'tel-extension',
                'cc-number', 'cc-exp', 'cc-exp-month', 'cc-exp-year',
                'cc-csc', 'cc-type', 'transaction-currency', 'transaction-amount'
            ],

        ],
        'inputmode' => [
            'default'   => 'numeric',
            'values'    => ['none', 'text', 'numeric', 'decimal', 'tel', 'search', 'email', 'url'],
        ],
        // 'placeholder'   => ['default' => null, 'values' => 'string'], // example placeholder="e.g., 99.95" min="0"

        // 'data-char-counter'     => ['default' => false, 'values' => 'bool'], // fixme not sure id needed
        // 'data-live-validation'  => ['default' => false, 'values' => 'bool'], // fixme not sure id needed

        'default_validation_rules' => [
            'positive_only'            => ['values' => 'bool', 'default' => false],
            'negative_only'            => ['values' => 'bool', 'default' => false],
            'zero_not_allowed'         => ['values' => 'bool', 'default' => false],
            'enforce_step'             => ['values' => 'bool', 'default' => false],
        ],
    ],
    'range'   => [ // 18
        // Global Like if not applicable set to null
        'autocomplete'  => null, // Not Recommended: Not useful for visual selection
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for 'text'/'search'
        'hidden'        => null, // Not Applicable: General HTML attribute
        'inputmode'     => null, // Not Applicable: Only for text-based inputs
        'lang'          => null, // Not Applicable: Not content-based
        'maxlength'     => null, // Not Applicable: Limits character count
        'minlength'     => null, // Not Applicable: Limits character count
        'pattern'       => null, // Not Applicable: Only for text-based inputs
        'placeholder'   => null, // Not Applicable: Not relevant for a slider
        'readonly'      => null, // Not Applicable: Must be writable to function
        'size'          => null, // Not Applicable: Width set by CSS
        'spellcheck'    => null, // Not Applicable: Not text content
        'value'         => null, // Not Recommended: Initial value only; rely on scripting for data
        //End Global set to null

        // Used in Validation
        // 'step'          => ['default' => 1, 'values' => ['int', 'float']], // Note: default value is 1

        // 'step'          => ['values' => 'int',     'default' => null],

        'data-show-value'       => ['default' => false, 'values' => 'bool'], // fixme doc what is is used for
        // 'data-char-counter'     -- not applicable
        // 'data-live-validation'  -- not applicable

        'default_validation_rules' => [
            'enforce_step'             => ['values' => 'bool', 'default' => true],
        ],
    ],
    'color' => [ // 19
        // Global Like if not applicable set to null
        'autocomplete'  => null, // Not Applicable: Only for data input
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for specific text inputs
        'hidden'        => null, // Not Applicable: General HTML attribute
        'inputmode'     => null, // Not Applicable: Only for text inputs
        'lang'          => null, // Not Applicable: Not content-based
        'list'          => null, // Not Applicable: Rarely supported by browsers for this type
        'max'           => null, // Not Applicable: Only for numeric inputs
        'maxlength'     => null, // Not Applicable: Fixed length value
        'min'           => null, // Not Applicable: Only for numeric inputs
        'minlength'     => null, // Not Applicable: Fixed length value
        'pattern'       => null, // Not Applicable: Format is strictly enforced
        'placeholder'   => null, // Not Applicable: Only for text inputs
        'readonly'      => null, // Not Applicable: Use 'disabled' instead
        'required'      => null, // Not Recommended: Often redundant (always submits #000000)
        'size'          => null, // Not Applicable: Only for text inputs
        'spellcheck'    => null, // Not Applicable: Not user-editable text
        'step'          => null, // Not Applicable: Only for numeric inputs
        //End Global set to null


        'value'       => ['default' => '#000000', 'values' => 'string'], // Format: #rrggbb

        'default_validation_rules' => [
            'ignore_allowed'           => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'         => ['values' => 'bool', 'default' => true],
            'allowed'                  => ['values' => 'array', 'default' => []],
            'forbidden'                => ['values' => 'array', 'default' => ['#000000']],
        ],
    ],
    'checkbox_group' => [ // 21 ✅ New entry for radio_group
        // Global Like if not applicable set to null
        'autocomplete'  => null, // Not Applicable: Only for data input
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for specific text inputs
        'hidden'        => null, // Not Applicable: General HTML attribute
        'inputmode'     => null, // Not Applicable: Only for text inputs
        'lang'          => null, // Not Applicable: Not content-based
        'list'          => null, // Not Applicable: Used for text input suggestions
        'max'           => null, // Not Applicable: Only for numeric inputs
        'maxlength'     => null, // Not Applicable: Only for text inputs
        'min'           => null, // Not Applicable: Only for numeric inputs
        'minlength'     => null, // Not Applicable: Only for text inputs
        'pattern'       => null, // Not Applicable: Only for text inputs
        'placeholder'   => null, // Not Applicable: Only for text inputs
        'readonly'      => null, // Not Applicable: Use 'disabled' instead
        'size'          => null, // Not Applicable: Only for text inputs
        'spellcheck'    => null, // Not Applicable: Not user-editable text
        'step'          => null, // Not Applicable: Only for numeric inputs
        //End Global set to null

        'checked'     => ['default' => false, 'values' => 'bool'],

        // It will implicitly inherit global HTML attributes like 'required', 'id', 'class', etc.
        'default_validation_rules' => [
        ],
    ],
    'checkbox' => [ // 20
        // Global Like if not applicable set to null
        'autocomplete'  => null, // Not Applicable: Only for data input
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for specific text inputs
        'hidden'        => null, // Not Applicable: General HTML attribute
        'inputmode'     => null, // Not Applicable: Only for text inputs
        'lang'          => null, // Not Applicable: Not content-based
        'list'          => null, // Not Applicable: Used for text input suggestions
        'max'           => null, // Not Applicable: Only for numeric inputs
        'maxlength'     => null, // Not Applicable: Only for text inputs
        'min'           => null, // Not Applicable: Only for numeric inputs
        'minlength'     => null, // Not Applicable: Only for text inputs
        'pattern'       => null, // Not Applicable: Only for text inputs
        'placeholder'   => null, // Not Applicable: Only for text inputs
        'readonly'      => null, // Not Applicable: Use 'disabled' instead
        'size'          => null, // Not Applicable: Only for text inputs
        'spellcheck'    => null, // Not Applicable: Not user-editable text
        'step'          => null, // Not Applicable: Only for numeric inputs
        //End Global set to null

        // 'value'       => ['default' => '1', 'values' => 'string'],

        'checked'     => ['default' => false, 'values' => 'bool'],
        //'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'default_validation_rules' => [
            // 'ignore_allowed'           => ['values' => 'bool', 'default' => true],
            // 'ignore_forbidden'         => ['values' => 'bool', 'default' => true],
            // 'allowed'                  => ['values' => 'array', 'default' => []],
            // 'forbidden'                => ['values' => 'array', 'default' => ['#000000']],
        ],
    ],
    'radio_group' => [ // 21 ✅ New entry for radio_group
        // Global Like if not applicable set to null
        'autocomplete'  => null, // Not Applicable: Only for data input
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for specific text inputs
        'hidden'        => null, // Not Applicable: General HTML attribute
        'inputmode'     => null, // Not Applicable: Only for text inputs
        'lang'          => null, // Not Applicable: Not content-based
        'list'          => null, // Not Applicable: Used for text input suggestions
        'max'           => null, // Not Applicable: Only for numeric inputs
        'maxlength'     => null, // Not Applicable: Only for text inputs
        'min'           => null, // Not Applicable: Only for numeric inputs
        'minlength'     => null, // Not Applicable: Only for text inputs
        'pattern'       => null, // Not Applicable: Only for text inputs
        'placeholder'   => null, // Not Applicable: Only for text inputs
        'readonly'      => null, // Not Applicable: Use 'disabled' instead
        'size'          => null, // Not Applicable: Only for text inputs
        'spellcheck'    => null, // Not Applicable: Not user-editable text
        'step'          => null, // Not Applicable: Only for numeric inputs
        //End Global set to null

        // It will implicitly inherit global HTML attributes like 'required', 'id', 'class', etc.
        'default_validation_rules' => [
        ],
    ],
    'radio' => [ // 22
        // Global Like if not applicable set to null
        'autocomplete'  => null, // Not Applicable: Only for data input
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for specific text inputs
        'hidden'        => null, // Not Applicable: General HTML attribute
        'inputmode'     => null, // Not Applicable: Only for text inputs
        'lang'          => null, // Not Applicable: Not content-based
        'list'          => null, // Not Applicable: Used for text input suggestions
        'max'           => null, // Not Applicable: Only for numeric inputs
        'maxlength'     => null, // Not Applicable: Only for text inputs
        'min'           => null, // Not Applicable: Only for numeric inputs
        'minlength'     => null, // Not Applicable: Only for text inputs
        'pattern'       => null, // Not Applicable: Only for text inputs
        'placeholder'   => null, // Not Applicable: Only for text inputs
        'readonly'      => null, // Not Applicable: Use 'disabled' instead
        'size'          => null, // Not Applicable: Only for text inputs
        'spellcheck'    => null, // Not Applicable: Not user-editable text
        'step'          => null, // Not Applicable: Only for numeric inputs
        //End Global set to null

        'name'        => ['default' => null, 'values' => 'string'], // Grouping is essential
        'value'       => ['default' => null, 'values' => 'string'], // Must be unique within a group
        'form'        => ['default' => null, 'values' => 'string'],

        'readonly'      => null, // not applicable

        'disabled'    => ['default' => false, 'values' => 'bool'],
        'autofocus'   => ['default' => false, 'values' => 'bool'],
        'required'    => ['default' => false, 'values' => 'bool'],
        'checked'     => ['default' => false, 'values' => 'bool'],
    ],
    'select' => [ // 23
        // Global Like if not applicable set to null
        'autocomplete'  => null, // Not Applicable: Only for <input>
        'dir'           => null, // Not Applicable: Not content-based
        'dirname'       => null, // Not Applicable: Only for specific <input> types
        'hidden'        => null, // Not Applicable: General HTML attribute
        'inputmode'     => null, // Not Applicable: Only for <input>
        'lang'          => null, // Not Applicable: Not content-based
        'max'           => null, // Not Applicable: Only for numeric <input>
        'maxlength'     => null, // Not Applicable: Only for text <input>
        'min'           => null, // Not Applicable: Only for numeric <input>
        'minlength'     => null, // Not Applicable: Only for text <input>
        'pattern'       => null, // Not Applicable: Only for <input>
        'placeholder'   => null, // Not Applicable: Only for text <input>
        'spellcheck'    => null, // Not Applicable: Not user-editable text
        'step'          => null, // Not Applicable: Only for numeric <input>
        'value'         => null, // Not Applicable: Value is set on <option>
        //End Global set to null - 15 + 3 = 18

        'multiple'      => ['default' => false, 'values' => 'bool'],
        'size'          => ['default' => null,  'values' => 'int'], // Number of visible options

        'default_validation_rules' => [
            'choices'    => ['default' => 'array', 'values' => []],
            'choicesmoo'    => ['default' => 'array', 'values' => []],
                        // 'allowed'                  => ['values' => 'array', 'default' => ['aaaa', 'bbbb']],

        ],
    ],
    'file' => [ // 24
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        'step'          => null, // not applicable
        'autocomplete'  => null, // not applicable
        'inputmode'     => null, // not applicable
        'step'          => null, // not applicable
        'readonly'      => null, // not applicable

        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // not applicable - use CSS is recommended
        'pattern'       => null, // not applicable
        //End Global set to null


        'name'      => ['default' => null, 'values' => 'string'],
        'form'      => ['default' => null, 'values' => 'string'],
        'disabled'  => ['default' => false, 'values' => 'bool'],
        'autofocus' => ['default' => false, 'values' => 'bool'],
        'required'  => ['default' => false, 'values' => 'bool'],
        'multiple'  => ['default' => false, 'values' => 'bool'],
        'accept'    => ['default' => null, 'values' => 'string'], // e.g., 'image/*', '.pdf'

        'default_validation_rules' => [ // max_size //dddd
            'max_size'      => ['default' => null, 'values' => 'int'],
            'mime_types'    => ['values' => 'array_of_mime_types', 'default' => []], // ✅ Changed from 'array'
        ],

        // notes-: Took it out....logic got to complicated.
        // '_mime_type_whitelist' => [
        //     'image' => ['jpeg', 'jpg', 'png', 'gif', 'webp', 'svg+xml', 'bmp', 'tiff', 'x-icon', 'avif'],
        //     'audio' => ['mpeg', 'mp3', 'ogg', 'wav', 'webm', 'aac', 'flac', 'midi'],
        // ],
    ],
    'extratest' => [ // 25
        'default_validation_rules' => [
            'type' => ['default' => null, 'values' => 'string'],
            'forbidden'          => ['default' => [333], 'values' => []],
        ],
    ],
    'extratest2' => [ // 26
        'default_validation_rules' => [
            'type' => ['default' => null, 'values' => 'string'],
            'forbidden'          => ['default' => [333, 44], 'values' => []],
        ],
    ],
];
// 668 // 491 606 616 651 724 937 965 857
