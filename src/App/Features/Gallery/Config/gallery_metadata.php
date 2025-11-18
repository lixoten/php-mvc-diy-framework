<?php

declare(strict_types=1);

use App\Enums\Url;


return [
    // Entity metadata
    'metadata' => [
        'base_url_enum'     => Url::CORE_GALLERY,
        'edit_url_enum'     => Url::CORE_GALLERY_EDIT,
        'list_url_enum'     => Url::CORE_GALLERY_LIST,

        'create_url_enum'   => Url::CORE_GALLERY_CREATE,
        'view_url_enum'     => Url::CORE_GALLERY_VIEW,
        'delete_url_enum'   => Url::CORE_GALLERY_DELETE,
        'delete_confirm_url_enum' => Url::CORE_GALLERY_DELETE_CONFIRM,


        'owner_foreign_key' => 'user_id',
        'redirect_after_save' => 'edit',
        'redirect_after_add' => 'list',
        'pageKey' => 'gallery_edit',
        'entityName' => 'gallery',
    ],
];
