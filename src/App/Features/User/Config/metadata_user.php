<?php

declare(strict_types=1);

use App\Enums\Url;


return [
    // Entity metadata
    'metadata' => [
        'base_url_enum'     => Url::CORE_USER,
        'edit_url_enum'     => Url::CORE_USER_EDIT,
        'list_url_enum'     => Url::CORE_USER_LIST,

        'create_url_enum'   => Url::CORE_USER_CREATE,
        'view_url_enum'     => Url::CORE_USER_VIEW,
        'delete_url_enum'   => Url::CORE_USER_DELETE,
        'delete_confirm_url_enum' => Url::CORE_USER_DELETE_CONFIRM,



        'owner_foreign_key' => null,//'user_id',
        'redirect_after_save' => 'edit',
        'redirect_after_add' => 'list',
        'pageName' => 'user_edit',
        'entityName' => 'user',
    ],
];
