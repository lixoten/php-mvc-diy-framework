<?php
// filepath: d:\xampp\htdocs\my_projects\mvclixo\src\Config\app_lookups.php

/**
 * Centralized Code Lookup Configuration
 *
 * This file defines all code-to-label mappings for the application.
 * Each top-level key represents a "type" of code (e.g., 'gender', 'payment_type').
 *
 * Structure:
 * return [
 *     'type_name' => [
 *         'code' => [
 *             'label'   => 'translation.key',  // Translation key (processed by I18nTranslator)
 *             'variant' => 'semantic_value',   // Optional: Semantic UI variant (info, primary, success, etc.)
 *             'icon'    => 'icon_name',        // Optional: Icon identifier
 *             'hex'     => '#RRGGBB',          // Optional: Hex color code
 *             // ... other custom properties as needed
 *         ],
 *     ],
 * ];
 *
 * Design Principles:
 * - ✅ Pure Data: No closures, no business logic, no function calls.
 * - ✅ Framework-Neutral: 'variant' values are semantic (e.g., 'info', 'success'),
 *                         not theme-specific (e.g., 'badge-info'). Renderers map these.
 * - ✅ Translatable: All 'label' values are translation keys, translated at render time.
 * - ✅ Extensible: Easy to add new properties (e.g., 'sort_order', 'is_active').
 *
 * @see CodeLookupService for methods that consume this data
 * @see lang/en/common_lang.php for translation keys
 */

declare(strict_types=1);

return [
    /**
     * Gender Codes
     *
     * Used by: gender_id field in various entities
     * Database: Stored as CHAR(4) with CHECK constraint
     */
    'gender' => [
        'm'  => [
            'label'   => 'gender.male',
            'variant' => 'info',        // Semantic variant (Bootstrap: badge-info, Material: mdc-theme--info)
        ],
        'f'  => [
            'label'   => 'gender.female',
            'variant' => 'primary',
        ],
        'o'  => [
            'label'   => 'gender.other',
            'variant' => 'secondary',
        ],
        'nb' => [
            'label'   => 'gender.non_binary',
            'variant' => 'dark',
        ],
    ],

    /**
     * Payment Type Codes
     *
     * Used by: payment_type field in order/transaction entities
     * Database: Stored as VARCHAR(10)
     */
    'payment_type' => [
        'CC'  => [
            'label'   => 'payment.credit_card',
            'icon'    => 'fa-credit-card',
            'variant' => 'primary',
        ],
        'PP'  => [
            'label'   => 'payment.paypal',
            'icon'    => 'fa-paypal',
            'variant' => 'info',
        ],
        'INV' => [
            'label'   => 'payment.invoice',
            'icon'    => 'fa-file-invoice',
            'variant' => 'secondary',
        ],
        'COD' => [
            'label'   => 'payment.cash_on_delivery',
            'icon'    => 'fa-money-bill',
            'variant' => 'success',
        ],
    ],

    /**
     * Delivery Method Codes
     *
     * Used by: delivery_method field in order entities
     * Database: Stored as VARCHAR(10)
     */
    'delivery_method' => [
        'STD' => [
            'label'   => 'delivery.standard',
            'variant' => 'secondary',
        ],
        'EXP' => [
            'label'   => 'delivery.express',
            'variant' => 'warning',
        ],
        'PUP' => [
            'label'   => 'delivery.pickup',
            'variant' => 'info',
        ],
    ],

    /**
     * Notification Frequency Codes
     *
     * Used by: notification_frequency field in user settings
     * Database: Stored as CHAR(1)
     */
    'notification_frequency' => [
        'D' => [
            'label'   => 'notification.daily',
            'variant' => 'primary',
        ],
        'W' => [
            'label'   => 'notification.weekly',
            'variant' => 'info',
        ],
        'M' => [
            'label'   => 'notification.monthly',
            'variant' => 'secondary',
        ],
        'N' => [
            'label'   => 'notification.never',
            'variant' => 'dark',
        ],
    ],

    /**
     * Example: US States (Abbreviated for demonstration)
     *
     * For production, you would include all 50+ states.
     * This demonstrates handling large code sets without creating PHP classes.
     */
    'us_states' => [
        'AL' => ['label' => 'states.alabama'],
        'AK' => ['label' => 'states.alaska'],
        'AZ' => ['label' => 'states.arizona'],
        'CA' => ['label' => 'states.california'],
        'NY' => ['label' => 'states.new_york'],
        'TX' => ['label' => 'states.texas'],
        // ... (add remaining 44+ states as needed)
    ],

    /**
     * Example: Simple Color Codes
     *
     * Used by: color field in product/design entities
     * Database: Stored as VARCHAR(10)
     */
    'color' => [
        'RED'   => [
            'label'   => 'color.red',
            'hex'     => '#FF0000',
            'variant' => 'danger',
        ],
        'BLUE'  => [
            'label'   => 'color.blue',
            'hex'     => '#0000FF',
            'variant' => 'primary',
        ],
        'GREEN' => [
            'label'   => 'color.green',
            'hex'     => '#00FF00',
            'variant' => 'success',
        ],
        'BLACK' => [
            'label'   => 'color.black',
            'hex'     => '#000000',
            'variant' => 'dark',
        ],
    ],

    // ✅ Add more code types here as needed:
    // - 'person_type' => ['C' => ['label' => 'person.child'], 'A' => ['label' => 'person.adult']],
    // - 'feeling_today' => ['H' => ['label' => 'feeling.happy', 'emoji' => '😊'], ...],
    // - 'icon_type' => ['CAL' => ['label' => 'icon.calendar', 'svg_path' => '/assets/icons/calendar.svg'], ...],
];