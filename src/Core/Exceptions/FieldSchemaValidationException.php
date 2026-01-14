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
        $fullMessage = $message . " -- " . $this->devCode;
        if ($this->suggestion) {
            $fullMessage .= " -- Suggestion: " . $this->suggestion;
        }
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
    public function toHtmlHelp(
        FieldSchemaValidationException $exception
    ): string {
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




    // private function xxx () {
    //     $title = '';//$this->translator->get('dev_code.' . $context['dev_code'], pageName: 'xxxx');
    //     $title = '❌ <strong>Warning: ' . $context['dev_code'] . ' - ' . $title . '</strong>';
    //     // $fullErrorMessage = "";
    //     $line[] = $title;
    //     $line[] .= '✉️ <strong>Message :</strong> ' . $plainMessage ;
    //     $line[] .= '💡 <strong>Suggestions:</strong> ' . $context['suggestion'];
    //     if (isset($context['details'])) {
    //         $fullErrorMessage = '';
    //         foreach ($context['details'] as $key => $value) {
    //             if ($key ===  'title') {
    //                 //$line[$key] = '❌ ' . '<strong>Warning: ' . $value . '</strong>';
    //             } elseif ($key ===  'error') {
    //                 $line[$key] = "🔴 $key: " . '<strong>' . $value . '</strong>';
    //             } elseif ($key ===  'error_code') {
    //                 // $line[$key] = "🔴 $key: " . '<strong>' . $value . '</strong>';
    //             } elseif ($key ===  'error_dev_code') {
    //                 $line[$key] = "🔴 $key: " . $value;
    //             } elseif ($key ===  'type') {
    //                 $line[$key] = "📄 $key: " . $value;
    //             } elseif ($key ===  'entity') {
    //                 $line[$key] = "📄 <strong>$key:</strong> " . $value;
    //             } elseif ($key ===  'type') {
    //                 $line[$key] = "📄  $key: " . $value;
    //             } elseif ($key ===  'field') {
    //                 $line[$key] = "🔹 $key: " . $value;
    //             } elseif ($key ===  'configKey') {
    //                 // $line[$key] = "🔑 $key: " . $value;
    //             } elseif ($key ===  'fix') {
    //                 // $line[$key] = "💡 $key: " . $value;
    //             } elseif ($key ===  'suggestions') {
    //                 // $line[$key] = "💡 $key: " . $value;
    //             } elseif ($key ===  'msg') {
    //             } else {
    //                 $line[$key] = '📄 <strong>' . $key . ':</strong> ' . $value; // 📝 💡 Fix t 🏷️ 🆔 ! ⚠️
    //             }
    //         }
    //     }
    //     // 🏷️
    //     $line[] .= "<strong>Initiated by:</strong> {$callerFile} on line {$callerLine}";
    //     $line[] .=  str_repeat('──', 40);
    //     $fullErrorMessage .=  implode("\n <br />", $line);

    // }
    /////////////////
}
