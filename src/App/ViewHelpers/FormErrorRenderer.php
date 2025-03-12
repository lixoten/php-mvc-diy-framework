<?php

declare(strict_types=1);

namespace App\ViewHelpers;

class FormErrorRenderer
{
    /**
     * Renders the form errors.
     *
     * @param array $errors The array of errors, where each field can have multiple error messages.
     * @return string The rendered HTML for the errors.
     */
    public function render(array $errors): string
    {
        $output = '<div style="border:2px solid red;">';
        foreach ($errors as $fieldErrors) {
            if (is_array($fieldErrors)) {
                foreach ($fieldErrors as $err) {
                    $output .= '<div class="error">' . htmlspecialchars($err) . '</div>';
                }
            } else {
                $output .= '<div class="error">' . htmlspecialchars($fieldErrors) . '</div>';
            }
        }
        $output .= '</div>';

        return $output;
    }
}
