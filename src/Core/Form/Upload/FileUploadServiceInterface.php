<?php

declare(strict_types=1);

namespace Core\Form\Upload;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Defines the contract for a service that handles the initial, generic upload
 * of files to a temporary, secure location. This service is agnostic to the
 * final purpose or type (image, document) of the file.
 */
interface FileUploadServiceInterface
{
    /**
     * Handles an array of uploaded files, moving them to a temporary location
     * and returning generic metadata for each.
     *
     * @param array<string, UploadedFileInterface|array<UploadedFileInterface>> $uploadedFiles An associative array
     *        where keys are form field names and values are UploadedFileInterface objects or arrays thereof.
     * @return array<string, array<string, mixed>> An associative array where keys are form field names
     *         and values are arrays of metadata for the temporarily stored files.
     *         Metadata includes: 'temporary_path', 'original_filename', 'mime_type', 'size_bytes', 'status' (success/failed).
     *         For multi-file uploads on a single field, the value will be an array of such metadata arrays.
     */
    public function handleFiles(array $uploadedFiles): array;
}
