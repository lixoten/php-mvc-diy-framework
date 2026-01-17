<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use Psr\Http\Message\UploadedFileInterface;

/**
 * File field validator
 */
class FileValidator extends AbstractValidator
{
    /**
     * {@inheritdoc}
     *
     * @param UploadedFileInterface|null $value The uploaded file instance, or null if not present.
     * @param array<string, mixed> $options Validation options (e.g., max_size, mime_types, required).
     * @return string|null
     */
    public function validate($value, array $options = []): ?string
    {
        $isRequired = (bool) ($options['required'] ?? false);

        // ✅ Handle UPLOAD_ERR_NO_FILE explicitly for required fields
        if ($value instanceof UploadedFileInterface && $value->getError() === UPLOAD_ERR_NO_FILE) {
            return $isRequired ? 'This file is required.' : null;
        }

        if (!($value instanceof UploadedFileInterface)) {
            return $isRequired ? 'This file is required.' : null;
        }

        if ($value->getError() !== UPLOAD_ERR_OK) {
            return match ($value->getError()) {
                UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
                default => 'Unknown upload error.',
            };
        }

        // ✅ Check for temp file metadata (injected by Validator service)
        $tempFileMetadata = $options['_temp_file_metadata'] ?? null;

        // ✅ Validate MIME type using REAL MIME type from disk (if available)
        if (isset($options['mime_types']) && is_array($options['mime_types'])) {
            $mimeType = $tempFileMetadata['mime_type'] ?? $value->getClientMediaType(); // ✅ Prefer real MIME type

            if (!in_array($mimeType, $options['mime_types'], true)) {//xxxx
                //return 'Invalid file type. Allowed types: ' . implode(', ', $options['mime_types']);
                $options['message'] = $options['message_invalid_mime'] ?? null;
                return $this->getErrorMessage($options, 'validation.invalid_mime');
                //return $this->getErrorMessage($options, $options['message_mime'] ?? 'validation.mime2');
                //$options['message'] ??= $options['required_message'] ?? null;
                //return $this->getErrorMessage($options, 'validation.required');
            }
        }

        // ✅ Validate max_size
        if (isset($options['max_size'])) {
            $fileSize = $tempFileMetadata['size_bytes'] ?? $value->getSize(); // ✅ Prefer validated size

            if ($fileSize > $options['max_size']) {
                $maxSizeMB = round($options['max_size'] / 1048576, 2);
                $options['message'] = $options['message_max_size'] ?? null;
                return $this->getErrorMessage($options, 'validation.max_size');
                //return "File size must not exceed {$maxSizeMB} MB.";
            }
        }

            // 2097152 x / 1048576, 2
        // ✅ Validate min_size (optional, rarely used but good for completeness)
        if (isset($options['min_size'])) {
            $fileSize = $tempFileMetadata['size_bytes'] ?? $value->getSize();

            if ($fileSize < $options['min_size']) {
                $minSizeKB = round($options['min_size'] / 1024, 2);
                return "File size must be at least {$minSizeKB} KB.";
            }
        }

        return null;
    }



