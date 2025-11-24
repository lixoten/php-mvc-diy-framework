<?php

declare(strict_types=1);

namespace Core\View\Renderer;

use Core\Form\Field\FieldInterface;
use Core\I18n\I18nTranslator;
use Core\View\ViewInterface;
use Core\Services\FormatterService;
use Core\Services\ThemeServiceInterface;
use Psr\Log\LoggerInterface;
use Core\Exceptions\InvalidFormatterChainException; // ✅ ADDED

/**
 * Abstract View renderer with framework-agnostic rendering logic.
 *
 * Contains shared logic for all View renderers (Bootstrap, Material, Vanilla).
 * Child classes only implement framework-specific HTML output.
 */
abstract class AbstractViewRenderer implements ViewRendererInterface
{
    /**
     * Default rendering options shared across all View renderers.
     *
     * @var array<string, mixed>
     */
    protected array $defaultOptions = [
        'title'             => 'view.default.title',
        'show_actions'      => true,
        'show_action_edit'  => true,
        'show_action_delete'=> true,
        'edit_button_label' => 'Edit',
        'delete_button_label' => 'Delete',
        'back_button_label' => 'Back', // ✅ ADDED
        'edit_url'          => null, // Placeholder, should be provided by controller
        'delete_url'        => null, // Placeholder, should be provided by controller
        'back_url'          => null, // Placeholder, should be provided by controller
        'layout_type'       => 'sections', // 'sections' (default), 'single_column'
    ];

    /**
     * ✅ Enable strict mode for formatter validation.
     * When true, invalid formatter chains will throw an exception instead of just logging a warning.
     * Override this in child classes or set via environment variable for per-environment control.
     *
     * @var bool
     */
    protected bool $strictFormatterValidation = false;

    /**
     * Constructor with dependency injection.
     *
     * @param ThemeServiceInterface $themeService
     * @param I18nTranslator $translator
     * @param FormatterService $formatterService
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected ThemeServiceInterface $themeService,
        protected I18nTranslator $translator,
        protected FormatterService $formatterService,
        protected LoggerInterface $logger
    ) {
        // TODO: Use ConfigService for strict formatter validation (single source of truth)
        // For now, using $_ENV or $_SERVER as per current pattern in AbstractListRenderer
        $env = $_ENV['STRICT_FORMATTER_VALIDATION'] ?? $_SERVER['STRICT_FORMATTER_VALIDATION'] ?? null;
        if ($env !== null) {
            $this->strictFormatterValidation = ($env === '1' || strtolower($env) === 'true');
        }
    }

    /**
     * {@inheritdoc}
     *
     * Renders the entire View using the Template Method pattern.
     * This method defines the overall flow and delegates HTML generation to abstract methods.
     */
    public function renderView(ViewInterface $view, array $options = []): string
    {
        // Merge options: defaultOptions -> view's render options -> method-provided options
        $options = $this->mergeOptions($view, $options);

        $output = '';

        $cardClass = $this->themeService->getElementClass('card');
        $output .= '<div class="' . $cardClass . '">';

        // Step 1: Render the view's header (e.g., title)
        $output .= $this->renderHeader($view, $options);

        $cardBodyClass = $this->themeService->getElementClass('card.body');
        $output .= '<div class="' . $cardBodyClass . '">';

        // Step 2: Render the main body content (fields, arranged by layout)
        $output .= $this->renderBodyContent($view, $options);

        $output .= '</div>'; // Close card.body

        // Step 3: Render the action buttons (e.g., edit, delete)
        $output .= $this->renderActions($view, $options);

        $output .= '</div>'; // Close card

        return $output;
    }

    // --- Abstract Methods (to be implemented by concrete renderers) ---

    /**
     * Renders the View's header section, typically including the title.
     *
     * @param ViewInterface $view The View instance.
     * @param array<string, mixed> $options Rendering options.
     * @return string Framework-specific HTML for the View header.
     */
    abstract protected function renderHeader(ViewInterface $view, array $options): string;

    /**
     * Renders the main content within the View, typically fields arranged by layout.
     *
     * @param ViewInterface $view The View instance.
     * @param array<string, mixed> $options Rendering options.
     * @return string Framework-specific HTML for the View body content.
     */
    abstract protected function renderBodyContent(ViewInterface $view, array $options): string;

