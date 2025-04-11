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
    public const VIEW_AUTH_REGISTRATION = 'Auth/registration';
    public const VIEW_AUTH_FORGOT_PASSWORD = 'Auth/forgot-password';
    public const VIEW_AUTH_RESET_PASSWORD = 'Auth/reset-password';

    public const VIEW_AUTH_REGISTRATION_SUCCESS = 'Auth/registration_success';

    // Add these constants to the existing class
    public const VIEW_AUTH_VERIFICATION_PENDING = 'Auth/verification_pending';
    public const VIEW_AUTH_VERIFICATION_SUCCESS = 'Auth/verification_success';
    public const VIEW_AUTH_VERIFICATION_ERROR = 'Auth/verification_error';
    public const VIEW_AUTH_VERIFICATION_RESEND = 'Auth/verification_resend';
    /**
     * Route names
     */
    public const ROUTE_LOGIN = 'auth.login';
    public const ROUTE_LOGOUT = 'auth.logout';
    public const ROUTE_REGISTRATION = 'auth.registration';
    public const ROUTE_FORGOT_PASSWORD = 'auth.forgot_password';

    public const ROUTE_VERIFY_EMAIL = 'auth.verify_email';
    public const ROUTE_VERIFICATION_PENDING = 'auth.verification_pending';
}
