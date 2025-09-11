<?php

declare(strict_types=1);

namespace App\ViewHelpers;

use App\Helpers\DebugRt as Debug;
use App\Enums\FlashMessageType;
use App\Helpers\MyLinkHelper as LinkHelper;
use App\Services\Interfaces\FlashMessageServiceInterface;

/**
 * FlashMessageRendererView
 */
class FlashMessageRendererView
{
    private FlashMessageServiceInterface $flash;

    public function __construct(FlashMessageServiceInterface $flash)
    {
        $this->flash = $flash;
    }

    // method that your template is calling
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

    private function renderMessages(): void
    {
        $messages = $this->getMessages();

        foreach ($messages as $type => $msgs) {
            foreach ($msgs as $msgData) {
                echo $this->formatMessage($msgData, $type);
            }
        }
    }

    protected function formatMessage(array $msgData, string $type): string
    {
        $cssClass = "alert alert-$type";

        $messageHtml = $msgData['message'];

        // Add link if link data exists
        if (isset($msgData['linkData']) && !empty($msgData['linkData'])) {
            $messageHtml .= ' --- ' . LinkHelper::render($msgData['linkData']);
        }


        if ($msgData['sticky']) {
            $cssClass .= ' sticky';  # not really needed.
        } else {
            $messageHtml = '<button type="button" class="close" data-dismiss="alert">&times;</button>'
             . $messageHtml;
        }

        return "<div class=\"$cssClass\">
        {$messageHtml}
        </div>\n";
    }
}
