<?php

declare(strict_types=1);

namespace Core\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when a field definition fails schema validation.
 */
class FieldSchemaValidationException extends RuntimeException
{
    /**
     * @param string $message The exception message.
     * @param string $devCode A unique developer error code for easier debugging.
     * @param string|null $suggestion An optional suggestion for resolving the issue.
     * @param Throwable|null $previous The previous throwable used for the exception chaining.
     */
    public function __construct(
        string $message,
        private string $devCode,
        private ?string $suggestion = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        // Append devCode and suggestion to the message for easier logging/display
        $fullMessage = $message;

        parent::__construct($fullMessage, $code, $previous);
    }

    public function getDevCode(): string
    {
        return $this->devCode;
    }

    public function getSuggestion(): ?string
    {
        return $this->suggestion;
    }

    ////////////////
    public function toHtmlHelp(): string {
        $errors     = [];//$exception->getErrors();
        $configFile = "";//htmlspecialchars($exception->getConfigIdentifier());
        $pageKey    = "";//htmlspecialchars($exception->getPageKey());
        $entityName = "";//htmlspecialchars($exception->getEntityName());

        $errorListHtml = '';
        foreach ($errors as $index => $error) {
            $errorListHtml .= '<li class="error-item">' . htmlspecialchars($error) . '</li>';
        }

        $xxxx = count($errors);
        $errorListHtml = <<<HTML
                    <ul class="error-list">
                        {$errorListHtml}
                    </ul>
        HTML;


        //$xxx == $this->xxx()


        return <<<HTML
        <div class="container">
            <div class="header">
                <h5>
                    <span class="icon">⚠️</span>
                    2 Configuration Validation Failed
                </h5>
                <p>Your form configuration contains errors that must be fixed before the page can render.</p>
            </div>

            <div class="content">
                <div class="meta-info">
                    <p><strong>📄 Config File:</strong> {$configFile}</p>
                    <p><strong>📝 Page Key:</strong> {$pageKey}</p>
                    <p><strong>🏷️ Entity:</strong> {$entityName}</p>
                </div>

                <div class="error-section">
                    <h5>🔴 Errors Found ({$xxxx})</h5>
                    <ul class="error-list">
                        {$errorListHtml}
                    </ul>
                </div>
            </div>

            <div class="footer">
                💡 Fix these issues in your configuration file and refresh the page.
            </div>
        </div>
    HTML;
    }
}
