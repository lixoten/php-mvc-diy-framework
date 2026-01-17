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

    /**
     * Renders the form's header section (typically outside the <form> tag).
     * May include the form title, AJAX spinner, or other global status indicators.
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the form header
     */
    // abstract protected function renderHeader(FormInterface $form, array $options): string;
    protected function renderHeader(FormInterface $form, array $options): string
    {
        $output = '';

        // Render the form heading if configured
        $showTitleHeading = !empty($options['show_title_heading']);
        if ($showTitleHeading) {
            $headerClass = $this->themeService->getElementClass('card.header');
            $output .= '<div class="' . $headerClass . '">';

            // Resolve heading level, defaulting to 'h2'
            $headingLevelCandidate = $options['title_heading_level'] ?? 'h2';
            $headingLevel = (is_string($headingLevelCandidate) && preg_match('/^h[1-6]$/i', $headingLevelCandidate))
                            ? $headingLevelCandidate
                            : 'h2';

            $headingClass = $options['title_heading_class'] ?? $this->themeService->getElementClass('form.heading');
            $headingText  = $this->translator->get('form.title', pageName: $form->getPageName());

            $output .= "<{$headingLevel} class=\"{$headingClass}\">" .
                       $headingText .
                       "</{$headingLevel}>";
            $output .= '</div>';
        }

        // Render ajax_save_spinner here (global form status indicator)
        if (!empty($options['ajax_save'])) {
            // Translate the "Saving..." message
            $savingText = $this->translator->get('form.saving', pageName: $form->getPageName());

            // ✅ Use the ThemeService to get the fully rendered AJAX spinner HTML
            $output .= $this->themeService->getAjaxSpinnerHtml($savingText);
        }

        return $output;
    }




    // ✅ NEW: Abstract method - child renderers implement framework-specific start tag HTML
    /**
     * Renders the opening <form> tag and any initial hidden fields (e.g., CSRF token).
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the form start
     */
    // abstract protected function renderStartTag(FormInterface $form, array $options): string;
    protected function renderStartTag(FormInterface $form, array $options): string
    {
        $attrString = $this->buildFormAttributes($form, $options);
        $output = '<form' . $attrString . '>';

        // CSRF token
        $token = $form->getCSRFToken();
        $output .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';

        return $output;
    }



    /**
     * Renders the form's submit and optional cancel buttons.
     *
     * @param FormInterface<form> $options The rendering options.
     * @param array<string, mixed> $options The rendering options.
     * @return string Stringed attributes.
     */
    protected function buildFormAttributes(FormInterface $form, array $options): string
    {
        $attributes = $form->getAttributes();

        // Set default method if not provided
        if (!isset($attributes['method'])) {
            $attributes['method'] = 'post';
        }

        //---------------------------------------------------------------------

        // Use action_url from render options if available (injected by controller)
        // Falls back to form's action attribute if not set via render options
        if (!empty($options['action_url'])) {
            $attributes['action'] = $options['action_url'];
        } elseif (!isset($attributes['action'])) {
            // Fallback to current URL if no action is specified anywhere
            $attributes['action'] = '';
        }

        //---------------------------------------------------------------------

        // Add enctype if any field is of type 'file'
        foreach ($form->getFields() as $field) {
            if ($field->getType() === 'file') {
                $attributes['enctype'] = 'multipart/form-data';
                break;
            }
        }

        //---------------------------------------------------------------------

        // SearchFor 'ajax_update_url'
        // //  AJAX attributes if provided via render options
        // if (!empty($options['ajax_update_url'])) {
        //     $attributes['data-ajax-action'] = $options['ajax_update_url'];
        // } elseif (!empty($options['ajax_store_url'])) {
        //     $attributes['data-ajax-action'] = $options['ajax_store_url'];
        // }

        //---------------------------------------------------------------------

        // Include HTML attributes from options
        $htmlAttributes = $options['attributes'] ?? [];
        if (!empty($htmlAttributes)) {
            $attributes = array_merge($attributes, $htmlAttributes);
        }

        if (!empty($options['ajax_save'])) { // js-feature
            $attributes['data-ajax-save'] = 'true';
        }
        if (!empty($options['auto_save'])) { // js-feature
            $attributes['data-auto-save'] = 'true';
        }
        if (!empty($options['use_local_storage'])) { // js-feature
            $attributes['data-use-local-storage'] = 'true';
        }

        //---------------------------------------------------------------------

        // Handle direct HTML attributes like onsubmit (ADD THIS CODE)
        $directAttributes = ['onsubmit', 'onclick', 'onchange', 'onblur', 'onfocus'];
        foreach ($directAttributes as $attr) {
            if (isset($options[$attr])) {
                $attributes[$attr] = $options[$attr];
            }
        }

        //---------------------------------------------------------------------

        // Theme
        $themeClass = $options['css_form_theme_class'] ?? '';
        if ($themeClass) {
            if (!isset($attributes['class'])) {
                $attributes['class'] = $themeClass;
            } else {
                $attributes['class'] .= ' ' . $themeClass;
            }
        }

        //---------------------------------------------------------------------

        // Bootstrap validation class
        $existingClass = trim($attributes['class'] ?? '');
        $validationClass = $this->themeService->getElementClass('form.validation') ?? null;

        if (!empty($validationClass)) { // Only apply if theme provides a validation class
            if ($existingClass === '') {
                $attributes['class'] = $validationClass;
            } else {
                // Ensure exact token match, avoid partial matches
                if (!preg_match('/\b' . preg_quote($validationClass, '/') . '\b/', $existingClass)) {
                    $attributes['class'] = $existingClass . $validationClass;
                } else {
                    $attributes['class'] = $existingClass; // keep normalized value
                }
            }
        }

        //---------------------------------------------------------------------

        // novalidate attribute for custom validation
        if (!($options['html5_validation'] ?? false)) {
            $attributes['novalidate'] = '';
        }

        //---------------------------------------------------------------------

        // Build attribute string
        $attrString = '';
        foreach ($attributes as $name => $value) {
            if ($value === '') {
                $attrString .= ' ' . $name;
            } else {
                $attrString .= ' ' . $name . '="' . htmlspecialchars((string)$value) . '"';
            }
        }

        return $attrString;
    }




    // ✅ NEW: Abstract method - child renderers implement framework-specific body HTML
    /**
     * Renders the main content within the <form> tag.
     * Includes errors, visible fields (or layout-based field groups), and captcha.
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the form body content
     */
    // abstract protected function renderBodyContent(FormInterface $form, array $options): string;
    protected function renderBodyContent(FormInterface $form, array $options): string
    {
        $output = '';
        $pageName = $form->getPageName();

        // Render hidden fields first
        foreach ($form->getFields() as $field) {
            if ($field->getType() === 'hidden') {
                $output .= $this->renderField(
                    // $form->getPageKey(),
                    $form->getPageName(),
                    $field,
                    $options
                );
                // Note: RRR means reset type to 'display' so it's not removed after rendering as hidden.
                // Commented out for now as this behavior needs clarification:
                // $field->setType('display');
            }
        }

        $errorDisplay = $options['error_display'] ?? 'inline';


        if ($errorDisplay === 'summary') {
            // Render all errors (including field errors) in summary at the top
            $output .= $this->renderErrors($form, $options);
            // Set flag to prevent duplicate inline errors on individual fields
            $options['hide_inline_errors'] = true;
        } else {
            // Render only form-level errors before fields (field errors are inline)
            $output .= $this->renderErrors($form, $options);
        }


        // ✅ Delegate layout rendering to helper method
        $output .= $this->renderLayoutFields($form, $options);

        // Render CAPTCHA in a consistent place before submit button
        if ($form->isCaptchaRequired()) {
            $captchaFieldName = 'captcha';
            $output .= '<div class="security-wrapper mb-4">';
            $output .= '<h5 class="security-heading mb-3">' .
                       $this->translator->get('common.security.verification', pageName: $pageName).
                       '</h5>';
            $output .= $this->renderField(
                // $form->getName(),
                $pageName,
                $form->getField($captchaFieldName),
                $options
            );
            $output .= '</div>';
        }

        return $output;
    }


    /**
     * ✅ NEW HELPER METHOD: Renders fields based on layout configuration.
     * Supports 'fieldsets', 'sections', and 'sequential' layout types.
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Bootstrap HTML for the layout
     */
    protected function renderLayoutFields(FormInterface $form, array $options): string
    {
        $output = '';
        $pageName = $form->getPageName();
        $layout = $form->getLayout();
        $layout_type = $options['layout_type'] ?? 'sequential';

        if ($layout_type === 'fieldsets' && !empty($layout)) {
            $columns = count($layout);
            $columnClass = $columns > 1 ? 'row' : '';
            $output .= '<div class="' . $columnClass . '">';

            foreach ($layout as $fieldsetId => $fieldset) {
                $colWidth = $columns > 1 ? 'col-md-' . (12 / $columns) : '';
                $output .= '<div class="fieldset-container ' . $colWidth . '">';
                $fldId = $fieldset['id'] ?? "fieldset-" . htmlspecialchars("$fieldsetId");
                $output .= '<fieldset id="' . $fldId . '" class="mb-4">';

                if (!empty($fieldset['title'])) {
                    $output .= '<legend>' . htmlspecialchars($fieldset['title']) . '</legend>';
                }

                foreach ($fieldset['fields'] as $fieldName) {
                    if ($form->hasField($fieldName)) {
                        $output .= $this->renderField(
                            // $form->getName(),
                            $pageName,
                            $form->getField($fieldName),
                            $options
                        );
                    }
                }

                $output .= '</fieldset></div>';
            }
            $output .= '</div>';
        } elseif ($layout_type === 'sections' && !empty($layout)) {
            foreach ($layout as $sectionId => $section) {
                $title = $section['title'] ?? '';
                $secId = $section['id'] ?? "section-" . htmlspecialchars("$sectionId");
                $output .= '<h3 class="form-section-header my-3" id="section-' . $secId . '">' .
                    htmlspecialchars($title) . '</h3>';

                if (!empty($section['divider'])) {
                    $output .= '<hr class="form-divider my-4" style="border:2px solid red;">';
                }

                $fields = $section['fields'] ?? [];
                foreach ($fields as $fieldName) {
                    if ($form->hasField($fieldName)) {
                        $output .= $this->renderField(
                            // $form->getName(),
                            $pageName,
                            $form->getField($fieldName),
                            $options
                        );
                    }
                }
            }
        } else {
            // Default: Sequential rendering
            foreach ($layout as $setId => $set) {
                foreach ($set['fields'] as $fieldName) {
                    if ($form->hasField($fieldName) && $fieldName !== 'captcha') {
                        $output .= $this->renderField(
                            // $form->getName(),
                            $pageName,
                            $form->getField($fieldName),
                            $options
                        );
                    }
                }
            }
        }

        return $output;
    }




    /**
     * Renders the form's action buttons (e.g., submit, cancel).
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the form buttons
     */
    // abstract protected function renderButtons(FormInterface $form, array $options): string;
    protected function renderButtons(FormInterface $form, array $options): string
    {
        $output = '';

        // Render submit button if requested
        if (!isset($options['no_submit_button']) || !$options['no_submit_button']) {
            $buttonText = $this->translator->get('button.save', pageName: $form->getPageName());

            $buttonButtonVariant = $options['submit_button_variant'] ?? 'primary';
            $buttonClass = $this->themeService->getButtonClass($buttonButtonVariant);

            $output .= <<<HTML
                <div class="mb-3"><button type="submit" class="{$buttonClass}">{$buttonText}</button></div>
            HTML;

            //  Add Cancel/Back button if cancel_url is provided via render options
            if (!empty($options['cancel_url'])) {
                $buttonText = $this->translator->get('button.cancel', pageName: $form->getPageName());

                $buttonButtonVariant = $options['cancel_button_variant'] ?? 'secondary';
                $buttonClass = $this->themeService->getButtonClass($buttonButtonVariant) . ' ms-2';

                $url = htmlspecialchars($options['cancel_url']);
                $output .= <<<HTML
                    <a href="{$url}" class="{$buttonClass}">$buttonText</a>
                HTML;
            }
        }

        return $output;
    }


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

    /**
     * Renders error messages for the form or a specific field.
     *
     * @param FormInterface $form The form instance
     * @param array<string, mixed> $options Rendering options
     * @return string Framework-specific HTML for the errors
     */
    public function renderErrors(FormInterface $form, array $options = []): string
    {
        // Check if we're in summary mode
        $errorDisplay = $options['error_display'] ?? 'inline';
        $alertClass = match ($errorDisplay) {
            'summary' => $this->themeService->getElementClass('alert.danger.summary'),
            default => $this->themeService->getElementClass('alert.danger.inline'),
        };


        // DebugRt::j('1', '', $errorDisplay);
        if ($errorDisplay === 'summary') {
            // Collect ALL errors (both field and form level)
            $allErrors = [];

            // Collect field errors
            foreach ($form->getFields() as $field) {
                $fieldErrors = $field->getErrors();
                if (!empty($fieldErrors)) {
                    $fieldLabel = $field->getLabel();
                    foreach ($fieldErrors as $error) {
                        $informedError = $this->getInformedValidationError($form->getPageName(), $field, $error);

                        $allErrors[] = '<li><strong>' . htmlspecialchars($fieldLabel) . ':</strong> ' .
                                    htmlspecialchars($informedError) . '</li>';
                    }
                }
            }
            // Add form-level errors
            $formErrors = $form->getErrors('_form');
            foreach ($formErrors as $error) {
                $allErrors[] = '<li>' . htmlspecialchars($error) . '</li>';
            }

            // Only render if we have errors OR show_error_container is true
            if (!empty($allErrors) || ($options['show_error_container'] ?? false)) {
                $output = '<div class="' . $alertClass . '" role="alert">';
                if (!empty($allErrors)) {
                    $errorInstructions = $this->translator->get(
                        'form.error.instructions',
                        pageName: $form->getPageName()
                    );
                    $output .= "<h5>$errorInstructions</h5>";

                    $output .= '<ul>' . implode('', $allErrors) . '</ul>';
                } else {
                    $output .= '<p class="mb-0">No errors.</p>';
                }
                $output .= '</div>';
                return $output;
            }

            return '';
        } else {
            // Original code for inline errors - only form level
            $errors = $form->getErrors('_form');
            if (empty($errors)) {
                return '';
            }

            $output = '<div class="' . $alertClass . '" role="alert">';
            foreach ($errors as $error) {
                $output .= htmlspecialchars($error) . '<br>';
            }
            $output .= '</div>';

            return $output;
        }
    }

    /**
     * ✅ MOVED FROM BOOTSTRAP: Render constraint hints HTML (now framework-agnostic).
     *
     * @param string $pageName The current page/form context name for translation.
     * @param FieldInterface $field The field for which to render the hints.
     * @param array{
     *     always: array<int, array{icon: string, text: string, replacements: array<string, mixed>, class: string}>,
     *     on_focus: array<int, array{icon: string, text: string, replacements: array<string, mixed>, class: string}>
     * } $hints Categorized hints
     * @return string The HTML string for the constraint hints.
     */
    protected function renderConstraintHintsHtml(string $pageName, FieldInterface $field, array $hints): string
    {
        $fieldName = $field->getName();
        $html = '';

        // Get theme-specific classes with Bootstrap defaults as fallback
        $wrapperAlwaysClass = $this->themeService->getElementClass('constraints.wrapper_always');
        $wrapperFocusClass = $this->themeService->getElementClass('constraints.wrapper_focus');
        $listClass = $this->themeService->getElementClass('constraints.list');
        $itemClass = $this->themeService->getElementClass('constraints.item');
        $iconWrapperClass = $this->themeService->getElementClass('constraints.icon_wrapper'); // ✅ NEW: For icon spacing

        // ✅ Always-visible hints
        if (!empty($hints['always'])) {
            $html .= '<div class="' . $wrapperAlwaysClass . '" id="constraints-always-' .
                    htmlspecialchars($fieldName) . '" aria-live="polite">';
            $html .= '<ul class="' . $listClass . '">';

            foreach ($hints['always'] as $hint) {
                $translatedText = $this->translator->get($hint['text'], $hint['replacements'], pageName: $pageName);
                $html .= sprintf(
                    '<li class="%s %s"><span class="%s">%s</span><span class="constraint-text">%s</span></li>',
                    htmlspecialchars($itemClass),
                    htmlspecialchars($hint['class']),
                    htmlspecialchars($iconWrapperClass),
                    $hint['icon'], // DO NOT ESCAPE THIS!
                    $translatedText
                );
            }

            $html .= '</ul></div>';
        }

        // ⚠️ Focus-based hints
        if (!empty($hints['on_focus'])) {
            $html .= '<div class="' . $wrapperFocusClass . '" id="constraints-focus-' .
                    htmlspecialchars($fieldName) . '" aria-live="polite">';
            $html .= '<ul class="' . $listClass . '">';

            foreach ($hints['on_focus'] as $hint) {
                $translatedText = $this->translator->get(
                    $hint['text'],
                    $hint['replacements'] ?? [],
                    pageName: $pageName
                );
                $html .= sprintf(
                    '<li class="%s %s"><span class="%s">%s</span><span class="constraint-text">%s</span></li>',
                    htmlspecialchars($itemClass),
                    htmlspecialchars($hint['class']),
                    htmlspecialchars($iconWrapperClass),
                    $hint['icon'], // DO NOT ESCAPE THIS!
                    $translatedText
                );
            }

            $html .= '</ul></div>';
        }

        return $html;
    }


    /**
     * ✅ ABSTRACT METHOD: Child renderers implement framework-specific draft notification HTML.
     *
     * @return string Framework-specific HTML for draft notification
     */
    // abstract protected function renderDraftNotificationHtml(): string;
    protected function renderDraftNotificationHtml(): string
    {
        $notificationClass = $this->themeService->getElementClass('notification.draft') ?? 'alert alert-warning mt-3';
        $buttonClass = $this->themeService->getElementClass('notification.button') ?? 'btn btn-secondary btn-sm mt-2';

        $html  = '<div id="draft-notification" style="display:none;" class="' . $notificationClass . '"></div>';
        $html .= '<button type="button" id="discard-draft-btn" style="display:none;" ';
        $html .= 'class="' . $buttonClass . '">' .
                              $this->translator->get('form.restore_data_from_server', pageName: 'common') . '</button>';

        return $html;
    }


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
                'icon' => $this->themeService->getIconHtml('constraint_required'),
                'text' => 'form.hints.required',
                'replacements' => [],
                'class' => 'constraint-required'
            ];
        }

        // ✅ ALWAYS VISIBLE: Text length constraints
        if (!empty($attrs['minlength'])) {
            $onFocus[] = [
                'icon' => $this->themeService->getIconHtml('constraint_minlength'),
                'text' => 'form.hints.minlength',
                'replacements' => ['minlength' => $attrs['minlength']],
                'class' => 'constraint-minlength'
            ];
        }

        if (!empty($attrs['maxlength'])) {
            $onFocus[] = [
                'icon' => $this->themeService->getIconHtml('constraint_maxlength'),
                'text' => 'form.hints.maxlength',
                'replacements' => ['maxlength' => $attrs['maxlength']],
                'class' => 'constraint-maxlength'
            ];
        }

        // ✅ ALWAYS VISIBLE: Numeric constraints
        if (in_array($type, ['number', 'decimal', 'range'], true)) {
            if (!empty($attrs['min'])) {
                $alwaysVisible[] = [
                    'icon' => $this->themeService->getIconHtml('constraint_min'),
                    'text' => 'form.hints.min',
                    'replacements' => ['min' => $attrs['min']],
                    'class' => 'constraint-min'
                ];
            }
            if (!empty($attrs['max'])) {
                $alwaysVisible[] = [
                    'icon' => $this->themeService->getIconHtml('constraint_max'),
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
                    'icon' => $this->themeService->getIconHtml('constraint_date_min'),
                    'text' => 'form.hints.date_min',
                    'replacements' => ['date_min' => $attrs['min']],
                    'class' => 'constraint-date-min'
                ];
            }
            if (!empty($attrs['max'])) {
                $alwaysVisible[] = [
                    'icon' => $this->themeService->getIconHtml('constraint_date_max'),
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
                'icon' => $this->themeService->getIconHtml('constraint_pattern'),
                'text' => $patternMsg,
                'replacements' => [],
                'class' => 'constraint-pattern'
            ];
        }

        // ⚠️ ON FOCUS: Type-specific format hints
        if ($type === 'email') {
            $onFocus[] = [
                'icon' => $this->themeService->getIconHtml('constraint_email'),
                'text' => 'form.hints.email',
                'replacements' => [],
                'class' => 'constraint-email'
            ];
        }

        if ($type === 'tel') {
            $onFocus[] = [
                'icon' => $this->themeService->getIconHtml('constraint_tel'),

                'text' => 'form.hints.tel',
                'replacements' => [],
                'class' => 'constraint-tel'
            ];
        }

        if ($type === 'url') {
            $onFocus[] = [
                'icon' => $this->themeService->getIconHtml('constraint_url'),
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
            if ($field->getName() === $parts[0]) {
                unset($parts[0]);
                return implode('.', $parts);
            }
            return null;
        }

        $errorType  = $parts[array_key_last($parts)];
        $attrs      = $field->getAttributes();
        //$attrs      = $field->getAttributes();
        $replacements = [];

        if (isset($field->getValidators()['file'])) {
            $rrr = $field->getValidators()['file'];
            $maxSize = $field->getValidators()['file']['max_size'];
            $maxSizeMB = round($maxSize / 1048576, 2);
            $attrs['max_size'] = $maxSizeMB;
        }

        // Define error types that require a value from field attributes for replacement.
        // For these types, the attribute directly informs the message (e.g., "must be at least {minlength}").
        $attributeBasedErrorTypes = ['minlength', 'maxlength', 'min', 'max', 'step', 'pattern', 'max_size'];

        // Only add a replacement if the error type is attribute-based and the attribute exists.
        if ($errorType === 'enforce_step') {
            $errorType = 'step';
        }
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
