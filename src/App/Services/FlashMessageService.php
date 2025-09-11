<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\FlashMessageType;
use App\Helpers\DebugRt;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Session\SessionManagerInterface;

/**
 * Flash message service for temporary notifications
 *
 * Flash messages are stored as:
 * [
 *   'success' => [
 *     ['message' => string, 'sticky' => bool],
 *     ['message' => string, 'sticky' => bool],
 *   ],
 *   'error' => [
 *     ['message' => string, 'sticky' => bool],
 *   ],
 *   etc...
 * ]
 */
class FlashMessageService implements FlashMessageServiceInterface
{
    private const SESSION_KEY = 'flash_messages';
    private SessionManagerInterface $sessionManager;

    public function __construct(SessionManagerInterface $sessionManager)
    {
        $this->sessionManager = $sessionManager;

        // Initialize flash message array if needed
        if (!$this->sessionManager->has(self::SESSION_KEY)) {
            $this->sessionManager->set(self::SESSION_KEY, []);
        }
    }

    public function add(
        string $message,
        FlashMessageType $type = FlashMessageType::Info,
        bool $sticky = false,
        ?array $linkData = null
    ): FlashMessageServiceInterface {
        // Get current messages
        $messages = $this->sessionManager->get(self::SESSION_KEY, []);

        // Add new message
        $messages[$type->value][] = [
            'message' => $message,
            'sticky' => $sticky,
            'linkData' => $linkData
        ];

        // Store updated messages
        $this->sessionManager->set(self::SESSION_KEY, $messages);

        return $this;
    }


    /**
     * Gets and clears flash messages
     */
    public function get(?FlashMessageType $type = null): array
    {
        $messages = $this->peek($type);

        // Clear the retrieved messages
        $allMessages = $this->sessionManager->get(self::SESSION_KEY, []);

        if ($type !== null) {
            unset($allMessages[$type->value]);
        } else {
            $allMessages = []; // Clear all messages
        }

        $this->sessionManager->set(self::SESSION_KEY, $allMessages);

        return $messages;
    }

    /**
     * Checks if any flash messages exist
     */
    public function has(?FlashMessageType $type = null): bool
    {
        $allMessages = $this->sessionManager->get(self::SESSION_KEY, []);

        if ($type !== null) {
            return !empty($allMessages[$type->value]);
        }

        // Check if there are any messages of any type
        foreach (FlashMessageType::cases() as $messageType) {
            if (!empty($allMessages[$messageType->value])) {
                return true;
            }
        }

        return false;
    }


    /**
     * Gets flash messages without clearing them
     */
    public function peek(?FlashMessageType $type = null): array
    {
        $allMessages = $this->sessionManager->get(self::SESSION_KEY, []);

        if ($type !== null) {
            return $allMessages[$type->value] ?? [];
        }

        // Return all message types
        $result = [];
        foreach (FlashMessageType::cases() as $messageType) {
            $typeValue = $messageType->value;
            if (!empty($allMessages[$typeValue])) {
                $result[$typeValue] = $allMessages[$typeValue];
            }
        }

        return $result;
    }
}
