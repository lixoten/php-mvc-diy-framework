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
     * ✅ VANILLA-SPECIFIC: Render individual field with vanilla CSS.
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
    protected function renderEndTag(FormInterface $form, array $options): string
    {
        return '</form>';
    }

    
}
