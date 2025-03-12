<?php

declare(strict_types=1);

namespace App\Enums;

enum FlashMessageType: string
{
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';
    case Error = 'danger';
}
