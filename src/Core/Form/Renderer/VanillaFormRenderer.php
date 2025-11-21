<?php

declare(strict_types=1);

namespace Core\Form\Renderer;

use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;

/**
 * Vanilla CSS form renderer - pure CSS with no framework.
 *
 * ✅ Extends AbstractFormRenderer for shared logic.
 * ✅ Only contains vanilla CSS-specific HTML output.
 */
class VanillaFormRenderer extends AbstractFormRenderer
{
    /**
     * {@inheritdoc}
     */
    public function renderForm(FormInterface $form, array $options = []): string
    {
        $pageName = $form->getPageName();

        $options = $this->mergeOptions($form, $options);

        $output = $this->renderStart($form, $options);

        // Vanilla CSS error summary
        $errorDisplay = $options['error_display'] ?? 'inline';
        if ($errorDisplay === 'summary') {
            $allErrors = [];

            foreach ($form->getFields() as $field) {
                $fieldErrors = $field->getErrors();
                if (!empty($fieldErrors)) {
                    $fieldLabel = $field->getLabel();
                    foreach ($fieldErrors as $error) {
                        $allErrors[] = '<li><strong>' . htmlspecialchars($fieldLabel) . ':</strong> ' .
                                    htmlspecialchars($error) . '</li>';
                    }
                }
            }

            $formErrors = $form->getErrors('_form');
            foreach ($formErrors as $error) {
                $allErrors[] = '<li>' . htmlspecialchars($error) . '</li>';
            }

            if (!empty($allErrors)) {
                $output .= '<div class="vanilla-alert vanilla-alert-danger">';
                $output .= '<h5>Please correct the following errors:</h5>';
                $output .= '<ul>' . implode('', $allErrors) . '</ul>';
                $output .= '</div>';
            }

            $options['hide_inline_errors'] = true;
        } else {
            $output .= $this->renderErrors($form, $options);
        }

        // Render hidden fields first
        foreach ($form->getFields() as $field) {
            if ($field->getType() === 'hidden') {
                $output .= $this->renderField($form->getName(), $pageName, $field, $options);
               // $field->setType('display');
            }
        }

        // Render visible fields with constraint hints
        foreach ($form->getFields() as $field) {
            if ($field->getType() !== 'hidden') {
                $output .= $this->renderField($form->getName(), $pageName, $field, $options);

                if ($options['show_constraint_hints'] ?? true) {
                    $output .= $this->generateConstraintHints($field, $form->getName());
                }
            }
        }

        // Render CAPTCHA if required
        if ($form->isCaptchaRequired()) {
            $output .= '<div class="vanilla-card">';
            $output .= '<h5>Security Verification</h5>';
            $output .= $this->renderField($form->getName(), $pageName, $form->getField('captcha'), $options);
            $output .= '</div>';
        }

        // AJAX save spinner
        if (!empty($options['ajax_save'])) {
            $output .= '<div id="ajax-save-spinner" style="display:none;" class="vanilla-spinner">';
            $output .= '<span>⟳</span> Saving...';
            $output .= '</div>';
        }

        // Submit button with vanilla CSS
        if (!isset($options['no_submit_button']) || !$options['no_submit_button']) {
            $buttonText = $options['submit_text'] ?? 'Submit';
            $buttonClass = $options['submit_class'] ?? 'vanilla-button vanilla-button-primary';
            $output .= sprintf(
                '<div class="vanilla-form-actions"><button type="submit" class="%s">%s</button>',
                htmlspecialchars($buttonClass),
                htmlspecialchars($buttonText)
            );

            if (!empty($options['cancel_url'])) {
                $cancelText = $options['cancel_text'] ?? 'Cancel';
                $cancelClass = $options['cancel_class'] ?? 'vanilla-button vanilla-button-secondary';
                $output .= sprintf(
                    ' <a href="%s" class="%s">%s</a>',
                    htmlspecialchars($options['cancel_url']),
                    htmlspecialchars($cancelClass),
                    htmlspecialchars($cancelText)
                );
            }

            $output .= '</div>';
        }

        $output .= $this->renderDraftNotification($options);
        $output .= $this->renderEnd($form, $options);

        return $output;
    }

    /**
     * ✅ VANILLA-SPECIFIC: Render constraint hints with vanilla CSS HTML structure.
     *
     * @param FieldInterface $field
     * @param array<int, array<string, string>> $hints
     * @return string
     */
    protected function renderConstraintHintsHtml(FieldInterface $field, array $hints): string
    {
        $fieldName = $field->getName();

        $html = '<div class="vanilla-field-constraints" id="constraints-' . htmlspecialchars($fieldName) . '">';
        $html .= '<ul class="vanilla-constraints-list">';

        foreach ($hints as $hint) {
            $html .= sprintf(
                '<li class="vanilla-constraint-item %s"><span class="vanilla-constraint-icon">%s</span> <span class="vanilla-constraint-text">%s</span></li>',
                htmlspecialchars($hint['class']),
                $hint['icon'],
                htmlspecialchars($hint['text'])
            );
        }

        $html .= '</ul></div>';

        return $html;
    }

    /**
     * ✅ VANILLA-SPECIFIC: Render draft notification with vanilla CSS HTML.
     *
     * @return string
     */
    protected function renderDraftNotificationHtml(): string
    {
        $html  = '<div id="draft-notification" style="display:none;" class="vanilla-alert vanilla-alert-warning"></div>';
        $html .= '<button type="button" id="discard-draft-btn" style="display:none;" ';
        $html .= 'class="vanilla-button vanilla-button-secondary">Restore Data from server</button>';

        return $html;
    }

