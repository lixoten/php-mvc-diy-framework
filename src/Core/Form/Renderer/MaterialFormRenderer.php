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
    protected function renderEndTag(FormInterface $form, array $options): string
    {
        return '</form>';
    }

    
}
