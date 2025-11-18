<?php

declare(strict_types=1);

namespace Core\Exceptions;

use RuntimeException;

/**
 * Exception thrown when an invalid formatter chain is detected in strict validation mode.
 *
 * This occurs when an HTML-producing formatter (e.g., 'badge', 'image_link') is followed
 * by an HTML-escaping formatter (e.g., 'text'), which would cause the HTML to be rendered
 * as plain text instead of being displayed correctly.
 */
class InvalidFormatterChainException extends RuntimeException
{
}