    /**
     * ✅ VANILLA-SPECIFIC: Render individual field with vanilla CSS.
     *
     * @param string $formName
     * @param FieldInterface $field
     * @param array<string, mixed> $options
     * @return string
     */
    public function renderField(string $formName, string $pageName, FieldInterface $field, array $options = []): string
    {
        $type = $field->getType();
        $name = $field->getName();
        $id = $field->getAttribute('id') ?? $name;
        $label = htmlspecialchars($this->translator->get($field->getLabel()));
        $value = htmlspecialchars((string)$field->getValue() ?? '');
        $errors = $field->getErrors();

        // Vanilla CSS error handling
        $errorHTML = '';
        $errorClass = '';
        if (!empty($errors) && empty($options['hide_inline_errors'])) {
            $errorClass = ' vanilla-input-error';
            $errorId = $id . '-error';
            $errorHTML = '<div id="' . $errorId . '" class="vanilla-error-message">';
            foreach ($errors as $error) {
                $errorHTML .= htmlspecialchars($error) . '<br>';
            }
            $errorHTML .= '</div>';
        }

        $output = '<div class="vanilla-form-group">';

        // Vanilla CSS input fields
        if (in_array($type, ['text', 'email', 'tel', 'url', 'password', 'number', 'search', 'date', 'time'])) {
            $output .= '<label class="vanilla-label" for="' . $id . '">' . $label . '</label>';
            $output .= '<input type="' . $type . '" class="vanilla-input' . $errorClass . '" ';
            $output .= 'id="' . $id . '" name="' . $name . '" value="' . $value . '">';
            $output .= $errorHTML;
        } elseif ($type === 'textarea') {
            $output .= '<label class="vanilla-label" for="' . $id . '">' . $label . '</label>';
            $output .= '<textarea class="vanilla-textarea' . $errorClass . '" id="' . $id . '" ';
            $output .= 'name="' . $name . '">' . $value . '</textarea>';
            $output .= $errorHTML;
        } elseif ($type === 'checkbox') {
            $output .= '<div class="vanilla-checkbox">';
            $output .= '<input type="checkbox" class="vanilla-checkbox-input" id="' . $id . '" ';
            $output .= 'name="' . $name . '" value="1"' . ($field->getValue() ? ' checked' : '') . '>';
            $output .= '<label class="vanilla-checkbox-label" for="' . $id . '">' . $label . '</label>';
            $output .= '</div>';
            $output .= $errorHTML;
        } elseif ($type === 'select') {
            $output .= '<label class="vanilla-label" for="' . $id . '">' . $label . '</label>';
            $output .= '<select class="vanilla-select' . $errorClass . '" id="' . $id . '" name="' . $name . '">';

            $choices = $field->getOptions()['choices'] ?? [];
            $defaultChoice = $field->getOptions()['default_choice'] ?? null;
            if ($defaultChoice) {
                $output .= '<option value="">' . htmlspecialchars($defaultChoice) . '</option>';
            }
            foreach ($choices as $choiceValue => $optionLabel) {
                $selected = ($field->getValue() == $choiceValue) ? ' selected' : '';
                $output .= '<option value="' . htmlspecialchars((string)$choiceValue) . '"' . $selected . '>';
                $output .= htmlspecialchars($optionLabel) . '</option>';
            }
            $output .= '</select>';
            $output .= $errorHTML;
        } elseif ($type === 'hidden') {
            $output .= '<input type="hidden" id="' . $id . '" name="' . $name . '" value="' . $value . '">';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function renderErrors(FormInterface $form, array $options = []): string
    {
        $errors = $form->getErrors('_form');
        if (empty($errors)) {
            return '';
        }

        $output = '<div class="vanilla-alert vanilla-alert-danger">';
        foreach ($errors as $error) {
            $output .= '<p>' . htmlspecialchars($error) . '</p>';
        }
        $output .= '</div>';

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function renderStart(FormInterface $form, array $options = []): string
    {
        $attributes = $form->getAttributes();
        $renderOptions = $form->getRenderOptions();
        $options = array_merge($renderOptions, $options);

        if (!empty($options['action_url'])) {
            $attributes['action'] = $options['action_url'];
        } elseif (!isset($attributes['action'])) {
            $attributes['action'] = '';
        }

        if (!isset($attributes['method'])) {
            $attributes['method'] = 'post';
        }

        // Vanilla CSS form class
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'vanilla-form';
        }

        $attrString = '';
        foreach ($attributes as $name => $value) {
            if ($value === '') {
                $attrString .= ' ' . $name;
            } else {
                $attrString .= ' ' . $name . '="' . htmlspecialchars((string)$value) . '"';
            }
        }

        $output = '<form' . $attrString . '>';
        $token = $form->getCSRFToken();
        $output .= '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';

        if (!empty($options['form_heading'])) {
            $headingLevel = $options['form_heading_level'] ?? 'h2';
            $output .= "<{$headingLevel} class=\"vanilla-form-heading\">" .
                    htmlspecialchars($options['form_heading']) .
                    "</{$headingLevel}>";
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function renderEnd(FormInterface $form, array $options = []): string
    {
        return '</form>';
    }
}
