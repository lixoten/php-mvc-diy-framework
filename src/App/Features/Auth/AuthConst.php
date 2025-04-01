<?php

declare(strict_types=1);

namespace App\Features\Auth;

/**
 * Auth feature constants
 */
class AuthConst
{
    /**
     * View templates
     */
    public const VIEW_AUTH_LOGIN = 'Auth/login';
    public const VIEW_AUTH_REGISTER = 'Auth/register';
    public const VIEW_AUTH_FORGOT_PASSWORD = 'Auth/forgot-password';
    public const VIEW_AUTH_RESET_PASSWORD = 'Auth/reset-password';

    /**
     * Route names
     */
    public const ROUTE_LOGIN = 'auth.login';
    public const ROUTE_LOGOUT = 'auth.logout';
    public const ROUTE_REGISTER = 'auth.register';
    public const ROUTE_FORGOT_PASSWORD = 'auth.forgot_password';
}
