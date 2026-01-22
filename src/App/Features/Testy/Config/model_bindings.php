<?php

declare(strict_types=1);

use App\Features\Testy\TestyRepositoryInterface;

/**
 * Model Binding Configuration for Testy Feature
 *
 * Defines which controller actions require model binding and how to authorize access.
 *
 * Structure:
 * - 'actions' => Array of controller action methods (e.g., 'editAction', 'viewAction')
 *   - Each action contains one or more parameter bindings (e.g., 'testy')
 *     - 'repository' => Repository interface to use for fetching the model
 *     - 'method' => Repository method to call (default: 'findById')
 *     - 'parameter_name' => Route parameter name (default: 'id')
 *     - 'authorization' => Authorization rules (optional)
 *       - 'check' => Whether to perform authorization (default: false)
 *       - 'owner_field' => Model field that stores the owner's user ID (default: 'user_id')
 *       - 'allowed_roles' => Roles that can bypass ownership check (e.g., ['admin'])
 */
return [
    'actions' => [
        // ✅ Edit Action: Requires ownership or admin role
        'editAction' => [
            'testy' => [
                'repository' => TestyRepositoryInterface::class,
                'method' => 'findById',
                // 'method' => 'findByIdWithFields',
                'parameter_name' => 'id',
                'fields' => [
                    'id',           // ✅ Primary key
                    'user_id',      // ✅ Ownership check
                    'store_id',     // ✅ Store context check
                    'updated_at',   // ✅ Optimistic locking (prevent concurrent edits)
                    'status',       // ✅ Business rule validation (can't edit archived records)
                    'title',        // ✅ User-friendly error messages
                ],
                'authorization' => [
                    'check' => true,
                    'owner_field' => 'user_id',
                    'store_field' => 'store_id',
                    'allowed_roles' => ['admin', 'store_owner'],
                ],
            ],
        ],
        'viewAction' => [
            'testy' => [
                'repository' => TestyRepositoryInterface::class,
                'method' => 'findByIdWithFields',
                'parameter_name' => 'id',
                'fields' => [
                    'id',
                    'user_id',
                    'store_id',
                    'title',        // ✅ For displaying in breadcrumbs/header
                    'status',       // ✅ Show status badge
                    'deleted_at',   // ✅ Check if soft-deleted
                ],
                'authorization' => [
                    'check' => true,
                    'owner_field' => 'user_id',
                    'store_field' => 'store_id',
                    'allowed_roles' => ['admin', 'store_owner'],
                ],
            ],
        ],
        'deleteAction' => [
            'testy' => [
                'repository' => TestyRepositoryInterface::class,
                'method' => 'findByIdWithFields',
                'parameter_name' => 'id',
                'fields' => [
                    'id',
                    'user_id',
                    'store_id',
                    'title',        // ✅ For confirmation message: "Delete 'My Awesome Testy'?"
                    'status',       // ✅ Prevent deleting published records
                ],
                'authorization' => [
                    'check' => true,
                    'owner_field' => 'user_id',
                    'store_field' => 'store_id',
                    'allowed_roles' => ['admin', 'store_owner'],
                ],
            ],
        ],

        // ✅ Example: Action with custom repository method
        // 'showWithRelationsAction' => [
        //     'testy' => [
        //         'repository' => TestyRepositoryInterface::class,
        //         'method' => 'findByIdWithRelations', // Custom method
        //         'parameter_name' => 'id',
        //         'authorization' => [
        //             'check' => false,
        //         ],
        //     ],
        // ],
    ],
];