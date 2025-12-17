<?php

declare(strict_types=1);

namespace Core\Exceptions;

/**
 * Exception thrown when a critical file upload error occurs.
 *
 * This exception indicates a system-level failure (e.g., storage unavailable,
 * disk full, permission denied) rather than a validation failure.
 *
 * Validation failures (oversized file, wrong MIME type) should be handled
 * gracefully by returning null from FileUploadService and logging warnings.
 *
 * @package Core\Form\Upload
 */
class FileUploadException extends \RuntimeException
{
}