    public function validateOLD2($value, array $options = []): ?string
    {
        // Get 'required' status from options (merged from form attributes in Validator service)
        $isRequired = (bool) ($options['required'] ?? false);

        // ✅ Handle UPLOAD_ERR_NO_FILE explicitly for required fields
        if ($value instanceof UploadedFileInterface && $value->getError() === UPLOAD_ERR_NO_FILE) {
            if ($isRequired) {
                // If the field is required but no file was uploaded, return an error.
                return $this->getErrorMessage($options, $options['message_required'] ?? 'This file field is required.');
            } else {
                // If the field is not required and no file was uploaded, validation passes.
                return null;
            }
        }

        // If the value is not an UploadedFileInterface at this point,
        // it implies that the field name was present in the form but no file
        // (even UPLOAD_ERR_NO_FILE) was associated. This could be an edge case.
        // Or if the form field was completely absent for an optional field.
        if (!($value instanceof UploadedFileInterface)) {
             if ($isRequired) {
                // This means the field was required, but no UploadedFileInterface was provided at all.
                // This could happen if the HTML input was missing, or a non-file input was treated as one.
                return $this->getErrorMessage($options, $options['message_missing_file'] ?? 'A file is expected but none was provided or it was invalid.');
             }
             return null; // Not an uploaded file for an optional field, validation passes.
        }

        // At this point, $value is an UploadedFileInterface and it's NOT UPLOAD_ERR_NO_FILE.
        // This means a file was actually provided (even if it might have other PHP upload errors).

        // If the file upload itself had an error (other than NO_FILE, which was handled above),
        // these are usually caught by FormHandler's generic PHP error checks, but a secondary check here is robust.
        if ($value->getError() !== UPLOAD_ERR_OK) {
            // These errors are typically system-level (e.g., UPLOAD_ERR_INI_SIZE).
            // FormHandler adds a general form error for these, but we can add a field-specific one too.
            return $this->getErrorMessage($options, $options['message_upload_error'] ?? 'File upload failed with an internal error.');
        }

        // Validate max_size
        if (isset($options['max_size'])) {
            $maxSize = (int) $options['max_size'];
            if ($value->getSize() > $maxSize) {
                // return $this->getErrorMessage($options, $options['message_size'] ?? 'The file is too large. Maximum allowed size is ' . ($maxSize / 1024 / 1024) . 'MB.');
                $toolarge = ($maxSize / 1024 / 1024) . 'MB';
                return $this->getErrorMessage($options, $options['message_size'] ?? 'validation.file_to_large');
            }
        }

        // Validate mime_types
        if (isset($options['mime_types']) && is_array($options['mime_types'])) {
            $allowedMimeTypes = array_map('strtolower', $options['mime_types']);
            $uploadedMimeType = strtolower($value->getClientMediaType());

            if (!in_array($uploadedMimeType, $allowedMimeTypes, true)) {
                return $this->getErrorMessage($options, $options['message_mime'] ?? 'Invalid file type. Only ' . implode(', ', $options['mime_types']) . ' are allowed.');
            }
        }

        return null;
    }



    /**
     * {@inheritdoc}
     *
     * @param UploadedFileInterface|null $value The uploaded file instance
     * @param array<string, mixed> $options Validation options (e.g., max_size, mime_types)
     * @return string|null
     */
    public function oldvalidate($value, array $options = []): ?string
    {
        // If the field is not required and no file was uploaded, skip validation
        if ($this->shouldSkipValidation($value) || !($value instanceof UploadedFileInterface)) {
            // If it's a required file field and $value is null/empty,
            // the 'required' validator (if used) should catch it.
            // This validator focuses on the *properties* of an uploaded file.
            return null;
        }

        // If the file upload itself had an error, it should ideally be caught by FormHandler
        // However, a secondary check here can ensure robustness.
        if ($value->getError() !== UPLOAD_ERR_OK) {
            // PHP upload errors should ideally be handled earlier by FormHandler
            // but if they slip through, this provides a fallback.
            return $this->getErrorMessage($options, 'File upload failed with error code ' . $value->getError() . '.');
        }

        // Validate max_size
        if (isset($options['max_size'])) {
            $maxSize = (int) $options['max_size'];
            if ($value->getSize() > $maxSize) {
                return $this->getErrorMessage($options, 'The file is too large. Maximum allowed size is ' . ($maxSize / 1024 / 1024) . 'MB.');
            }
        }

        // Validate mime_types
        if (isset($options['mime_types']) && is_array($options['mime_types'])) {
            $allowedMimeTypes = array_map('strtolower', $options['mime_types']);
            $uploadedMimeType = strtolower($value->getClientMediaType());

            if (!in_array($uploadedMimeType, $allowedMimeTypes, true)) {
                return $this->getErrorMessage($options, 'Invalid file type. Only ' . implode(', ', $options['mime_types']) . ' are allowed.');
            }
        }

        // You can add more file-specific validations here if needed, e.g., image dimensions, etc.

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'file';
    }

    protected function getDefaultOptions(): array
    {
        return [
            'max_size'           => null, // Expected max size in bytes
            'mime_types'         => [],   // Allowed MIME types
            'required'           => false, // Default to not required; overridden by form attributes
            'message'            => 'The uploaded file is invalid.', // General fallback message
            'message_required'   => 'This file field is required.',
            'message_size'       => 'The file is too large.',
            'message_mime'       => 'Invalid file type.',
            'message_upload_error' => 'A problem occurred during file upload.',
            'message_missing_file' => 'A file was expected but not found.',
        ];
    }
}
