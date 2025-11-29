<?php

declare(strict_types=1);

namespace Core\Form\Renderer;

use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;
use Core\I18n\I18nTranslator;
use Core\Services\FormatterService;
use Core\Services\ThemeServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract form renderer with framework-agnostic rendering logic.
 *
 * ✅ Contains shared logic for all form renderers (Bootstrap, Material, Vanilla).
 * ✅ Child classes only implement framework-specific HTML output.
 */
abstract class AbstractFormRenderer implements FormRendererInterface
{
    /**
     * Default rendering options shared across all form renderers.
     *
     * @var array<string, mixed>
     */
    protected array $defaultOptions = [
        'error_display' => 'inline',        // 'inline' or 'summary'
        'html5_validation' => false,         // Enable native HTML5 validation
        'show_constraint_hints' => true,     // Show field constraint hints
        'layout_type' => 'sequential',       // 'sequential', 'fieldsets', 'sections'
        'submit_text' => 'Submit',
        'submit_class' => 'btn btn-primary',
    ];

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
    }




    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////



    // ✅ NEW: Template Method - orchestrates the form rendering flow (framework-agnostic)
    /**
     * Renders the entire form using the Template Method pattern.
     * This method defines the overall flow and delegates HTML generation to abstract methods.
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string The complete rendered HTML for the form
     */
    public function renderForm(FormInterface $form, array $options = []): string
    {
        // Merge options: defaultOptions -> form's render options -> method-provided options
        $options = $this->mergeOptions($form, $options);

        $output = '';

        $cardClass = $this->themeService->getElementClass('card');
        $output = '<div class="' . $cardClass . '">';

        // Step 1: Render the form's header (e.g., title, AJAX spinner) - outside <form> tag
        $output .= $this->renderHeader($form, $options);

        $cardBodyClass = $this->themeService->getElementClass('card.body');
        $output .= '<div class="' . $cardBodyClass . '">';

        // Step 2: Render the opening <form> tag and hidden fields (CSRF, etc.)
        $output .= $this->renderStartTag($form, $options);

        // Step 3: Render the main body content (errors, visible fields, captcha)
        $output .= $this->renderBodyContent($form, $options);

        // Step 4: Render the action buttons (submit, cancel)
        $output .= $this->renderButtons($form, $options);

        // Step 5: Render any notification/draft features
        $output .= $this->renderDraftNotification($options);

        // Step 6: Render the closing </form> tag (and any final wrapper elements)
        $output .= $this->renderEndTag($form, $options);

        $output .= '</div>';
        $output .= '</div>';


        return $output;
    }

    // ✅ NEW: Abstract method - child renderers implement framework-specific header HTML
    /**
     * Renders the form's header section (typically outside the <form> tag).
     * May include the form title, AJAX spinner, or other global status indicators.
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the form header
     */
    abstract protected function renderHeader(FormInterface $form, array $options): string;

    // ✅ NEW: Abstract method - child renderers implement framework-specific start tag HTML
    /**
     * Renders the opening <form> tag and any initial hidden fields (e.g., CSRF token).
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the form start
     */
    abstract protected function renderStartTag(FormInterface $form, array $options): string;

    // ✅ NEW: Abstract method - child renderers implement framework-specific body HTML
    /**
     * Renders the main content within the <form> tag.
     * Includes errors, visible fields (or layout-based field groups), and captcha.
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the form body content
     */
    abstract protected function renderBodyContent(FormInterface $form, array $options): string;

    // ✅ NEW: Abstract method - child renderers implement framework-specific button HTML
    /**
     * Renders the form's action buttons (e.g., submit, cancel).
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the form buttons
     */
    abstract protected function renderButtons(FormInterface $form, array $options): string;

    // ✅ NEW: Abstract method - child renderers implement framework-specific end tag HTML
    /**
     * Renders the closing </form> tag and any final wrapper elements.
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the form end
     */
    abstract protected function renderEndTag(FormInterface $form, array $options): string;

    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////


    //  * @param string $formName The name of the form
    // ✅ KEPT: Existing abstract methods that child renderers already implement
    /**
     * Renders a single field of the form.
     *
     * @param string $pageName The current page name for translation context
     * @param FieldInterface $field The field to render
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the field
     */
    abstract public function renderField(
        // string $formName,
        string $pageName,
        FieldInterface $field,
        array $options = []
    ): string;

    //  * @param string|null $fieldName Optional field name to render errors for
        // ?string $fieldName = null
    /**
     * Renders error messages for the form or a specific field.
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the errors
     */
    abstract public function renderErrors(
        FormInterface $form,
        array $options = [],
    ): string;

    /**
     * {@inheritdoc}
     *
     * @param string $pageName The current page/form context name for translation.
     * @param FieldInterface $field The field for which to render the hints.
     * @param array{
     *     always: array<int, array{
     *         icon: string,
     *         text: string,
     *         replacements: array<string, string|int|float|bool|null>,
     *         class: string
     *     }>,
     *     on_focus: array<int, array{
     *         icon: string,
     *         text: string,
     *         replacements: array<string, string|int|float|bool|null>,
     *         class: string
     *     }>
     * } $hints Categorized hints ['always' => [...], 'on_focus' => [...]]
     * @return string The HTML string for the constraint hints.
     */
    abstract protected function renderConstraintHintsHtml(
        string $pageName,
        FieldInterface $field,
        array $hints
    ): string;


    /**
     * ✅ ABSTRACT METHOD: Child renderers implement framework-specific draft notification HTML.
     *
     * @return string Framework-specific HTML for draft notification
     */
    abstract protected function renderDraftNotificationHtml(): string;


    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////

    /**
     * C: Generate constraint hints with visibility tiers.
     *
     * This method is shared by all form renderers (Bootstrap, Material, Vanilla).
     * It uses the translator to get localized hint messages.
     *
     * @param FieldInterface $field The field to generate hints for
     * @param string $pageName The page/form context for translation lookup
     * @return array<string, array<int, array<string, mixed>>> Categorized hints ['always' => [...],
     *                      'on_focus' => [...]]
     */
    protected function generateConstraintHints(FieldInterface $field, string $pageName): array
    {
        $alwaysVisible = []; // High-visibility hints (always shown)
        $onFocus = [];       // Focus-based hints (shown on field focus)


        $hints = [];
        $attrs = $field->getAttributes();
        $type = $field->getType();

        // ✅ ALWAYS VISIBLE: Required indicator
        if (!empty($attrs['required'])) {
            $alwaysVisible[] = [
                'icon' => '●',
                'text' => 'form.hints.required',
                'replacements' => [],
                'class' => 'constraint-required'
            ];
        }

        // ✅ ALWAYS VISIBLE: Text length constraints
        if (!empty($attrs['minlength'])) {
            $onFocus[] = [
                'icon' => '↓',
                'text' => 'form.hints.minlength',
                'replacements' => ['minlength' => $attrs['minlength']],
                'class' => 'constraint-minlength'
            ];
        }

        if (!empty($attrs['maxlength'])) {
            $onFocus[] = [
                'icon' => '↑',
                'text' => 'form.hints.maxlength',
                'replacements' => ['maxlength' => $attrs['maxlength']],
                'class' => 'constraint-maxlength'
            ];
        }

        // ✅ ALWAYS VISIBLE: Numeric constraints
        if (in_array($type, ['number', 'decimal', 'range'], true)) {
            if (!empty($attrs['min'])) {
                $alwaysVisible[] = [
                    'icon' => '≥',
                    'text' => 'form.hints.min',
                    'replacements' => ['min' => $attrs['min']],
                    'class' => 'constraint-min'
                ];
            }
            if (!empty($attrs['max'])) {
                $alwaysVisible[] = [
                    'icon' => '≤',
                    'text' => 'form.hints.max',
                    'replacements' => ['max' => $attrs['max']],
                    'class' => 'constraint-max'
                ];
            }
        }

        // ✅ ALWAYS VISIBLE: Date/Time range constraints
        if (in_array($type, ['date', 'datetime', 'month', 'week', 'time'], true)) {
            if (!empty($attrs['min'])) {
                $alwaysVisible[] = [
                    'icon' => '📅',
                    'text' => 'form.hints.date_min',
                    'replacements' => ['date_min' => $attrs['min']],
                    'class' => 'constraint-date-min'
                ];
            }
            if (!empty($attrs['max'])) {
                $alwaysVisible[] = [
                    'icon' => '📅',
                    'text' => 'form.hints.date_max',
                    'replacements' => ['date_max' => $attrs['max']],
                    'class' => 'constraint-date-max'
                ];
            }
        }

        // ⚠️ ON FOCUS: Pattern constraint (complex explanation)
        if (!empty($attrs['pattern'])) {
            $patternMsg = $attrs['pattern_message'] ??
                        'form.hints.pattern';
            $onFocus[] = [
                'icon' => '⚙',
                'text' => $patternMsg,
                'replacements' => [],
                'class' => 'constraint-pattern'
            ];
        }

        // ⚠️ ON FOCUS: Type-specific format hints
        if ($type === 'email') {
            $onFocus[] = [
                'icon' => '@',
                'text' => 'form.hints.email',
                'replacements' => [],
                'class' => 'constraint-email'
            ];
        }

        if ($type === 'tel') {
            $onFocus[] = [
                'icon' => '☎',
                'text' => 'form.hints.tel',
                'replacements' => [],
                'class' => 'constraint-tel'
            ];
        }

        if ($type === 'url') {
            $onFocus[] = [
                'icon' => '🔗',
                'text' => 'form.hints.url',
                'replacements' => [],
                'class' => 'constraint-url'
            ];
        }

        // if (empty($hints)) {
        //     return '';
        // }

        // ✅ Return categorized hints
        return [
            'always' => $alwaysVisible,
            'on_focus' => $onFocus,
        ];
    }

    /*
     * Need phpdoc // todo
     */
    protected function getInformedValidationError(string $pageName, FieldInterface $field, string $error): ?string
    {
        $parts     = explode('.', $error);
        $validationFlag = array_slice($parts, -2, 1)[0];

        if ($validationFlag !== 'validation') {
            return null;
        }

        $errorType  = $parts[array_key_last($parts)];
        $attrs      = $field->getAttributes();
        $replacements = [];

        // Define error types that require a value from field attributes for replacement.
        // For these types, the attribute directly informs the message (e.g., "must be at least {minlength}").
        $attributeBasedErrorTypes = ['minlength', 'maxlength', 'min', 'max', 'step', 'pattern'];

        // Only add a replacement if the error type is attribute-based and the attribute exists.
        if (in_array($errorType, $attributeBasedErrorTypes, true) && isset($attrs[$errorType])) {
            $replacements[$errorType] = $attrs[$errorType];
        }

        // Log a warning if an attribute-based error type was expected but the corresponding attribute is missing.
        // This indicates a potential misconfiguration where a message expects a parameter it won't receive.
        if (in_array($errorType, $attributeBasedErrorTypes, true) && !isset($attrs[$errorType])) {
            $this->logger->warning(
                sprintf(
                    'Validation error key "%s" (attribute-based) found, but corresponding attribute "%s" not set in field "%s". ' .
                    'The translation might be incomplete or inaccurate.',
                    $errorType,
                    $errorType,
                    method_exists($field, 'getName') ? $field->getName() : 'unknown'
                )
            );
            // We still proceed, but the translation might just use the default message or show an empty placeholder.
        }

        // Pass the determined replacements (which will be empty for generic errors like 'invalid') to the translator.
        $translation = $this->translator->get($error, $replacements, $pageName); // FindMe - Validation translation

        return $translation;
    }


    /**
     * ✅ FRAMEWORK-AGNOSTIC: Render draft notification HTML.
     *
     * Used for auto-save/localStorage features.
     *
     * @param array<string, mixed> $options Rendering options
     * @return string HTML for draft notification
     */
    protected function renderDraftNotification(array $options): string
    {
        if (!empty($options['auto_save']) && !empty($options['use_local_storage'])) {
            return $this->renderDraftNotificationHtml();
        }
        return '';
    }


    /**
     * ✅ SHARED HELPER: Merge default options with form and method options.
     *
     * @param FormInterface $form
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function mergeOptions(FormInterface $form, array $options): array
    {
        return array_merge(
            $this->defaultOptions,
            $form->getRenderOptions(),
            $options
        );
    }
}
