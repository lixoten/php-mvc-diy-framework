<?php

declare(strict_types=1);

use App\Enums\Url;

return [
    // Entity metadata
    'metadata' => [
        'base_url_enum'     => Url::CORE_TESTY,
        'edit_url_enum'     => Url::CORE_TESTY_EDIT,
        'list_url_enum'     => Url::CORE_TESTY_LIST,

        'create_url_enum'   => Url::CORE_TESTY_CREATE,
        'view_url_enum'     => Url::CORE_TESTY_VIEW,
        'delete_url_enum'   => Url::CORE_TESTY_DELETE,
        'delete_confirm_url_enum' => Url::CORE_TESTY_DELETE_CONFIRM,


        'owner_foreign_key'   => 'user_id',
        'redirect_after_save' => 'edit',
        'redirect_after_add'  => 'list',
        'pageKey'             => 'testy_edit',
        'entityName'          => 'testy',
    ],
];
