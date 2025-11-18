<?php

declare(strict_types=1);

namespace Core\List\Renderer;

use App\Helpers\DebugRt;
use Core\List\ListInterface;
use Core\Services\FormatterService;
use Core\Services\ThemeServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract list renderer with framework-agnostic rendering logic
 */
abstract class AbstractListRenderer implements ListRendererInterface
{
    /**
     * View type constants
     */
    public const VIEW_TABLE = 'table';
    public const VIEW_GRID = 'grid';
    public const VIEW_LIST = 'list';

    /**
     * Default options
     */
    protected array $defaultOptions = [
        'show_actions' => true,
        'show_pagination' => true,
        'show_view_toggle' => true,
        'view_type' => self::VIEW_TABLE,
        'add_button_label' => 'Add New',
    ];

    /**
     * ✅ NEW: Enable strict mode for formatter validation.
     * When true, invalid formatter chains will throw an exception instead of just logging a warning.
     * Override this in child classes or set via environment variable for per-environment control.
     *
     * @var bool
     */
    protected bool $strictFormatterValidation = false; // ✅ Default: false (warnings only)


    /**
     * Constructor
     *
     * @param ThemeServiceInterface $themeService
     * @param FormatterService $formatterService
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected ThemeServiceInterface $themeService,
        protected FormatterService $formatterService,
        protected LoggerInterface $logger
    ) {
        // Todo Change it to use configService  (single source of truth)
        // Use $_ENV instead of getenv() (Dotenv populates $_ENV by default)
        $env = $_ENV['STRICT_FORMATTER_VALIDATION'] ?? $_SERVER['STRICT_FORMATTER_VALIDATION'] ?? null;

        if ($env !== null) {
            $this->strictFormatterValidation = ($env === '1' || strtolower($env) === 'true');
        }
        // Todo use this: $this->strictFormatterValidation = $this->config->get('app.strict_formatter_validation', false);
    }

    /**
     * Render a full list
     */
    public function renderList(ListInterface $list, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $list->getRenderOptions(), $options);

        $cardClass = $this->themeService->getElementClass('card');

        $output = '<div class="' . $cardClass . '">';

        // Render header with title and add button
        $output .= $this->renderHeader($list, $options);

        // Render view toggle if enabled
        if ($options['show_view_toggle']) {
            $output .= $this->renderViewToggle($list, $options);
        }

        // Render body based on view type
        switch ($options['view_type']) {
            case self::VIEW_GRID:
                $output .= $this->renderGridView($list, $options);
                break;
            case self::VIEW_LIST:
                $output .= $this->renderListView($list, $options);
                break;
            default:
                $output .= $this->renderBody($list, $options);
                break;
        }

        // Render pagination if enabled
        if ($options['show_pagination'] && !empty($list->getPagination())) {
            $output .= $this->renderPagination($list, $options);
        }

        $output .= '</div>';

