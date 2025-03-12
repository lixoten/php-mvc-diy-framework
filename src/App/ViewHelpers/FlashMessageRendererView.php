<?php

declare(strict_types=1);

namespace App\ViewHelpers;

use App\Helpers\DebugRt as Debug;
use App\Enums\FlashMessageType;  // Change this to App\Enums\FlashMessageType
use App\Services\Interfaces\FlashMessageServiceInterface;

/**
 * FlashMessageRendererView
 */
class FlashMessageRendererView
{
    private FlashMessageServiceInterface $flash;

    // Change constructor to accept service
    public function __construct(FlashMessageServiceInterface $flash)
    {
        $this->flash = $flash;
    }

    // Add this method that your template is calling
    public function render(): string
    {
        ob_start();
        $this->renderMessages();
        return ob_get_clean();
    }

    public function getMessages(?FlashMessageType $type = null): array
    {
        // Use flash service instead of session array
        return $type !== null ? $this->flash->get($type) : $this->flash->get();
    }

    public function renderMessages(): void
    {
        $messages = $this->getMessages();

        foreach ($messages as $type => $msgs) {
            foreach ($msgs as $msgData) {
                echo $this->formatMessage($msgData, $type);
            }
        }
    }

    // Keep your existing formatMessage method
    protected function formatMessage(array $msgData, string $type): string
    {
        $cssClass = "alert alert-$type";
        if ($msgData['sticky']) {
            $cssClass .= ' sticky';  # not really needed.
        } else {
            $msgData['message'] = '<button type="button" class="close" data-dismiss="alert">&times;</button>'
             . $msgData['message'];
        }

        return "<div class=\"$cssClass\">
        {$msgData['message']}
        </div>\n";
    }
}