    /**
     * Renders the action buttons for the view (e.g., Edit, Delete, Back).
     *
     * @param ViewInterface $view The View instance.
     * @param array<string, mixed> $options Rendering options.
     * @return string Framework-specific HTML for the View actions.
     */
    abstract protected function renderActions(ViewInterface $view, array $options): string;

    /**
     * {@inheritdoc}
     */
    abstract public function renderField(ViewInterface $view, FieldInterface $field, array $options = []): string;

    /**
     * {@inheritdoc}
     */
    abstract public function renderLayoutSection(ViewInterface $view, array $section, array $options = []): string;

    // --- Common Helper Methods (Framework-agnostic) ---

    /**
     * {@inheritdoc}
     *
     * Renders a field's value with appropriate formatting using the FormatterService.
     */
    public function renderValue(string $fieldName, mixed $value, array $recordData, array $fieldDef): string
    {
        if ($value === null) {
            return '';
        }

        $formattersConfig = $fieldDef['formatters'] ?? [];

        // Validate formatter chains to prevent accidental incompatibilities
        if (is_array($formattersConfig) && count($formattersConfig) > 1) {
            $this->validateFormatterChain($fieldName, $formattersConfig);
        }

        if (is_array($formattersConfig)) {
            foreach ($formattersConfig as $formatterDefinition) {
                // Ensure $formatterDefinition is an array with a 'name' key.
                if (!is_array($formatterDefinition) || !isset($formatterDefinition['name'])) {
                    $this->logger->error(sprintf(
                        'Malformed formatter definition for field "%s". Expected array with "name" key. Got: %s',
                        $fieldName,
                        json_encode($formatterDefinition)
                    ));
                    continue; // Skip malformed formatter config
                }

                $formatterName = $formatterDefinition['name'];
                $formatterOptions = $formatterDefinition; // Start with the full definition
                unset($formatterOptions['name']); // Remove 'name' from options passed to formatter service

                try {
                    // Resolve dynamic options if an 'options_provider' is defined.
                    if (isset($formatterOptions['options_provider'])) {
                        $provider = $formatterOptions['options_provider'];
                        if (is_callable($provider)) {
                            // Call the provider (e.g., [TestyStatus::class, 'getFormatterOptions'])
                            // passing the current field value and the full record.
                            $resolvedOptions = call_user_func($provider, $value, $recordData);
                            // Merge resolved options into existing 'options' key, or create it.
                            if (!isset($formatterOptions['options']) || !is_array($formatterOptions['options'])) {
                                $formatterOptions['options'] = [];
                            }
                            $formatterOptions['options'] = array_merge($formatterOptions['options'], $resolvedOptions);
                        } else {
                            $this->logger->warning(sprintf(
                                'Formatter options_provider for field "%s", formatter "%s" is not callable.',
                                $fieldName,
                                $formatterName
                            ));
                        }
                    } elseif (isset($formatterOptions['options']) && is_callable($formatterOptions['options'])) {
                         // ⚠️ EXCEPTION: Simple, one-off data providers in config (arrow functions)
                        // This handles the user-defined exception case for arrow functions in config instructions.
                        $resolvedOptions = call_user_func($formatterOptions['options'], $value, $recordData);
                        // Merge resolved options into existing 'options' key.
                        if (!isset($formatterOptions['options']) || !is_array($formatterOptions['options'])) {
                            $formatterOptions['options'] = [];
                        }
                        $formatterOptions['options'] = array_merge($formatterOptions['options'], $resolvedOptions);
                    }

                    // Apply each formatter in sequence with the (possibly updated) options
                    $value = $this->formatterService->format($formatterName, $value, $formatterOptions['options'] ?? []);
                } catch (\Core\Exceptions\FormatterNotFoundException $e) {
                    $this->logger->warning(sprintf(
                        'Formatter "%s" not found for field "%s". Error: %s',
                        $formatterName,
                        $fieldName,
                        $e->getMessage()
                    ));
                    // Continue with the current value
                } catch (\Throwable $e) {
                    $this->logger->error(sprintf(
                        'Error applying formatter "%s" to field "%s": %s',
                        $formatterName,
                        $fieldName,
                        $e->getMessage()
                    ));
                    // Continue with the current value
                }
            }
            return (string)$value;
        }

        // Apply default 'text' formatter for fields without explicit formatters
        try {
            return $this->formatterService->format('text', $value);
        } catch (\Core\Exceptions\FormatterNotFoundException $e) {
            $this->logger->error('Default "text" formatter not found! This is a critical configuration error.');
            // Fallback to raw htmlspecialchars if text formatter is missing
            return is_string($value) ? htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8') : (string)$value;
        }
    }


