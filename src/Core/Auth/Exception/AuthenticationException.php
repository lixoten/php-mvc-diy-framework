<?php

declare(strict_types=1);

namespace Core\Auth\Exception;

class AuthenticationException extends \Exception
{
    public const INVALID_CREDENTIALS = 1;
    public const ACCOUNT_INACTIVE = 2;
    public const ACCOUNT_LOCKED = 3;
    public const SESSION_EXPIRED = 4;
    public const INSUFFICIENT_PERMISSIONS = 5;
    public const TOO_MANY_ATTEMPTS = 6;
}
