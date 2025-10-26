<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;

use Core\Database\Database;

/**
 * Validator for checking uniqueness of values within a table
 */
class UniqueValidator // extends AbstractValidator
{
    // Todo - a Unique Validator for a field withing a table
    /*
        'validators' => [
        'email' => [],  // Existing email validation
        'unique' => [
            'table' => 'testy',
            'column' => 'primary_email',
            'exclude_id' => 'id',  // Exclude current record during updates
            'message' => 'This email address is already registered.',
            // Optional: For per-store uniqueness
            // 'conditions' => ['store_id' => $currentStoreId],
        ]
    ]
    */
}