    /**
     * Validate a formatter chain to prevent common configuration mistakes.
     *
     * This method checks if HTML-producing formatters (like 'badge', 'image_link')
     * are followed by HTML-escaping formatters (like 'text'), which would break the output.
     *
     * @param string $fieldName The field name being validated.
     * @param array<int, array<string, mixed>> $formattersConfig The formatters configuration for this field.
     * @return void
     * @throws InvalidFormatterChainException If strict validation is enabled and an invalid chain is found.
     */
    protected function validateFormatterChain(string $fieldName, array $formattersConfig): void
    {
        $formatterNames = [];
        foreach ($formattersConfig as $config) {
            if (is_array($config) && isset($config['name'])) {
                $formatterNames[] = $config['name'];
            }
        }

        $htmlProducingFormatters = [
            'badge',       // Produces <span class="badge">
            'image_link',  // Produces <a><img></a>
            'link',        // Produces <a href="">
            // Add future HTML formatters here as you create them
        ];

        $htmlEscapingFormatters = [
            'text',        // Escapes all HTML via htmlspecialchars()
            'truncate',    // May escape if it inherits from TextFormatter behavior
            // Add future escaping formatters here
        ];

        $htmlFormattersInChain = array_intersect($htmlProducingFormatters, $formatterNames);
        $escapingFormattersInChain = array_intersect($htmlEscapingFormatters, $formatterNames);

        if (!empty($htmlFormattersInChain) && !empty($escapingFormattersInChain)) {
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
            if ($firstHtmlFormatterIndex !== null && $firstEscapingFormatterIndex !== null && $firstHtmlFormatterIndex < $firstEscapingFormatterIndex) {
                $htmlFormatterName = $formatterNames[$firstHtmlFormatterIndex];
                $escapingFormatterName = $formatterNames[$firstEscapingFormatterIndex];

                $errorMessage = sprintf(
                    'Invalid formatter chain for field "%s": ' .
                    'Formatter "%s" (HTML-producing) is followed by "%s" (HTML-escaping). ' .
                    'The "%s" formatter will escape the HTML produced by "%s", causing it to display as plain text. ' .
                    'Solution: Remove "%s" from the formatters array, or move it BEFORE "%s" in your field config.',
                    $fieldName,
                    $htmlFormatterName,
                    $escapingFormatterName,
                    $escapingFormatterName,
                    $htmlFormatterName,
                    $escapingFormatterName,
                    $htmlFormatterName
                );

                if ($this->strictFormatterValidation) {
                    throw new InvalidFormatterChainException($errorMessage);
                }

                $this->logger->warning('⚠️ CONFIGURATION WARNING: ' . $errorMessage);
            }
        }
    }

    /**
     * Helper to find the first field of a specific type within the view's field definitions.
     *
     * @param ViewInterface $view The view instance.
     * @param string $type The field type to look for (e.g., 'image', 'title').
     * @return string|null The field name if found, null otherwise.
     */
    protected function findFirstFieldOfType(ViewInterface $view, string $type): ?string
    {
        foreach ($view->getFields() as $name => $field) {
            if ($field->getType() === $type) {
                return $name;
            }
            // Fallback: Check field name for common patterns (e.g., 'image_url' for 'image' type)
            // This is a heuristic and might need refinement based on your naming conventions.
            if (strpos($name, $type) !== false) {
                return $name;
            }
        }
        return null;
    }

    /**
     * Get action button CSS class from ThemeService.
     * This logic is framework-agnostic as it delegates to the ThemeService abstraction.
     *
     * @param string $actionName The action name (e.g., 'edit', 'delete', 'back').
     * @return string The CSS class for the button.
     */
    protected function getActionButtonClass(string $actionName): string
    {
        return $this->themeService->getElementClass('button.' . $actionName);
    }

    /**
     * Helper to merge default options with view's render options and method-provided options.
     *
     * @param ViewInterface $view The view instance.
     * @param array<string, mixed> $options Method-specific options.
     * @return array<string, mixed> The merged options.
     */
    protected function mergeOptions(ViewInterface $view, array $options): array
    {
        return array_merge(
            $this->defaultOptions,
            $view->getRenderOptions(),
            $options
        );
    }
}
