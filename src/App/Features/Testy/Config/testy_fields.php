<?php

/**
 * Generated File - Date: 20251109_203449
 * Field definitions for the Testy entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
    'store_id' => [
        'label' => 'testy.store_id',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.store_id.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'user_id' => [
        'label' => 'testy.user_id',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.user_id.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'status' => [
        'label' => 'testy.status',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.status.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'slug' => [
        'label' => 'testy.slug',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.slug.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'title' => [
        'label' => 'testy.title',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'textarea',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.title.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'content' => [
        'label' => 'testy.content',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'textarea',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.content.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'generic_text' => [
        'label' => 'testy.generic_text',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_text.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'image_count' => [
        'label' => 'testy.image_count',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.image_count.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'cover_image_id' => [
        'label' => 'testy.cover_image_id',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.cover_image_id.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'date_of_birth' => [
        'label' => 'testy.date_of_birth',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'date',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.date_of_birth.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'generic_date' => [
        'label' => 'testy.generic_date',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'date',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_date.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'generic_month' => [
        'label' => 'testy.generic_month',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_month.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'generic_week' => [
        'label' => 'testy.generic_week',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_week.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'generic_time' => [
        'label' => 'testy.generic_time',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'time',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_time.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'generic_datetime' => [
        'label' => 'testy.generic_datetime',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'datetime-local',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_datetime.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'telephone' => [
        'label' => 'testy.telephone',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.telephone.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'gender_id' => [
        'label' => 'testy.gender_id',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.gender_id.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'gender_other' => [
        'label' => 'testy.gender_other',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.gender_other.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'is_verified' => [
        'label' => 'testy.is_verified',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'checkbox',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.is_verified.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'interest_soccer_ind' => [
        'label' => 'testy.interest_soccer_ind',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'checkbox',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.interest_soccer_ind.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'interest_baseball_ind' => [
        'label' => 'testy.interest_baseball_ind',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'checkbox',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.interest_baseball_ind.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'interest_football_ind' => [
        'label' => 'testy.interest_football_ind',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'checkbox',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.interest_football_ind.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'interest_hockey_ind' => [
        'label' => 'testy.interest_hockey_ind',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'checkbox',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.interest_hockey_ind.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'primary_email' => [
        'label' => 'testy.primary_email',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'email',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.primary_email.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'secret_code_hash' => [
        'label' => 'testy.secret_code_hash',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.secret_code_hash.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'balance' => [
        'label' => 'testy.balance',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.balance.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'generic_decimal' => [
        'label' => 'testy.generic_decimal',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_decimal.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'volume_level' => [
        'label' => 'testy.volume_level',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.volume_level.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'start_rating' => [
        'label' => 'testy.start_rating',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.start_rating.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'generic_number' => [
        'label' => 'testy.generic_number',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_number.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'generic_num' => [
        'label' => 'testy.generic_num',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'number',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_num.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'generic_color' => [
        'label' => 'testy.generic_color',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'color',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.generic_color.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'wake_up_time' => [
        'label' => 'testy.wake_up_time',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'time',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.wake_up_time.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'favorite_week_day' => [
        'label' => 'testy.favorite_week_day',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'text',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.favorite_week_day.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'online_address' => [
        'label' => 'testy.online_address',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'url',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.online_address.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'profile_picture' => [
        'label' => 'testy.profile_picture',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'textarea',
            'required'      => false, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.profile_picture.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'created_at' => [
        'label' => 'testy.created_at',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'datetime-local',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.created_at.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
    'updated_at' => [
        'label' => 'testy.updated_at',
        'list' => [
            'sortable' => false,
            'formatter' => null,
        ],
        'form' => [
            'type'          => 'datetime-local',
            'required'      => true, // Required if not nullable
            'attributes'    => [
                'placeholder' => 'testy.updated_at.placeholder',
            ],
        ],
        'formatters' => [
        ],
        'validators' => [
            'text' => [ // Default validator, can be refined based on db_type
            ],
        ]
    ],
];
