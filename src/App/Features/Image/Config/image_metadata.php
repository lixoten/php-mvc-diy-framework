<?php

declare(strict_types=1);

use App\Enums\Url;

return [
    // Entity metadata
    'metadata' => [
        'base_url_enum'     => Url::CORE_IMAGE,
        'edit_url_enum'     => Url::CORE_IMAGE_EDIT,
        'list_url_enum'     => Url::CORE_IMAGE_LIST,

        'create_url_enum'   => Url::CORE_IMAGE_CREATE,
        'view_url_enum'     => Url::CORE_IMAGE_VIEW,
        'delete_url_enum'   => Url::CORE_IMAGE_DELETE,
        // 'delete_confirm_url_enum' => Url::CORE_IMAGE_DELETE_CONFIRM,


        'owner_foreign_key'   => 'user_id',
        'redirect_after_save' => 'list',//list edit
        'redirect_after_add'  => 'list',
        'pageKey'             => 'image_edit',
        'entityName'          => 'image',
    ],
];
