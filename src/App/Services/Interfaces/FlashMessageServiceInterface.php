<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\Enums\FlashMessageType;

interface FlashMessageServiceInterface
{
    public function add(
        string $message,
        FlashMessageType $type = FlashMessageType::Info,
        bool $sticky = false
    ): FlashMessageServiceInterface;
    public function get(?FlashMessageType $type = null): array;
    public function has(?FlashMessageType $type = null): bool;
    /**
     * Gets flash messages without clearing them
     *
     * @param FlashMessageType|null $type The message type to retrieve, or null for all types
     * @return array The flash messages
     */
    public function peek(?FlashMessageType $type = null): array;
    // Other methods...
}
