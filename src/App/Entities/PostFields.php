<?php

declare(strict_types=1);

namespace App\Entities;

/**
 * Field name constants for the Post entity.
 */
final class PostFields
{
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const POST_ID = 'postId';
    public const POST_STORE_ID = 'postStoreId';
    public const POST_USER_ID = 'postUserId';
    public const POST_STATUS = 'postStatus';
    public const SLUG = 'slug';
    public const TITLE = 'title';
    public const CONTENT = 'content';
    public const USERNAME = 'username';
}

// <?php
// enum SortDirection: string
// {
//     case ASC = 'ASC';
//     case DESC = 'DESC';
// }


// <?php
// use Core\List\SortDirection;
// use Core\List\ListOptions;

// $options = [
//     ListOptions::DEFAULT_SORT_DIRECTION => SortDirection::ASC->value,
// ];

// <?php
// enum SortDirection: string
// {
//     case ASC = 'ASC';
//     case DESC = 'DESC';
// }

















