<?php

declare(strict_types=1);

namespace Core\Form\Renderer;

use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;

/**
 * Material Design form renderer.
 *
 * ✅ Extends AbstractFormRenderer for shared logic.
 * ✅ Only contains Material Design-specific HTML output.
 */
class MaterialFormRenderer extends AbstractFormRenderer
{
    /**
     * {@inheritdoc}
     */
    public function renderForm(FormInterface $form, array $options = []): string
    {
        $pageName = $form->getPageName();

        $options = $this->mergeOptions($form, $options);

        $output = $this->renderStart($form, $options);

        // Material Design specific: Error summary with elevated card
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
                $output .= '<div class="mdc-card mdc-card--outlined mb-4" style="background-color: #ffebee;">';
                $output .= '<div class="mdc-card__content">';
                $output .= '<h5 class="mdc-typography--headline6 text-danger">Please correct the following errors:</h5>';
                $output .= '<ul class="mb-0">' . implode('', $allErrors) . '</ul>';
                $output .= '</div></div>';
            }

            $options['hide_inline_errors'] = true;
        } else {
            $output .= $this->renderErrors($form, $options);
        }

        // Render hidden fields first
        foreach ($form->getFields() as $field) {
            if ($field->getType() === 'hidden') {
                $output .= $this->renderField(
                    // $form->getName(),
                    $pageName,
                    $field,
                    $options
                );
                //$field->setType('display');
            }
        }

        // Render visible fields with constraint hints
        foreach ($form->getFields() as $field) {
            if ($field->getType() !== 'hidden') {
                $output .= $this->renderField($pageName, $field, $options);

                if ($options['show_constraint_hints'] ?? true) {
                    $output .= $this->generateConstraintHints($field, $pageName, $form->getName());
                }
            }
        }

        // Render CAPTCHA if required
        if ($form->isCaptchaRequired()) {
            $output .= '<div class="mdc-card mdc-card--outlined mb-4">';
            $output .= '<div class="mdc-card__content">';
            $output .= '<h5 class="mdc-typography--headline6 mb-3">Security Verification</h5>';
            $output .= $this->renderField($form->getField('captcha'), $options);
            $output .= '</div></div>';
        }

        // AJAX save spinner
        if (!empty($options['ajax_save'])) {
            $output .= '<div id="ajax-save-spinner" style="display:none;" class="text-info mb-2">';
            $output .= '<span class="spinner-border spinner-border-sm"></span> Saving...';
            $output .= '</div>';
        }

        // Submit button with Material Design styling
        if (!isset($options['no_submit_button']) || !$options['no_submit_button']) {
            $buttonText = $options['submit_text'] ?? 'Submit';
            $buttonClass = $options['submit_class'] ?? 'mdc-button mdc-button--raised mdc-theme--primary';
            $output .= sprintf(
                '<div class="mb-3"><button type="submit" class="%s">%s</button>',
                htmlspecialchars($buttonClass),
                htmlspecialchars($buttonText)
            );

            if (!empty($options['cancel_url'])) {
                $cancelText = $options['cancel_text'] ?? 'Cancel';
                $cancelClass = $options['cancel_class'] ?? 'mdc-button mdc-button--outlined ms-2';
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

    /** {@inheritdoc} */
    protected function renderConstraintHintsHtml(FieldInterface $field, array $hints): string
    {
        $fieldName = $field->getName();

        $html = '<div class="field-constraints mdc-text-field-helper-line" id="constraints-' .
                htmlspecialchars($fieldName) . '" aria-live="polite">';
        $html .= '<ul class="constraints-list mdc-list mdc-list--dense">';

        foreach ($hints as $hint) {
            $html .= sprintf(
                '<li class="constraint-item mdc-list-item %s"><span class="constraint-icon mdc-list-item__graphic">" .
                            "%s</span><span class="constraint-text mdc-list-item__text">%s</span></li>',
                htmlspecialchars($hint['class']),
                $hint['icon'],
                htmlspecialchars($hint['text'])
            );
        }

        $html .= '</ul></div>';

        return $html;
    }

    /**
     * ✅ MATERIAL-SPECIFIC: Render draft notification with Material Design HTML.
     *
     * @return string
     */
    protected function renderDraftNotificationHtml(): string
    {
        $html  = '<div id="draft-notification" style="display:none;" ' .
                                       'class="mdc-card mdc-card--outlined mb-3" style="background-color: #fff3cd;">';
        $html .= '<div class="mdc-card__content"></div></div>';
        $html .= '<button type="button" id="discard-draft-btn" style="display:none;" ';
        $html .= 'class="mdc-button mdc-button--outlined">Restore Data from server</button>';

        return $html;
    }

    /**
     * ✅ MATERIAL-SPECIFIC: Render individual field with Material Design components.
     *
     * @param string $formName
     * @param FieldInterface $field
     * @param array<string, mixed> $options
     * @return string
     */
    public function renderField(string $pageName, FieldInterface $field, array $options = []): string
    {
        $type = $field->getType();
        $name = $field->getName();
        $id = $field->getAttribute('id') ?? $name;
        $label = htmlspecialchars($this->translator->get($field->getLabel()));
        $value = htmlspecialchars((string)$field->getValue() ?? '');
        $errors = $field->getErrors();
        $attributes = $field->getAttributes();

        // Material Design error handling
        $errorHTML = '';
        $errorClass = '';
        if (!empty($errors) && empty($options['hide_inline_errors'])) {
            $errorClass = ' mdc-text-field--invalid';
            $errorId = $id . '-error';
            $errorHTML = '<div id="' . $errorId . '" class="mdc-text-field-helper-text ' .
                     'mdc-text-field-helper-text--persistent mdc-text-field-helper-text--validation-msg" role="alert">';
            foreach ($errors as $error) {
                $errorHTML .= htmlspecialchars($error) . '<br>';
            }
            $errorHTML .= '</div>';
        }

        $output = '<div class="mb-3">';

        // Material Design text field
        if (in_array($type, ['text', 'email', 'tel', 'url', 'password', 'number', 'search'])) {
            $output .= '<label class="mdc-text-field mdc-text-field--filled' . $errorClass . '">';
            $output .= '<span class="mdc-text-field__ripple"></span>';
            $output .= '<span class="mdc-floating-label" id="' . $id . '-label">' . $label . '</span>';
            $output .= '<input type="' . $type . '" class="mdc-text-field__input" id="' . $id . '" ';
            $output .= 'name="' . $name . '" value="' . $value . '" aria-labelledby="' . $id . '-label">';
            $output .= '<span class="mdc-line-ripple"></span>';
            $output .= '</label>';
            $output .= $errorHTML;
        } elseif ($type === 'textarea') {
            $output .= '<label class="mdc-text-field mdc-text-field--filled mdc-text-field--textarea' . $errorClass . '">';
            $output .= '<span class="mdc-text-field__ripple"></span>';
            $output .= '<span class="mdc-floating-label" id="' . $id . '-label">' . $label . '</span>';
            $output .= '<textarea class="mdc-text-field__input" id="' . $id . '" ';
            $output .= 'name="' . $name . '" aria-labelledby="' . $id . '-label">' . $value . '</textarea>';
            $output .= '<span class="mdc-line-ripple"></span>';
            $output .= '</label>';
            $output .= $errorHTML;
        } elseif ($type === 'checkbox') {
            $output .= '<div class="mdc-form-field">';
            $output .= '<div class="mdc-checkbox">';
            $output .= '<input type="checkbox" class="mdc-checkbox__native-control" id="' . $id . '" ';
            $output .= 'name="' . $name . '" value="1"' . ($field->getValue() ? ' checked' : '') . '>';
            $output .= '<div class="mdc-checkbox__background">';
            $output .= '<svg class="mdc-checkbox__checkmark" viewBox="0 0 24 24">';
            $output .= '<path class="mdc-checkbox__checkmark-path" fill="none" d="M1.73,12.91 8.1,19.28 22.79,4.59"/>';
            $output .= '</svg>';
            $output .= '<div class="mdc-checkbox__mixedmark"></div>';
            $output .= '</div>';
            $output .= '<div class="mdc-checkbox__ripple"></div>';
            $output .= '</div>';
            $output .= '<label for="' . $id . '">' . $label . '</label>';
            $output .= '</div>';
            $output .= $errorHTML;
        } elseif ($type === 'select') {
            $output .= '<div class="mdc-select mdc-select--filled' . $errorClass . '">';
            $output .= '<div class="mdc-select__anchor" role="button" aria-haspopup="listbox" aria-labelledby="' . $id . '-label">';
            $output .= '<span class="mdc-select__ripple"></span>';
            $output .= '<span class="mdc-floating-label mdc-floating-label--float-above" id="' . $id . '-label">' . $label . '</span>';
            $output .= '<span class="mdc-select__selected-text"></span>';
            $output .= '<span class="mdc-select__dropdown-icon">';
            $output .= '<svg class="mdc-select__dropdown-icon-graphic" viewBox="7 10 10 5" focusable="false">';
            $output .= '<polygon class="mdc-select__dropdown-icon-inactive" stroke="none" fill-rule="evenodd" points="7 10 12 15 17 10"></polygon>';
            $output .= '<polygon class="mdc-select__dropdown-icon-active" stroke="none" fill-rule="evenodd" points="7 15 12 10 17 15"></polygon>';
            $output .= '</svg>';
            $output .= '</span>';
            $output .= '<span class="mdc-line-ripple"></span>';
            $output .= '</div>';
            $output .= '<select class="mdc-select__native-control" id="' . $id . '" name="' . $name . '">';

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
            $output .= '</div>';
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

        $output = '<div class="mdc-card mdc-card--outlined mb-3" style="background-color: #ffebee;">';
        $output .= '<div class="mdc-card__content">';
        foreach ($errors as $error) {
            $output .= '<p class="text-danger mb-1">' . htmlspecialchars($error) . '</p>';
        }
        $output .= '</div></div>';

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

        // Material Design form class
        if (!isset($attributes['class'])) {
            $attributes['class'] = 'mdc-form';
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
            $output .= "<{$headingLevel} class=\"mdc-typography--headline4 mb-4\">" .
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
