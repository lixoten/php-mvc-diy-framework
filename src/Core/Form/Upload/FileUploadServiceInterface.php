<?php

declare(strict_types=1);

namespace Core\Form\Upload;

use Psr\Http\Message\UploadedFileInterface;

/**
 * Interface for high-level file upload orchestration.
 */
interface FileUploadServiceInterface
{
    /**
     * Process uploaded files and return per-field metadata:
     * - single file => array{key:string, original_name:string, mime:string, size:int}
     * - multi file  => array<int, array{key:string, original_name:string, mime:string, size:int}>
     *
     * @param array<string, UploadedFileInterface|UploadedFileInterface[]> $uploadedFiles
     * @param array<string, mixed> $fieldDefinitions
     * @return array<string, mixed>
     */
    public function handleFiles(array $uploadedFiles, array $fieldDefinitions): array;
}