        return $output;
    }


    /**
     * Render column value with appropriate formatting
     *
     * @param string $column The column name
     * @param mixed $value The value to render
     * @param array<string, mixed> $record The complete record data
     * @param array<string, mixed> $columns Column definitions
     * @return string The formatted value as HTML
     */
    public function renderValue(string $column, $value, array $record, array $columns = []): string
    {
        if ($value === null) {
            return '';
        }

        $columnConfig = $columns[$column] ?? [];
        $formattersConfig = $columnConfig['formatters'] ?? [];

        // ✅ NEW: Validate formatter chains to prevent accidental incompatibilities
        if (is_array($formattersConfig) && count($formattersConfig) > 1) {
            $this->validateFormatterChain($column, $formattersConfig);
        }

        // Check if this column has explicit formatters (array-based config)
        if (isset($columnConfig['formatters']) && is_array($columnConfig['formatters'])) {
            // If explicit formatters are defined, the child renderer (e.g., BootstrapListRenderer)
            // has already applied them via FormatterService.
            // We trust that the FormatterService respected isSafeHtml() and handled escaping correctly.
            // Return the value as-is WITHOUT additional escaping.
            return (string)$value;
        }

        // ⚠️ LEGACY: Support old-style single 'formatter' closure (deprecated pattern)
        $columnOptions = $columnConfig['options'] ?? [];
        if (isset($columnOptions['formatter']) && is_callable($columnOptions['formatter'])) {
            return $columnOptions['formatter']($value, $record);
        }

        // Apply default 'text' formatter for columns without explicit formatters
        try {
            return $this->formatterService->format('text', $value);
        } catch (\Core\Exceptions\FormatterNotFoundException $e) {
            $this->logger->error('Default "text" formatter not found! This is a critical configuration error.');
            // Fallback to raw htmlspecialchars if text formatter is missing
            return is_string($value) ? htmlspecialchars($value) : (string)$value;
        }
    }


    /**
     * ✅ ENHANCED: Validate a formatter chain to prevent common configuration mistakes.
     *
     * This method checks if HTML-producing formatters (like 'badge', 'image_link')
     * are followed by HTML-escaping formatters (like 'text'), which would break the output.
     *
     * @param string $column The column name being validated
     * @param array<string, mixed> $formattersConfig The formatters configuration for this column
     * @return void
     */
    protected function validateFormatterChain(string $column, array $formattersConfig): void
    {
        $formatterNames = array_keys($formattersConfig);

        // ✅ NEW: Define formatters that produce safe HTML (isSafeHtml = true)
        // As you add more HTML-producing formatters, add them to this list.
        $htmlProducingFormatters = [
            'badge',       // Produces <span class="badge">
            'image_link',  // Produces <a><img></a>
            'link',        // Produces <a href="">
            // Add future HTML formatters here as you create them
        ];

        // ✅ NEW: Define formatters that escape HTML (isSafeHtml = false)
        // These formatters will break any HTML from previous formatters in the chain.
        $htmlEscapingFormatters = [
            'text',        // Escapes all HTML via htmlspecialchars()
            'truncate',    // May escape if it inherits from TextFormatter behavior
            // Add future escaping formatters here
        ];

        // Check if we have any HTML-producing formatter
        $htmlFormattersInChain = array_intersect($htmlProducingFormatters, $formatterNames);
        $escapingFormattersInChain = array_intersect($htmlEscapingFormatters, $formatterNames);

        // ✅ NEW: If both types exist in the chain, determine the order and warn
        if (!empty($htmlFormattersInChain) && !empty($escapingFormattersInChain)) {
            // Get the first occurrence of each type
            $firstHtmlFormatterIndex = null;
            $firstEscapingFormatterIndex = null;

            foreach ($formatterNames as $index => $name) {
                if (in_array($name, $htmlProducingFormatters, true) && $firstHtmlFormatterIndex === null) {
                    $firstHtmlFormatterIndex = $index;
                }
                if (in_array($name, $htmlEscapingFormatters, true) && $firstEscapingFormatterIndex === null) {
                    $firstEscapingFormatterIndex = $index;
                }
            }


            // ⚠️ PROBLEM: HTML formatter comes BEFORE escaping formatter
            if ($firstHtmlFormatterIndex < $firstEscapingFormatterIndex) {
                $htmlFormatterName = $formatterNames[$firstHtmlFormatterIndex];
                $escapingFormatterName = $formatterNames[$firstEscapingFormatterIndex];

                $errorMessage = sprintf(
                    'Invalid formatter chain for column "%s": ' .
                    'Formatter "%s" (HTML-producing) is followed by "%s" (HTML-escaping). ' .
                    'The "%s" formatter will escape the HTML produced by "%s", causing it to display as plain text. ' .
                    'Solution: Remove "%s" from the formatters array, or move it BEFORE "%s" in your field config file.',
                    $column,
                    $htmlFormatterName,
                    $escapingFormatterName,
                    $escapingFormatterName,
                    $htmlFormatterName,
                    $escapingFormatterName,
                    $htmlFormatterName
                );

                // ✅ NEW: Strict mode - throw exception to prevent app from running
                if ($this->strictFormatterValidation) {
                    throw new \Core\Exceptions\InvalidFormatterChainException($errorMessage);
                }

                // ⚠️ Non-strict mode - just log a warning
                $this->logger->warning('⚠️ CONFIGURATION WARNING: ' . $errorMessage);

            }
        }
    }


    /**
     * Helper to find the first field of a specific type
     *
     * @param array<string, mixed> $columns Column definitions
     * @param string $type The field type to look for
     * @return string|null The field name if found, null otherwise
     */
    protected function findFirstFieldOfType(array $columns, string $type): ?string
    {
        foreach ($columns as $name => $column) {
            $options = $column['options'] ?? [];
            $fieldType = $options['type'] ?? '';

            if ($fieldType === $type) {
                return $name;
            }

            // Check field name for common patterns
            if (strpos($name, $type) !== false || strpos($name, 'image') !== false) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Render view toggle buttons
     */
    abstract protected function renderViewToggle(ListInterface $list, array $options): string;

    /**
     * Render grid view with cards
     */
    abstract protected function renderGridView(ListInterface $list, array $options): string;

    /**
     * Render list view with full-width items
     */
    abstract protected function renderListView(ListInterface $list, array $options): string;
}
