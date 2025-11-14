<?php

/**
 * Form field attribute schema for all field types.
 *
 * @return array<string, array<string, mixed>>
 *
 */

declare(strict_types=1);

/*
    9 Global  +1 msg
    text based    - text, password, email, url, tel, search
    date based    - date, month, week, time, datetime-local
    number based  - number, range
    specialized   - color, hidden
    specialized   - color, hidden
    Buttons    submit, reset, button, image

    // text based    -- 4, 3, 6, 4, 5 msg, 2
    // number based  -- 4, 3, 6, 5, 6+ msg, 2???
    // date based    -- 4, 3, 2, 3, 4 msg, 1
    4
    // toggle       -- checkbox, radio
    3
    6
    4
    5
        // 'form'          => null, // Assoc. input with a specific form, allowing it to be outside the <form> tags
        // 'list'          => null, // Links the input to a <datalist> element for pre-defined suggestions.
*/


return [
    'global' => [
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
        'pattern'       => ['default' => null, 'values' => 'string'],

        'dirname'       => ['values' => 'string',  'default' => null],
        'size'          => ['values' => 'int',     'default' => null],

        // Used in Validation
        'required'      => ['values' => 'bool',    'default' => false],
        'min'           => ['values' => 'numeric', 'default' => null],
        'max'           => ['values' => 'numeric', 'default' => null],
        'step'          => ['values' => 'int',     'default' => null],
        'maxlength'     => ['values' => 'int',     'default' => null],
        'minlength'     => ['values' => 'int',     'default' => null],
    ],
    'display' => [
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
        'autocomplete'          => null, // not applicable
        'inputmode'          => null, // not applicable

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
    'hidden' => [
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        //End Global set to null
    ],
    'text' => [
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        //End Global set to null

        'autocomplete'  => [
            'default' => null, // if setting 'on' it will be included every time, and we do not want that, thus null
            'values'  => [
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
                                                ['text', 'numeric', 'decimal', 'email', 'tel', 'url', 'search']],

        // 'required_message'  => ['default' => null, 'values' => 'string'],
        // 'maxlength_message' => ['default' => null, 'values' => 'string'],
        // 'minlength_message' => ['default' => null, 'values' => 'string'],
        // 'pattern_message'   => ['default' => null, 'values' => 'string'],
        // 'invalid_message'   => ['default' => null, 'values' => 'string'],

        'data-char-counter'     => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'val_fields' => [
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

            'required_message'         => ['values' => 'string', 'default' => null],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease ???.'],
            'minlength_message' => ['values' => 'string', 'default' => 'zzText must be at least ___ characters.'],
            'maxlength_message' => ['values' => 'string', 'default' => 'zzText must not exceed ___ characters.'],
            'pattern_message'   => ['values' => 'string', 'default' => 'zzText does not match the required pattern.'],
            'allowed_message'   => ['values' => 'string', 'default' => 'zzPlease select a valid word.'],
            'forbidden_message' => ['values' => 'string', 'default' => 'zzThis word is not allowed.'],
        ],
    ],
    'textarea' => [
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

        // 'required_message'  => ['default' => null, 'values' => 'string'],
        // 'maxlength_message' => ['default' => null, 'values' => 'string'],
        // 'minlength_message' => ['default' => null, 'values' => 'string'],
        // 'pattern_message'   => ['default' => null, 'values' => 'string'],
        // 'invalid_message'   => ['default' => null, 'values' => 'string'],

        'data-char-counter'     => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'val_fields' => [
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

            'required_message'         => ['values' => 'string', 'default' => null],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease ???.'],
            'minlength_message' => ['values' => 'string', 'default' => 'zzText must be at least ___ characters.'],
            'maxlength_message' => ['values' => 'string', 'default' => 'zzText must not exceed ___ characters.'],
            'pattern_message'   => ['values' => 'string', 'default' => 'zzText does not match the required pattern.'],
            'allowed_message'   => ['values' => 'string', 'default' => 'zzPlease select a valid word.'],
            'forbidden_message' => ['values' => 'string', 'default' => 'zzThis word is not allowed.'],
        ],
    ],
    'password' => [
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        'list'          => null, // not applicable
        'spellcheck'    => null, // global but we take out
        'placeholder'   => null, // just do not use
        //End Global set to null

        'autocomplete'  => [
            'default' => 'one-time-code',
            'values'  => ['current-password', 'new-password', 'one-time-code'],
        ],
        'inputmode'     => ['default' => 'text', 'values' => ['text', 'numeric']],

        // 'required_message'  => ['default' => null, 'values' => 'string'],
        // 'maxlength_message' => ['default' => null, 'values' => 'string'],
        // 'minlength_message' => ['default' => null, 'values' => 'string'],
        // 'pattern_message'   => ['default' => null, 'values' => 'string'],
        // 'invalid_message'   => ['default' => null, 'values' => 'string'],

        'data-char-counter'     => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'val_fields' => [
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

            'required_message'  => ['values' => 'string', 'default' => 'zzPassword is required.'],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease enter a valid password address.'],
            'minlength_message' => ['values' => 'string', 'default' => 'zzPassword must be at least ___ characters.'],
            'maxlength_message' => ['values' => 'string', 'default' => 'zzPassword must not exceed ___ characters.'],
            'pattern_message'   => [
                'values'  => 'string',
                'default' => 'zzPassword does not match the required pattern.',
            ],
            'forbidden_message' => ['values' => 'string', 'default' => 'zzThis password is not allowed.'],
            'require_digit_message'     => [
                'values' => 'string',
                'default' => 'zzPassword must contain at least one digit.'
            ],
            'require_uppercase_message' => [
                'values' => 'string',
                'default' => 'zzPassword must contain at least one uppercase letter.'
            ],
            'require_lowercase_message' => [
                'values' => 'string',
                'default' => 'zzPassword must contain at least one lowercase letter.'
            ],
            'require_special_message'   => [
                'values' => 'string',
                'default' => 'zzPassword must contain at least one special character.'
            ],
        ],
    ],
    'email' => [
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        'spellcheck'    => null, // global but we take out
        //End Global set to null

        'multiple'      => ['default' => false, 'values' => 'bool'], //one-off

        'autocomplete'  => [
            'default'   => 'email',
            'values'    => ['on', 'off', 'email'],
        ],
        'inputmode'     => ['default' => 'email', 'values' => ['email']],

        'data-char-counter'     => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'val_fields' => [
            // 'value_kind'               => ['values' => 'string', 'default' => 'integer'],
            // 'min' => 444,
            //  'max'           => ['default' => 999, 'values' => 'numeric'],

            // important!!! allowed/Forbidden here pertain to DOMAINS
            'ignore_allowed'    => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'  => ['values' => 'bool', 'default' => false],
            'allowed'           => ['values' => 'array', 'default' => ['good.com', 'heaven.org']],
            'forbidden'         => ['values' => 'array', 'default' => ['xxx.com', 'bad.com']],

            'required_message'  => ['values' => 'string', 'default' => 'zzEmail is required.'],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease enter a valid email address.'],
            'minlength_message' => ['values' => 'string', 'default' => 'zzEmail must be at least ___ characters.'],
            'maxlength_message' => ['values' => 'string', 'default' => 'zzEmail must not exceed ___ characters.'],
            'pattern_message'   => ['values' => 'string', 'default' => 'zzEmail does not match the required pattern.'],
            'allowed_message'   => ['values' => 'string', 'default' => 'zzPlease select a valid domain.'],
            'forbidden_message' => ['values' => 'string', 'default' => 'zzThis domain is not allowed.'],
        ],
    ],
    'url' => [
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        'spellcheck'    => null, // global but we take out
        'dirname'       => null, // omit
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

        'val_fields' => [
            // 'value_kind'               => ['values' => 'string', 'default' => 'integer'],
            // 'min' => 444,
            //  'max'           => ['default' => 999, 'values' => 'numeric'],

            // important!!! allowed/Forbidden here pertain to DOMAINS
            'ignore_allowed'    => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'  => ['values' => 'bool', 'default' => false],
            'allowed'           => ['values' => 'array', 'default' => ['rudy.aaa.com', 'rudy.bbb.org', 'uk.co']],
            'forbidden'         => ['values' => 'array', 'default' => ['ass.xxx.com', 'ass.zzz.net', 'iraq.co']],

            'required_message'  => ['values' => 'string', 'default' => 'zzUrl is required.'],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease enter a valid url address.'],
            'minlength_message' => ['values' => 'string', 'default' => 'zzzzzUrl must be at least ___ characters.'],
            // 'minlength_message' => ['values' => 'string', 'default' => null],
            'maxlength_message' => ['values' => 'string', 'default' => 'zzUrl must not exceed ___ characters.'],
            'pattern_message'   => ['values' => 'string', 'default' => 'zzUrl does not match the required pattern.'],
            'allowed_message'   => ['values' => 'string', 'default' => 'zzPlease select a valid domain.'],
            'forbidden_message' => ['values' => 'string', 'default' => 'zzThis domain is not allowed.'],
        ],
    ],
    'tel' => [
        // Global Like if not applicable set to null
        'min'           => null, // not applicable
        'max'           => null, // not applicable
        'step'          => null, // not applicable
        'spellcheck'    => null, // global but we take out
        'dirname'       => null, // omit
        'pattern'       => null, // is valid but do not use since with use tel library
        'minlength'     => null, // makes no sense
        'maxlength'     => null, // makes no sense
        //End Global set to null

        'title'         => ['default' => 'Please enter a valid international phone number (e.g., +15551234567)',
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

        'val_fields' => [
            'required_message'  => ['values' => 'string', 'default' => 'zzPhone is required.'],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease enter a valid international phone ' .
                                                                       'number (e.g., +15551234567). Invalid Error.'],
            'invalid_region_message' => ['values' => 'string', 'default' => 'zzPlease select a valid domain.'],
            'invalid_parse_message'  => ['values' => 'string', 'default' => 'zzPlease enter a valid international ' .
                                                                    'phone number (e.g., +15551234567). Parse Error.'],
        ],
    ],
    'search' => [
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

        'val_fields' => [
            'required_message'  => ['values' => 'string', 'default' => 'zzUrl is required.'],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzSearch term contains invalid characters.'],
            'maxlength_message' => ['values' => 'string', 'default' => 'zzSearch must not exceed ___ characters.'],
            'allowed_message'   => ['values' => 'string', 'default' => 'zzSearch term contains invalid characters.'],
        ],
    ],
    'date'   => [
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

        'val_fields' => [
            'required_message'  => ['values' => 'string', 'default' => 'zzDate is required.'],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease enter a valid date.'],
            'min_message'       => ['values' => 'string', 'default' => 'zzDate must not be before ___.'],
            'max_message'       => ['values' => 'string', 'default' => 'zzDate must not be after ___.'],
        ],
    ],
    'datetime' => [
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

        'val_fields' => [
            'required_message'  => ['values' => 'string', 'default' => 'zzDate-Time is required.'],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease enter a valid date and time " .
                                                                       "(YYYY-MM-DDTHH:MM or YYYY-MM-DDTHH:MM:SS).'],
            'min_message'       => ['values' => 'string', 'default' => 'zzDate-Time  must not be before ___.'],
            'max_message'       => ['values' => 'string', 'default' => 'zzDate-Time  must not be after ___.'],
        ],
    ],
    'month' => [
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

        'val_fields' => [
            'required_message'  => ['values' => 'string', 'default' => 'zzYear-Month is required.'],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease enter a valid year-month.'],
            'min_message'       => ['values' => 'string', 'default' => 'zzYear-Month must not be before ___.'],
            'max_message'       => ['values' => 'string', 'default' => 'zzYear-Month must not be after ___.'],
        ],
    ],
    'week'   => [
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

        'val_fields' => [
            'required_message'  => ['values' => 'string', 'default' => 'zzYear-Week is required.'],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease enter a valid year-week.'],
            'min_message'       => ['values' => 'string', 'default' => 'zzYear-Week must not be before ___.'],
            'max_message'       => ['values' => 'string', 'default' => 'zzYear-Week must not be after ___.'],
        ],
    ],
    'time'  => [
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

        'val_fields' => [
            'required_message'  => ['values' => 'string', 'default' => 'zzTime is required.'],
            'invalid_message'   => ['values' => 'string', 'default' => 'zzPlease enter a valid time.'],
            'min_message'       => ['values' => 'string', 'default' => 'zzTime must not be before ___.'],
            'max_message'       => ['values' => 'string', 'default' => 'zzTime must not be after ___.'],
        ],
    ],
    'number' => [
        // Global Like if not applicable set to null
        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // valid, but CSS is recommended
        'pattern'       => null, // not applicable
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

        'val_fields' => [
            'positive_only'            => ['values' => 'bool', 'default' => false],
            'negative_only'            => ['values' => 'bool', 'default' => false],
            'zero_not_allowed'         => ['values' => 'bool', 'default' => false],
            'enforce_step'             => ['values' => 'bool', 'default' => false],

            'ignore_allowed'           => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'         => ['values' => 'bool', 'default' => true],
            'allowed'                  => ['values' => 'array', 'default' => []],
            'forbidden'                => ['values' => 'array', 'default' => [11, 33]],

            'required_message'         => ['values' => 'string', 'default' => 'zzNumber is required.'],
            'invalid_message'     => ['values' => 'string', 'default' =>'zzPlease enter a whole number (no decimals).'],
            'min_message'              => ['values' => 'string', 'default' => 'zzValue must be at least ___.'],
            'max_message'              => ['values' => 'string', 'default' => 'zzValue must not exceed ___.'],
            'positive_only_message'    => ['values' => 'string', 'default' => 'zzOnly positive numbers are allowed.'],
            'negative_only_message'    => ['values' => 'string', 'default' => 'zzOnly negative numbers are allowed.'],
            'zero_not_allowed_message' => ['values' => 'string', 'default' => 'zzZero is not allowed.'],
            'allowed_message'         => ['values' => 'string', 'default' => 'zzPlease select a valid allowed number.'],
            'forbidden_message'        => ['values' => 'string', 'default' => 'zzThis number is not allowed.'],
            'enforce_step_message' => ['values' => 'string', 'default' => 'zzNumber must be a multiple of ___.'],
            //'leading_zeros_message'    => ['values' => 'string', 'default' => null],
        ],
    ],
    'decimal' => [
        // Global Like if not applicable set to null
        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // valid, but CSS is recommended
        'pattern'       => null, // not applicable
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

        'val_fields' => [
            'positive_only'            => ['values' => 'bool', 'default' => false],
            'negative_only'            => ['values' => 'bool', 'default' => false],
            'zero_not_allowed'         => ['values' => 'bool', 'default' => false],
            'enforce_step'             => ['values' => 'bool', 'default' => false],

            'ignore_allowed'           => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'         => ['values' => 'bool', 'default' => true],
            'allowed'                  => ['values' => 'array', 'default' => []],
            'forbidden'                => ['values' => 'array', 'default' => [2.22, 2]],

            'required_message'         => ['values' => 'string', 'default' => 'zzNumber is required.'],
            'invalid_message'          => ['values' => 'string', 'default' => 'zzPlease enter a decimal number.'],
            'min_message'              => ['values' => 'string', 'default' => 'zzValue must be at least ___.'],
            'max_message'              => ['values' => 'string', 'default' => 'zzValue must not exceed ___.'],
            'positive_only_message'    => ['values' => 'string', 'default' => 'zzOnly positive numbers are allowed.'],
            'negative_only_message'    => ['values' => 'string', 'default' => 'zzOnly negative numbers are allowed.'],
            'zero_not_allowed_message' => ['values' => 'string', 'default' => 'zzZero is not allowed.'],
            'allowed_message'         => ['values' => 'string', 'default' => 'zzPlease select a valid allowed number.'],
            'forbidden_message'        => ['values' => 'string', 'default' => 'zzThis number is not allowed.'],
            'enforce_step_message'     => ['values' => 'string', 'default' => 'zzNumber must be a multiple of ___.'],
        ],
    ],
    'range'   => [
        // Global Like if not applicable set to null
        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // valid, but CSS is recommended
        'pattern'       => null, // not applicable
        'readonly'      => null, // not applicable
        'autocomplete'  => null, // Ignore it
        'inputmode'     => null, // not applicable
        'placeholder'   => null, // not applicable
        'required_message'   => null, // not applicable
        //End Global set to null

        // Used in Validation
        // 'step'          => ['default' => 1, 'values' => ['int', 'float']], // Note: default value is 1

        // 'step'          => ['values' => 'int',     'default' => null],

        'data-show-value'       => ['default' => false, 'values' => 'bool'], // fixme doc what is is used for
        // 'data-char-counter'     -- not applicable
        // 'data-live-validation'  -- not applicable

        'val_fields' => [
            'enforce_step'             => ['values' => 'bool', 'default' => true],

            'invalid_message'          => ['values' => 'string', 'default' => 'zzThis value must be a number.'],
            'min_message'              => ['values' => 'string', 'default' => 'zzValue must be at least ___.'],
            'max_message'              => ['values' => 'string', 'default' => 'zzValue must not exceed ___.'],
            'enforce_step_message'     => ['values' => 'string', 'default' => 'zzNumber must be a multiple of ___.'],
        ],
    ],

    'color' => [
        // Global Like if not applicable set to null
        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // valid, but CSS is recommended
        'pattern'       => null, // not applicable
        'readonly'      => null, // not applicable
        'autocomplete'  => null, // Ignore it
        'inputmode'     => null, // not applicable
        'placeholder'   => null, // not applicable
        'required'      => null, // just do not use, dfts to black
        'required_message' => null, // not applicable
        //End Global set to null


        'value'       => ['default' => '#000000', 'values' => 'string'], // Format: #rrggbb

        'val_fields' => [
            'ignore_allowed'           => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'         => ['values' => 'bool', 'default' => true],
            'allowed'                  => ['values' => 'array', 'default' => []],
            'forbidden'                => ['values' => 'array', 'default' => ['#000000']],

            'invalid_message' => ['values' => 'string', 'default' => 'zzPlease select a valid color (e.g.,#FF5733).'],
            'allowed_message'  => ['values' => 'string', 'default' => 'zzPlease select a color from the allowed list.'],
            'forbidden_message' => ['values' => 'string', 'default' => 'zzThis color is not allowed.'],
        ],
    ],

    'checkbox' => [
        // Global Like if not applicable set to null
        'maxlength'     => null, // not applicable
        'minlength'     => null, // not applicable
        'spellcheck'    => null, // not applicable
        'dirname'       => null, // not applicable
        'size'          => null, // valid, but CSS is recommended
        'pattern'       => null, // not applicable
        'readonly'      => null, // not applicable
        'autocomplete'  => null, // Ignore it
        'inputmode'     => null, // not applicable
        'placeholder'   => null, // not applicable
        //End Global set to null

        // 'value'       => ['default' => '1', 'values' => 'string'],

        'checked'     => ['default' => false, 'values' => 'bool'],
        'data-live-validation'  => ['default' => false, 'values' => 'bool'],

        'val_fields' => [
            // 'ignore_allowed'           => ['values' => 'bool', 'default' => true],
            // 'ignore_forbidden'         => ['values' => 'bool', 'default' => true],
            // 'allowed'                  => ['values' => 'array', 'default' => []],
            // 'forbidden'                => ['values' => 'array', 'default' => ['#000000']],

            // 'invalid_message' => ['values' => 'string', 'default' => 'zzPlease select a valid color (e.g.,#FF5733).'],
            // 'allowed_message'  => ['values' => 'string', 'default' => 'zzPlease select a color from the allowed list.'],
            // 'forbidden_message' => ['values' => 'string', 'default' => 'zzThis color is not allowed.'],

            'required_message'         => ['values' => 'string', 'default' => 'zzThis field is required.'],
            'invalid_message'     => ['values' => 'string', 'default' =>'zzPlease enter a whole number (no decimals).'],


        ],
    ],

    'radio' => [
        'name'        => ['default' => null, 'values' => 'string'], // Grouping is essential
        'value'       => ['default' => null, 'values' => 'string'], // Must be unique within a group
        'form'        => ['default' => null, 'values' => 'string'],

        'readonly'      => null, // not applicable

        'disabled'    => ['default' => false, 'values' => 'bool'],
        'autofocus'   => ['default' => false, 'values' => 'bool'],
        'required'    => ['default' => false, 'values' => 'bool'],
        'checked'     => ['default' => false, 'values' => 'bool'],
        // 'required_message' => ['default' => null, 'values' => 'string'],
    ],


    'select' => [
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
        'pattern'       => null, // not applicable
        'placeholder'   => null, // not applicable
        'value'         => null, // not applicable
        //End Global set to null

        'multiple'      => ['default' => false, 'values' => 'bool'],
        'size'          => ['default' => null,  'values' => 'int'], // Number of visible options

        'val_fields' => [
            'ignore_allowed'           => ['values' => 'bool', 'default' => true],
            'ignore_forbidden'         => ['values' => 'bool', 'default' => false],
            'allowed'                  => ['values' => 'array', 'default' => []],
            'forbidden'                => ['values' => 'array', 'default' => []],

            'required_message'         => ['values' => 'string', 'default' => 'zzThis field is required.'],
            'invalid_message'          => ['values' => 'string', 'default' => 'zzPlease select a valid option.'],
            'allowed_message'          => ['values' => 'string', 'default' => 'zzThe selected option is not allowed.'],
            'forbidden_message'        => ['values' => 'string', 'default' => 'zzThe selected option is forbidden.'],
        ],
    ],


    'file' => [
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
        // 'required_message' => ['default' => null, 'values' => 'string'],
    ],

    'extratest' => [
        'val_fields' => [
            'type' => ['default' => null, 'values' => 'string'],
            'forbidden'          => ['default' => [333], 'values' => []],
            'forbidden_message'  => ['default' => null, 'values' => 'string'],
        ],
    ],
    'extratest2' => [
        'val_fields' => [
            'type' => ['default' => null, 'values' => 'string'],
            'forbidden'          => ['default' => [333, 44], 'values' => []],
            'forbidden_message'  => ['default' => null, 'values' => 'string'],
        ],
    ],


];

        // hidden
        // button
        // reset
        // submit
        // file
        // image

        // <form>
        // <button>
        // <select>
        // <option>
        // <optgroup>
        // <label>
        // <fieldset>
        // <legend>
        // <output>
        // <datalist>
        // <progress>
        // <meter>
// 668 // 491 606 616 651 724
