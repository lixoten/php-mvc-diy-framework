<?php
// filepath: d:\xampp\htdocs\mvc3\tests\App\Services\MockFlashMessageService.php

namespace Tests\App\Services;

use App\Enums\FlashMessageType;
use App\Services\Interfaces\FlashMessageServiceInterface;

class MockFlashMessageService implements FlashMessageServiceInterface
{
    private array $messages = [];

    public function add(string $message, FlashMessageType $type = FlashMessageType::Info, bool $sticky = false): FlashMessageServiceInterface
    {
        $this->messages[$type->value][] = [
            'message' => $message,
            'sticky' => $sticky
        ];

        return $this;
    }

    public function get(?FlashMessageType $type = null): array
    {
        $messages = $this->peek($type);

        if ($type !== null) {
            unset($this->messages[$type->value]);
        } else {
            $this->messages = [];
        }

        return $messages;
    }

    public function peek(?FlashMessageType $type = null): array
    {
        if ($type !== null) {
            return $this->messages[$type->value] ?? [];
        }

        return $this->messages;
    }

    public function has(?FlashMessageType $type = null): bool
    {
        // If checking for a specific type
        if ($type !== null) {
            return !empty($this->messages[$type->value]);
        }

        // Check all types
        foreach (FlashMessageType::cases() as $messageType) {
            if (!empty($this->messages[$messageType->value])) {
                return true;
            }
        }

        return false;
    }
}
