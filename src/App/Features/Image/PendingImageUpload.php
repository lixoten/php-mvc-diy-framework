<?php

declare(strict_types=1);

namespace App\Features\Image;

/**
 * ✅ Temporary entity for tracking pending image uploads.
 * Records are deleted after finalization or expiry.
 */
class PendingImageUpload
{
    private int $id = 0;
    private string $upload_token = '';
    private int $store_id = 0;
    private int $user_id = 0;
    private string $temp_path = '';
    private string $original_filename = '';
    private string $client_mime_type = '';
    private int $file_size_bytes = 0;
    private string $created_at = '';
    private string $expires_at = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUploadToken(): string
    {
        return $this->upload_token;
    }

    public function setUploadToken(string $upload_token): self
    {
        $this->upload_token = $upload_token;
        return $this;
    }

    public function getStoreId(): int
    {
        return $this->store_id;
    }

    public function setStoreId(int $store_id): self
    {
        $this->store_id = $store_id;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getTempPath(): string
    {
        return $this->temp_path;
    }

    public function setTempPath(string $temp_path): self
    {
        $this->temp_path = $temp_path;
        return $this;
    }

    public function getOriginalFilename(): string
    {
        return $this->original_filename;
    }

    public function setOriginalFilename(string $original_filename): self
    {
        $this->original_filename = $original_filename;
        return $this;
    }

    public function getClientMimeType(): string
    {
        return $this->client_mime_type;
    }

    public function setClientMimeType(string $client_mime_type): self
    {
        $this->client_mime_type = $client_mime_type;
        return $this;
    }

    public function getFileSizeBytes(): int
    {
        return $this->file_size_bytes;
    }

    public function setFileSizeBytes(int $file_size_bytes): self
    {
        $this->file_size_bytes = $file_size_bytes;
        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getExpiresAt(): string
    {
        return $this->expires_at;
    }

    public function setExpiresAt(string $expires_at): self
    {
        $this->expires_at = $expires_at;
        return $this;
    }

    /**
     * Check if this pending upload has expired.
     */
    public function isExpired(): bool
    {
        return strtotime($this->expires_at) < time();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'upload_token' => $this->upload_token,
            'store_id' => $this->store_id,
            'user_id' => $this->user_id,
            'temp_path' => $this->temp_path,
            'original_filename' => $this->original_filename,
            'client_mime_type' => $this->client_mime_type,
            'file_size_bytes' => $this->file_size_bytes,
            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,
        ];
    }
}