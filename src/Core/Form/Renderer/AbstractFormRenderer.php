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

    /**
     * C: Generate constraint hints with visibility tiers.
     *
     * This method is shared by all form renderers (Bootstrap, Material, Vanilla).
     * It uses the translator to get localized hint messages.
     *
     * @param FieldInterface $field The field to generate hints for
     * @param string $pageKey The page/form context for translation lookup
     * @return array<string, array<int, array<string, mixed>>> Categorized hints ['always' => [...],
     *                      'on_focus' => [...]]
     */
    protected function generateConstraintHints(FieldInterface $field, string $pageKey): array
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
                'text' => $this->translator->get('form.hints.required'),
                'class' => 'constraint-required'
            ];
        }

        // ✅ ALWAYS VISIBLE: Text length constraints
        if (!empty($attrs['minlength'])) {
            $onFocus[] = [
                'icon' => '↓',
                'text' => sprintf(
                    $this->translator->get('form.hints.minlength'),
                    $attrs['minlength']
                ),
                'class' => 'constraint-minlength'
            ];
        }

        if (!empty($attrs['maxlength'])) {
            $onFocus[] = [
                'icon' => '↑',
                'text' => sprintf(
                    $this->translator->get('form.hints.maxlength'),
                    $attrs['maxlength']
                ),
                'class' => 'constraint-maxlength'
            ];
        }

        // ✅ ALWAYS VISIBLE: Numeric constraints
        if (in_array($type, ['number', 'decimal', 'range'], true)) {
            if (!empty($attrs['min'])) {
                $alwaysVisible[] = [
                    'icon' => '≥',
                    'text' => sprintf(
                        $this->translator->get('form.hints.min'),
                        $attrs['min']
                    ),
                    'class' => 'constraint-min'
                ];
            }
            if (!empty($attrs['max'])) {
                $alwaysVisible[] = [
                    'icon' => '≤',
                    'text' => sprintf(
                        $this->translator->get('form.hints.max'),
                        $attrs['max']
                    ),
                    'class' => 'constraint-max'
                ];
            }
        }

        // ✅ ALWAYS VISIBLE: Date/Time range constraints
        if (in_array($type, ['date', 'datetime', 'month', 'week', 'time'], true)) {
            if (!empty($attrs['min'])) {
                $alwaysVisible[] = [
                    'icon' => '📅',
                    'text' => sprintf(
                        $this->translator->get('form.hints.date_min'),
                        $attrs['min']
                    ),
                    'class' => 'constraint-date-min'
                ];
            }
            if (!empty($attrs['max'])) {
                $alwaysVisible[] = [
                    'icon' => '📅',
                    'text' => sprintf(
                        $this->translator->get('form.hints.date_max'),
                        $attrs['max']
                    ),
                    'class' => 'constraint-date-max'
                ];
            }
        }

        // ⚠️ ON FOCUS: Pattern constraint (complex explanation)
        if (!empty($attrs['pattern'])) {
            $patternMsg = $attrs['pattern_message'] ??
                        $this->translator->get('form.hints.pattern');
            $onFocus[] = [
                'icon' => '⚙',
                'text' => $patternMsg,
                'class' => 'constraint-pattern'
            ];
        }

        // ⚠️ ON FOCUS: Type-specific format hints
        if ($type === 'email') {
            $onFocus[] = [
                'icon' => '@',
                'text' => $this->translator->get('form.hints.email'),
                'class' => 'constraint-email'
            ];
        }

        if ($type === 'tel') {
            $onFocus[] = [
                'icon' => '☎',
                'text' => $this->translator->get('form.hints.tel'),
                'class' => 'constraint-tel'
            ];
        }

        if ($type === 'url') {
            $onFocus[] = [
                'icon' => '🔗',
                'text' => $this->translator->get('form.hints.url'),
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
    protected function getInformedValidationError(FieldInterface $field, string $error): ?string
    {
        $pageKey = 'sssssssssssssssssssssssssss';
        $parts     = explode('.', $error);
        $validationFlag = array_slice($parts, -2, 1)[0];

        if ($validationFlag !== 'validation') {
            return null;
        }

        $errorType  = $parts[array_key_last($parts)];
        $attrs      = $field->getAttributes();
        $translation = $this->translator->get($error);

        if (!array_key_exists($errorType, $attrs)) {
            // Log a warning for missing attribute, but do not mask the bug
            $this->logger->warning(
                sprintf(
                    'Validation error key "%s" not found in field attributes for field "%s".',
                    $errorType,
                    method_exists($field, 'getName') ? $field->getName() : 'unknown'
                )
            );
            //return $translation; // Return untranslated message as fallback
        }

        return $translation;
    }


    /**
     * ✅ ABSTRACT METHOD: Child renderers implement framework-specific HTML structure.
     *
     * @param FieldInterface $field The field being rendered
     * @param array<int, array<string, string>> $hints Array of hint data (icon, text, class)
     * @return string Framework-specific HTML for constraint hints
     */
    abstract protected function renderConstraintHintsHtml(FieldInterface $field, array $hints): string;

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
     * ✅ ABSTRACT METHOD: Child renderers implement framework-specific draft notification HTML.
     *
     * @return string Framework-specific HTML for draft notification
     */
    abstract protected function renderDraftNotificationHtml(): string;

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
