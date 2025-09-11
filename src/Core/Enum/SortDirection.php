<?php

declare(strict_types=1);

namespace Core\Enum;

/**
 * Enum SortDirection
 *
 * Represents sorting direction for lists and queries.
 *
 * @package Core\Enum
 */
enum SortDirection: string
{
    case ASC = 'ASC';   // Ascending order
    case DESC = 'DESC'; // Descending order
}
