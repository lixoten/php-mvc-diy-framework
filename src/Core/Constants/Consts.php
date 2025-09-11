<?php
// src/Core/Constants/ViewConstants.php
declare(strict_types=1);

namespace Core\Constants;

/**
 * View-related constants
 */
class Consts
{
    // View engines
    public const ENGINE_PHP = 'php';
    public const ENGINE_TWIG = 'twig';
    public const ENGINE_BLADE = 'blade';
    public const FOFO = 'FOFO-const';

    // Layout names
    public const LAYOUT_DEFAULT = 'layouts/base5simple';
    public const LAYOUT_AUTH = 'layouts/auth';
    public const LAYOUT_ADMIN = 'layouts/admin';
    public const LAYOUT_ERROR = 'layouts/error';

    public const CSS_BOOTSTRAP = 'bootstrap';
    public const CSS_TAILWIND = 'tailwind';
    public const CSS_BULMA = 'bulma';

    public const LAYOUT_SEQUENTIAL = 'sequential';
    public const LAYOUT_FIELDSETS = 'fieldsets';
    public const LAYOUT_SECTIONS = 'sections';
    public const LAYOUT_NONE = 'none';

    public const ERROR_INLINE = 'inline';
    public const ERROR_SUMMARY = 'summary';

    public const REDIRECT_HOME = 'home';

}