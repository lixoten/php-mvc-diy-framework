<?php

namespace App\Helpers;

/**
 * Class HtmlHelper
 *
 * Provides utility functions for HTML-related tasks.
 *
 * @package App\Helpers
 */
class HtmlHelper
{
    /**
     * Escape a string for safe output in HTML.
     *
     * This method converts special characters to HTML entities to prevent XSS attacks.
     *
     * @param string $string The string to escape.
     * @return string The escaped string.
     */
    public static function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }


    /**
     * Generates a URL based on the provided parameters.
     *
     * @param string $route The route to generate the URL for.
     * @param array $params An array of parameters to include in the URL.
     * @return string The generated URL.
     */
    public static function generateUrl(string $route, array $params = []): string
    {
        $url = '/' . $route;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }


    /**
     * Generate or retrieve a CSRF token from the session.
     *
     * @return string The CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }


    /**
     * Generate an HTML hidden input field containing a CSRF token.
     *
     * @param int $indent Optional indentation spaces for formatting
     * @return string HTML for a hidden input field with CSRF token
     */
    public static function csrfField(int $indent = 8): string
    {
        $spaces = str_repeat(' ', $indent);
        return "\n" . $spaces . '<input type="hidden" name="csrf_token" value="' .
            self::generateCsrfToken() . '">' . "\n";
    }
}
