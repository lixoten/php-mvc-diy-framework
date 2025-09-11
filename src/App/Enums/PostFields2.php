<?php

declare(strict_types=1);

// D:\xampp\htdocs\my_projects\mvclixo\src\App\Enums\PostFields2.php
namespace App\Enums;

enum PostFields2: string
{
    case ID = 'post_id';
    case TITLE = 'title';
    case CREATED_AT = 'created_at';
    case USER_ID = 'user_id';

    public function getMetadata(): array
    {
        return match ($this) {
            self::ID => [
                'label' => 'Post ID',
                'type' => 'int',
                'sortable' => true,
                'visible' => ['list'],
            ],
            self::TITLE => [
                'label' => 'Title',
                'type' => 'string',
                'sortable' => true,
                'visible' => ['list', 'form'],
            ],
            self::CREATED_AT => [
                'label' => 'Created On',
                'type' => 'DateTime',
                'sortable' => true,
                'visible' => ['list'],
            ],
            self::USER_ID => [
                'label' => 'User ID',
                'type' => 'int',
                'sortable' => false,
                'visible' => ['list'],
            ],
        };
    }

    public function label() {
        return $this->getMetadata()['label'];
    }
}
