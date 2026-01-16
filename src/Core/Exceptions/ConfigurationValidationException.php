<?php


declare(strict_types=1);

namespace Core\Exceptions;

use Core\I18n\I18nTranslator;
use Exception;

/**
 * Exception thrown when form configuration validation fails.
 *
 * Provides structured error reporting with:
 * - Clear error messages grouped by section
 * - Line-by-line error output
 * - Context information (config file, page, entity)
 */
class ConfigurationValidationException extends Exception
{
    private array $errors;
    private string $configIdentifier;
    private string $pageKey;
    private string $entityName;

    /**
     * @param array<string> $errors Array of validation error messages
     * @param string $configIdentifier Config file path (e.g., 'Testy/Config/testy_edit_view.php')
     * @param string $pageKey Page identifier (e.g., 'testy_edit')
     * @param string $entityName Entity name (e.g., 'testy')
     */
    public function __construct(
        protected I18nTranslator $translator,
        array $errors,
        string $configIdentifier,
        string $pageKey,
        string $entityName
    ) {
        $this->errors = $errors;
        $this->configIdentifier = $configIdentifier;
        $this->pageKey = $pageKey;
        $this->entityName = $entityName;

        // ✅ Build a clean, readable error message
        // $message = "wtffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff".$this->buildErrorMessage();
        $message = "Configuration Validation Failed";

        parent::__construct($message);
    }

    /**
     * Builds a structured, human-readable error message.
     */
    private function buildErrorMessage(): string
    {
        $lines = [];
        $lines[] = "❌ Form 3 Configuration Validation Failed";
        $lines[] = "";
        $lines[] = "📄 Config File: {$this->configIdentifier}";
        $lines[] = "📝 Page Key: {$this->pageKey}";
        $lines[] = "🏷️  Entity: {$this->entityName}";
        $lines[] = "";
        $lines[] = "🔴 Errors Found:";
        $lines[] = "";

        foreach ($this->errors as $index => $error) {
            if (is_string($error)) {
                $lines[] = "  " . ($index + 1) . ". " . $error;
            }
            if (is_array($error)) {
                // $rrr = implode("\n", $error);
                $lines[] = "  " . ($index + 1) . ". " . $error['message'];
            }
        }

        $lines[] = "";
        $lines[] = "💡 Fix these issues in your configuration file and try again.";

        return implode("\n", $lines);
    }

    /**
     * Get raw error array.
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get config identifier.
     */
    public function getConfigIdentifier(): string
    {
        return $this->configIdentifier;
    }

    /**
     * Get page key.
     */
    public function getPageKey(): string
    {
        return $this->pageKey;
    }

    /**
     * Get entity name.
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    ////////////////
    public function toHtmlHelp(): string {
        $errors     = $this->getErrors();
        $configFile = htmlspecialchars($this->getConfigIdentifier());
        $pageKey    = htmlspecialchars($this->getPageKey());
        $entityName = htmlspecialchars($this->getEntityName());

        $errorListHtml = '';
        foreach ($errors as $index => $error) {
            //if (is_string($error)) {
                $errorListHtml .= '<li class="error-item">' . htmlspecialchars("dd'dd'dd") . '</li>'; // ERROR is not an array
            //}

            if (is_array($error)) {
                $xxx = $this->xxx($error);
                $errorListHtml .= '<li class="error-item">' . $xxx . '</li>';
            }

        }
        $xxxx = count($errors);
        $errorListHtml = <<<HTML
                    <ul class="error-list">
                        {$errorListHtml}
                    </ul>
        HTML;


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


    private function xxx(array $context): string {
        $title = $this->translator->get('dev_code.' . $context['dev_code'], pageName: 'xxxx');
        $title = '❌ <strong>Critical: ' . $context['dev_code'] . ' - ' . $title . '</strong>';
        $line[] = $title;
        $line[] .= $this->bullet('✉️', 'Message', $this->formatTextHighlightKeywords($context['message']));
        $line[] .= $this->bullet('💡', 'Suggestions', $context['suggestion']);
        if (isset($context['details'])) {
            foreach ($context['details'] as $key => $value) {
                if ($key ===  'title') {
                    //$line[$key] = '❌ ' . '<strong>Warning: ' . $value . '</strong>';
                } elseif ($key ===  'error') {
                    $line[$key] = "🔴 $key: " . '<strong>' . $value . '</strong>';
                } elseif ($key ===  'error_code') {
                    // $line[$key] = "🔴 $key: " . '<strong>' . $value . '</strong>';
                } elseif ($key ===  'error_dev_code') {
                    $line[$key] = "🔴 $key: " . $value;
                } elseif ($key ===  'type') {
                    $line[$key] = "📄 $key: " . $value;
                } elseif ($key ===  'entity') {
                    $line[] .= $this->bullet('📄', $key, $value);
                } elseif ($key ===  'type') {
                    $line[$key] = "📄  $key: " . $value;
                } elseif ($key ===  'field') {
                    $line[$key] = "🔹 $key: " . $value;
                } elseif ($key ===  'configKey') {
                    // $line[$key] = "🔑 $key: " . $value;
                } elseif ($key ===  'fix') {
                    // $line[$key] = "💡 $key: " . $value;
                } elseif ($key ===  'suggestions') {
                    // $line[$key] = "💡 $key: " . $value;
                } elseif ($key ===  'msg') {
                } else {
                    // $line[$key] = '📄 <strong>' . $key . ':</strong> ' . $value; // 📝 💡 Fix t 🏷️ 🆔 ! ⚠️
                    $line[] .= $this->bullet('📄', $key, $value);
                }
            }
        }
        $line[] .=  str_repeat('──', 40);
        $rrr = implode("\n <br />", $line);
        return $rrr;
    }

    private function bullet(string $icon, string $label, string $text): string{
        return "─ $icon <strong>$label:</strong> $text";
    }

    /**
     * Helper to format text: applies HTML escaping and bolds content within single quotes.
     * Example: "Invalid value for 'max_size'." becomes "Invalid value for <strong>max_size</strong>."
     *
     * @param string $text The raw message text to format.
     * @return string The HTML formatted text.
     */
    private function formatTextHighlightKeywords(string $text): string
    {
        // 1. Sanitize the string.
        // This turns <script> into safe text.
        $safeText = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 2. Use a Regex that looks for BOTH possible single-quote entities:
        // &#039; OR &apos;
        $pattern = "/(&#039;|&apos;)(.*?)(&#039;|&apos;)/";

        $formattedText = preg_replace(
            $pattern,
            "<span style='font-weight: bold; color: red;'>$1$2$3</span>",
            $safeText
        );

        return $formattedText;
    }
}
