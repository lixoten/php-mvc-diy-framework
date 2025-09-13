<?php

declare(strict_types=1);

namespace Core\Form;

use App\Helpers\DebugRt;

/**
 * Abstract field registry with inheritance support
 */
abstract class AbstractFormFieldRegistry implements FormFieldRegistryInterface
{
    protected ?FormFieldRegistryInterface $baseRegistry;

    /**
     * Constructor
     */
    public function __construct(?FormFieldRegistryInterface $baseRegistry = null)
    {
        $this->baseRegistry = $baseRegistry;
    }

    /**
     * Get a field definition by name with inheritance
     */
    public function get(string $fieldName): ?array
    {
        // Try to get from local registry first
        $method = 'get' . ucfirst($fieldName);
        $localDefinition = method_exists($this, $method) ? $this->$method() : null;

        // If found locally, return it (local overrides common and base)
        if ($localDefinition !== null) {
            return $localDefinition;
        }

        // Try to get from common fields
        $commonDefinition = $this->getCommonField($fieldName);

        // Try to get from base registry
        $baseDefinition = $this->baseRegistry ? $this->baseRegistry->get($fieldName) : null;
        // if ($this->baseRegistry) {
        //     $baseDefinition = $this->baseRegistry->get($fieldName);
        // } else {
        //     $baseDefinition = null;
        // }
        // Determine the definition to use (common takes precedence over base if local is null)
        $definitionToUse = $commonDefinition ?? $baseDefinition;

        // If no definition found anywhere, return null
        if ($definitionToUse === null) {
            return null;
        }

        // If we only had a common or base definition, return it directly
        // (No merging needed yet as localDefinition is null)
        return $definitionToUse;

        // // Try to get from local registry first
        // $method = 'get' . ucfirst($fieldName);

        // $localDefinition = method_exists($this, $method) ? $this->$method() : null;
        // $baseDefinition = $this->baseRegistry ? $this->baseRegistry->get($fieldName) : null;

        // // Inheritance logic
        // if ($localDefinition === null && $baseDefinition === null) {
        //     return null;
        // }

        // // Only base registry has the field
        // if ($localDefinition === null) {
        //     return $baseDefinition;
        // }

        // // Only local registry has the field
        // if ($baseDefinition === null) {
        //     return $localDefinition;
        // }

        // // Start with base definition
        // $result = $baseDefinition;

        // // Override with local values
        // foreach ($localDefinition as $key => $value) {
        //     // Special handling for validators - COMPLETE replacement instead of merge
        //     if ($key === 'validators' && is_array($value)) {
        //         $result[$key] = $value; // Complete replacement
        //     } elseif ($value === null && isset($result[$key])) {
        //         // Special handling for null values - remove property entirely
        //         unset($result[$key]);
        //     } else {
        //         // Normal case - override the value
        //         $result[$key] = $value;
        //     }
        // }

        // return $result;
        // // Merge with local taking precedence
        // // return array_replace_recursive($baseDefinition, $localDefinition);
    }


    /**
     * Provides definitions for common form fields.
     * Override or extend in child classes if needed.
     *
     * @param string $fieldName
     * @return array|null
     */
    protected function getCommonField(string $fieldName): ?array
    {

        DebugRt::j('1', '', 'BOOM on Config File 123');
        switch ($fieldName) {
            case 'name':
                return [
                    'type' => 'text',
                    'label' => 'afrName',
                    'required' => true,
                    'minlength' => 10,
                    'maxlength' => 100, //255 Default max length
                    'attributes' => [
                        'class' => 'form-control',
                        'id' => 'name', // Default id
                        'placeholder' => 'Enter name'
                    ]
                ];
            case 'description':
                return [
                    'type' => 'textarea',
                    'label' => 'afrDescription',
                    'required' => true,
                    'minlength' => 10,
                    'maxlength' => 5000, // Default max length
                    'attributes' => [
                        'class' => 'form-control',
                        'id' => 'description', // Default id
                        'placeholder' => 'Enter description',
                        'rows' => '5'
                    ]
                ];
            case 'titlxe':
                return [
                    'type' => 'text',
                    'label' => 'afrTitle',
                    'required' => true,
                    'minlength' => 10,
                    'maxlength' => 100, //255 Default max length
                    'attributes' => [
                        'class' => 'form-control',
                        'id' => 'title', // Default id
                        'placeholder' => 'Enter title'
                    ]
                ];
            case 'content': // Example: Add a common 'content' field
                return [
                    'type' => 'textarea',
                    'label' => 'afrContent',
                    'required' => true,
                    'minlength' => 10,
                    'maxlength' => 5000, // Default max length
                    'attributes' => [
                        'class' => 'form-control',
                        'id' => 'content', // Default id
                        'placeholder' => 'Enter content',
                        'rows' => '5'
                    ]
                ];
            // case 'username':
            //     return [
            //         'type' => 'text',
            //         'label' => 'Username xxx',
            //         'required' => true,
            //         'minlength' => 3,
            //         'maxlength' => 50,
            //         'attributes' => [
            //             'class' => 'form-control',
            //             'id' => 'username',
            //             'placeholder' => 'Choose a unique username',
            //             'autofocus' => true
            //         ],
            //         'validators' => [
            //             'unique_username' => [
            //                 'message' => 'This username is already taken.'
            //             ]
            //         ]
            //     ];

    //     return [
    //         'label' => 'Username or Emailxxx',
    //         'attributes' => [
    //             'placeholder' => 'Enter your username or email',
    //         ],
    //         'minlength' => null,  // Remove minlength restriction
    //         'maxlength' => null,  // Remove maxlength restriction
    //         // Remove registration-specific validators
    //         'validators' => []
    //     ];




            // Add other common fields like 'status', 'slug', 'email', 'password', etc.
            // case 'email':
            //     return [ ... definition ... ];
            default:
                return null;
        }
    }
}
