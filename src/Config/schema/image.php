<?php

declare(strict_types=1);

return [
    // Entity Metadata
    'entity' => [
        'name' => 'Image',
        'table' => 'image',
        'timestamps' => true,
    ],

    // Field Definitions
    'fields' => [
        'id' => [
            'db_type' => 'bigIncrements',
            'primary' => true,
            'auto_increment' => true,
        ],
        'store_id' => [
            'db_type' => 'foreignId',
            'nullable' => false,
            'comment' => 'Store this image belongs to',
            'foreign_key' => [
                'table' => 'store',
                'column' => 'store_id',
                'name' => 'fk_image_store',
                'on_delete' => 'CASCADE'
            ]
        ],
        'user_id' => [
            'db_type' => 'foreignId',
            'nullable' => false,
            'comment' => 'User who uploaded the image',
            'foreign_key' => [
                'table' => 'users',
                'column' => 'user_id',
                'name' => 'fk_image_user',
                'on_delete' => 'CASCADE'
            ]
        ],
        'gallery_id' => [
            'db_type' => 'bigInteger',
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'Optional gallery assignment',
            'foreign_key' => [
                'table' => 'gallery',
                'column' => 'gallery_id',
                'name' => 'fk_image_gallery',
                'on_delete' => 'SET NULL'
            ]
        ],
        'status' => [
            'db_type' => 'string',
            'length' => 1,
            'nullable' => false,
            'default' => 'A',
            'comment' => 'A=Active, P=Pending, I=Inactive',
            'check' => "status IN ('A', 'P', 'I')"
        ],
        'slug' => [
            'db_type' => 'string',
            'length' => 255,
            'nullable' => false,
            'unique' => true,
            'comment' => 'SEO-friendly URL slug (e.g., sunset-over-ocean)'
        ],
        'title' => [
            'db_type' => 'string',
            'length' => 255,
            'nullable' => true,
            'comment' => 'User-provided title for SEO'
        ],
        'description' => [
            'db_type' => 'text',
            'nullable' => true,
            'comment' => 'Detailed description'
        ],
        'filename' => [
            'db_type' => 'string',
            'length' => 255,
            'nullable' => false,
            'comment' => 'Original filename'
        ],
        'filepath' => [
            'db_type' => 'string',
            'length' => 500,
            'nullable' => false,
            'comment' => 'Path to the file (relative to public_html)'
        ],
        'filesize' => [
            'db_type' => 'bigInteger',
            'nullable' => false,
            'unsigned' => true,
            'comment' => 'File size in bytes'
        ],
        'mime_type' => [
            'db_type' => 'string',
            'length' => 50,
            'nullable' => false,
            'comment' => 'MIME type (e.g., image/jpeg, image/png)'
        ],
        'width' => [
            'db_type' => 'integer',
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'Image width in pixels'
        ],
        'height' => [
            'db_type' => 'integer',
            'nullable' => true,
            'unsigned' => true,
            'comment' => 'Image height in pixels'
        ],
        'alt_text' => [
            'db_type' => 'string',
            'length' => 255,
            'nullable' => true,
            'comment' => 'Alt text for accessibility'
        ],
        'caption' => [
            'db_type' => 'text',
            'nullable' => true,
            'comment' => 'Image caption'
        ],
        'display_order' => [
            'db_type' => 'integer',
            'nullable' => false,
            'default' => 0,
            'comment' => 'Sort order within gallery'
        ],
        'is_featured' => [
            'db_type' => 'boolean',
            'nullable' => false,
            'default' => false,
            'comment' => 'Is this the featured image for a gallery?'
        ]
    ],

    // Additional Indexes
    'indexes' => [
        [
            'name' => 'idx_gallery_display_order',
            'columns' => ['gallery_id', 'display_order'],
            'type' => 'index'
        ],
        [
            'name' => 'unique_slug_store',
            'columns' => ['slug', 'store_id'],
            'type' => 'unique'
        ]
    ],

    // Repository Configuration
    // 'repository' => [
        // 'extends' => 'AbstractMultiTenantRepository',
    // ],
    'repository' => [
        'extends' => 'AbstractMultiTenantRepository',
        'implements' => ['ImageRepositoryInterface', 'BaseRepositoryInterface'],
        'queries' => [
            'findById' => "SELECT i.*, u.username
                FROM {tableName} i
                LEFT JOIN users u ON i.user_id = u.user_id
                WHERE i.{primaryKey} = :id",
            'findBy' => "SELECT i.*, u.username
                FROM {tableName} i
                LEFT JOIN users u ON i.user_id = u.user_id",
        ],
        'joins' => [
            'users' => [
                'type' => 'LEFT',
                'on' => 'i.user_id = u.user_id',
                'alias' => 'u',
                'select' => ['username'],
            ],
        ],
        'custom_methods' => [
            [
                'name' => 'findByGalleryId',
                'description' => 'Find images by gallery ID.',
                'params' => [
                    ['type' => 'int', 'name' => 'galleryId'],
                    ['type' => 'array<string, string>', 'name' => 'orderBy', 'default' => "['display_order' => 'ASC']"],
                    ['type' => '?int', 'name' => 'limit', 'default' => 'null'],
                    ['type' => '?int', 'name' => 'offset', 'default' => 'null'],
                ],
                'return' => 'array<Image>',
                'implementation' => 'findBy',
                'criteria' => ['gallery_id' => '$galleryId'],
            ],
            [
                'name' => 'findByGalleryIdWithFields',
                'description' => 'Find images by gallery ID with specified fields (raw data).',
                'params' => [
                    ['type' => 'int', 'name' => 'galleryId'],
                    ['type' => 'array<string>', 'name' => 'fields'],
                    ['type' => 'array<string, string>', 'name' => 'orderBy', 'default' => "['display_order' => 'ASC']"],
                    ['type' => '?int', 'name' => 'limit', 'default' => 'null'],
                    ['type' => '?int', 'name' => 'offset', 'default' => 'null'],
                ],
                'return' => 'array<array<string, mixed>>',
                'implementation' => 'findByCriteriaWithFields',
                'criteria' => ['gallery_id' => '$galleryId'],
            ],
            [
                'name' => 'countByGalleryId',
                'description' => 'Count images by gallery ID.',
                'params' => [
                    ['type' => 'int', 'name' => 'galleryId'],
                ],
                'return' => 'int',
                'implementation' => 'countBy',
                'criteria' => ['gallery_id' => '$galleryId'],
            ],
            [
                'name' => 'getFeaturedImageByGalleryId',
                'description' => 'Get featured image for a gallery.',
                'params' => [
                    ['type' => 'int', 'name' => 'galleryId'],
                ],
                'return' => 'Image|null',
                'body' => <<<'PHP'
            $results = $this->findBy(['gallery_id' => $galleryId, 'is_featured' => 1], [], 1);
            return $results[0] ?? null;
    PHP,
            ],
            [
                'name' => 'setFeaturedImage',
                'description' => 'Set featured image for a gallery (unsets all others in the same gallery).',
                'params' => [
                    ['type' => 'int', 'name' => 'imageId'],
                    ['type' => 'int', 'name' => 'galleryId'],
                ],
                'return' => 'bool',
                'body' => <<<'PHP'
            // First, unset all featured images in this gallery
            $sql = "UPDATE {$this->tableName}
                    SET is_featured = 0, updated_at = NOW()
                    WHERE gallery_id = :gallery_id";

            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(':gallery_id', $galleryId, \PDO::PARAM_INT);
            $stmt->execute();

            // Then, set the specified image as featured
            return $this->updateFields($imageId, ['is_featured' => 1]);
    PHP,
            ],
            [
                'name' => 'updateDisplayOrder',
                'description' => 'Update display order for an image.',
                'params' => [
                    ['type' => 'int', 'name' => 'id'],
                    ['type' => 'int', 'name' => 'displayOrder'],
                ],
                'return' => 'bool',
                'body' => "return \$this->updateFields(\$id, ['display_order' => \$displayOrder]);",
            ],
        ],
    ],
    // Controller Configuration
    'controller' => [
        'route_context' => 'account',
    ],
];
