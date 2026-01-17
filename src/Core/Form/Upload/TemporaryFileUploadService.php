<?php

declare(strict_types=1);

namespace Core\Form\Upload;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Service to handle the generic movement of uploaded files to a secure,
 * temporary location within the application's control.
 * It is agnostic to the file's final purpose (e.g., image, document).
 */
class TemporaryFileUploadService implements FileUploadServiceInterface
{
    /**
     * The directory where temporary files will be stored.
     * Needs to be writable by the web server.
     */
    private string $tempUploadDir;

    public function __construct(
        private LoggerInterface $logger,
        string $tempUploadDir = '' // Can be configured via DI
    ) {
        $this->tempUploadDir = $tempUploadDir ?: sys_get_temp_dir();

        if (!is_dir($this->tempUploadDir) && !mkdir($this->tempUploadDir, 0775, true)) {
            throw new RuntimeException("Temporary upload directory does not exist and could not be created: " . $this->tempUploadDir);
        }
        if (!is_writable($this->tempUploadDir)) {
            throw new RuntimeException("Temporary upload directory is not writable: " . $this->tempUploadDir);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleFiles(array $uploadedFiles): array
    {
        $processedFiles = [];

        foreach ($uploadedFiles as $fieldName => $uploadedFile) {
            if (is_array($uploadedFile)) {
                // Handle multiple files for a single field (e.g., <input type="file" name="images[]">)
                $processedFiles[$fieldName] = [];
                foreach ($uploadedFile as $individualFile) {
                    if ($individualFile instanceof UploadedFileInterface) {
                        $processedFiles[$fieldName][] = $this->processSingleFile($individualFile, $fieldName);
                    }
                }
            } elseif ($uploadedFile instanceof UploadedFileInterface) {
                // Handle a single file for a field
                $processedFiles[$fieldName] = $this->processSingleFile($uploadedFile, $fieldName);
            }
        }

        return $processedFiles;
    }

    /**
     * Processes a single UploadedFileInterface, moving it to temporary storage.
     *
     * @param UploadedFileInterface $uploadedFile The file to process.
     * @param string $fieldName The name of the form field.
     * @return array<string, mixed> Metadata of the temporarily stored file or error information.
     */
    private function processSingleFile(UploadedFileInterface $uploadedFile, string $fieldName): array
    {
        $errorMessage = $this->getPhpUploadErrorMessage($uploadedFile->getError());

        $metadata = [
            'status'            => 'failed',
            'error_code'        => $uploadedFile->getError(),
            //'error_message'   => $uploadedFile->getError() !== UPLOAD_ERR_OK ? $uploadedFile->getErrorMessage() : null,
            'error_message'     => $errorMessage, // ✅ FIX: Use the generated error message
            'original_filename' => $uploadedFile->getClientFilename(),
            'client_mime_type'  => $uploadedFile->getClientMediaType(), // ⚠️ FROM BROWSER (untrusted)
            'mime_type'         => null, // ✅ NEW: Will be detected from disk
            'size_bytes'        => $uploadedFile->getSize(),
            'temporary_path'   => null,
        ];

        if ($uploadedFile->getError() === UPLOAD_ERR_NO_FILE) {
            $metadata['status'] = 'no_file';
            $metadata['error_message'] = 'No file uploaded.';
            $this->logger->debug("No file uploaded for field: {$fieldName}");
            return $metadata;
        }

        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            $this->logger->error("File upload error for field {$fieldName}: {$metadata['error_message']}", ['code' => $metadata['error_code']]);
            return $metadata;
        }

        try {
            // Generate a unique name for the temporary file
            $uniqueFilename = uniqid('upload_', true) . '.' . pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $temporaryPath = $this->tempUploadDir . DIRECTORY_SEPARATOR . $uniqueFilename;

            $uploadedFile->moveTo($temporaryPath);

            // ✅ NEW: Detect REAL MIME type from disk using finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMimeType = finfo_file($finfo, $temporaryPath);
            finfo_close($finfo);

            $metadata['temporary_path'] = $temporaryPath;
            $metadata['mime_type'] = $realMimeType; // ✅ TRUSTED MIME type from disk
            $metadata['status'] = 'success';
            $metadata['error_code'] = UPLOAD_ERR_OK;
            $metadata['error_message'] = null; // Clear error message on success

            $this->logger->info("File successfully moved to temporary location: {$temporaryPath}", [
                'field' => $fieldName,
                'original_filename' => $metadata['original_filename'],
                'client_mime_type' => $metadata['client_mime_type'],
                'real_mime_type' => $metadata['mime_type'], // ✅ Log both for debugging
            ]);
        } catch (RuntimeException $e) {
            $metadata['error_message'] = 'Failed to move uploaded file to temporary directory: ' . $e->getMessage();
            $this->logger->error("Temporary file move failed for field {$fieldName}: {$e->getMessage()}", ['exception' => $e]);
        }

        return $metadata;
    }

    /**
     * Returns a human-readable error message for a given PHP file upload error code.
     *
     * @param int $errorCode One of PHP's UPLOAD_ERR_XXX constants.
     * @return string The error message.
     */
    private function getPhpUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => 'Unknown upload error.',
        };
    }
}
