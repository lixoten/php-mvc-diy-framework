<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Core\Exceptions\SchemaDefinitionException;
use RuntimeException;

/**
 * Service responsible for generating feature-specific fields configuration files.
 *
 * This generator reads an entity's schema definition and produces a
 * `{entityName}_fields_{configType}.php` file within the feature's Config directory.
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 *
 * @package   MVC LIXO Framework
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2025
 */
class ConfigFieldsGenerator
{
    private GeneratorOutputService $generatorOutputService;

    /**
     * param SchemaLoaderService $schemaLoaderService The service for loading schema definitions.
     * @param GeneratorOutputService $generatorOutputService The service for managing generator output.
     */
    public function __construct(
        GeneratorOutputService $generatorOutputService,
    ) {
        $this->generatorOutputService = $generatorOutputService;
    }

     /**
     * Generate the fields configuration file for a given entity schema.
     *
     * @param array<string, mixed> $schema The entity schema definition.
     * @param string $configType The type of configuration to generate (e.g., 'list', 'form', 'edit', 'root').
     * @return string The absolute path to the generated field config file.
     * @throws SchemaDefinitionException If the schema is invalid or not found.
     * @throws \RuntimeException If the output directory cannot be created or file cannot be written.
     */
    public function generate(array $schema, string $configType): string
    {
        if (empty($schema['entity']['name'])) {
            throw new SchemaDefinitionException('Invalid schema: missing entity name.');
        }
        $entityName = $schema['entity']['name'];
        $fields = $schema['fields'] ?? [];

        if (empty($fields)) {
            throw new SchemaDefinitionException("Schema for '{$entityName}' has no fields defined.");
        }

        // Get the output directory for the feature's config
        $outputDir = $this->generatorOutputService->getEntityOutputDir($entityName);

        // Ensure the directory exists
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new RuntimeException("Failed to create output directory: {$outputDir}");
            }
        }

        $filePath = $outputDir . strtolower($entityName) . "_fields_{$configType}" . '.php';
        $fileContent = $this->generateConfigFieldFileContent($entityName, $fields, $configType);

        $success = file_put_contents($filePath, $fileContent);
        if ($success === false) {
            throw new RuntimeException("Failed to write field config file: {$filePath}");
        }

        return $filePath;
    }

    /**
     * Generate the PHP code for the fields configuration file.
     *
     * @param string $entityName The name of the entity.
     * @param array<string, array<string, mixed>> $fields Schema field definitions.
     * @param string $configType The type of configuration to generate (e.g., 'list', 'form', 'edit', 'root').
     * @return string The PHP code for the field config file.
     */
    protected function generateConfigFieldFileContent(string $entityName, array $fields, string $configType): string
    {
        $generatedTimestamp = $this->generatorOutputService->getGeneratedFileTimestamp();
        $entityNameLower = strtolower($entityName);
        $fieldDefinitions = [];

        foreach ($fields as $fieldName => $config) {
            if (!in_array($fieldName, ['title', 'generic_text', 'primary_email', 'telephone'])) {
                    continue;
            }
            if ($configType === 'root') {
                if (
                    in_array(
                        $fieldName,
                        [
                            'idXxx', 'store_id', 'user_id', 'name', 'content', 'description',
                            'created_at', 'updated_at', 'xxx', 'xxx', 'xxx', 'xxx'
                        ]
                    )
                ) {
                    continue;
                }
            }
            if ($configType === 'list') {
                if (!in_array($fieldName, ['id', 'xxx', 'xxx'])) {
                    continue;
                }
            }
            if ($configType === 'edit') {
                if (!in_array($fieldName, ['id', 'xxx', 'xxx'])) {
                    continue;
                }
            }

            // if ($configType !== 'root' && !in_array($fieldName, ['id', 'email'])) {
                // continue;
            // }


            // Skip primary key 'id' for form generation, as it's usually auto-incremented
            // if (($config['primary'] ?? false) === true) {
            //     continue;
            // }

            $formType = $this->mapDbTypeToFormType($config, $fieldName);
            if ($formType === 'telephone') {
                $defaultValidatorName = 'tel';
                $defaultFormatterName = 'tel';
            } else {
                $defaultValidatorName = $formType;
                $defaultFormatterName = $formType;
            }
            $labelKey = "{$entityNameLower}.{$fieldName}";
            $placeholderKey = "{$entityNameLower}.{$fieldName}.placeholder";

            // Build the 'list' section if defined in schema
            $listSection = '';
            if (isset($config['list']) && is_array($config['list'])) {
                $listConfig = $config['list'];
            } else {
                    // 'formatter' => null,
                $listConfig = [
                    'sortable' => false,
                ];
            }

            $sortable = isset($listConfig['sortable']) ? ($listConfig['sortable'] ? 'true' : 'false') : 'false';
            $formatter = isset($listConfig['formatter']) ? 'null' : 'null'; // Can be enhanced to support closures


            $someListStuff      = $this->getCodeBlock(type: $formType, blockName: 'list');
            $someFormStuff      = $this->getCodeBlock(type: $formType, blockName: 'form');
            $someFormatterStuff = $this->getCodeBlock(type: $formType, blockName: 'formatter');
            $someValidatorStuff = $this->getCodeBlock(type: $formType, blockName: 'validator');






            // 'formatter' => {$formatter},
            $listSection = <<<PHP
        'list' => [
            {$someListStuff}
        ],

PHP;
            //}

            // ADDED: Build the 'form' section as a variable for consistency
            $formSection = <<<PHP
        'form' => [
            {$someFormStuff}
        ],
PHP;

            if (($config['primary'] ?? false) === true) {
                $formSection = '';
            }

            $fieldDefinitions[] = <<<PHP
    '{$fieldName}' => [ // gen
        'label' => '{$labelKey}',
{$listSection}{$formSection}
        'formatters' => [
            '{$defaultFormatterName}' => [
            {$someFormatterStuff}
            ],
        ],
        'validators' => [
            '{$defaultValidatorName}' => [ // Default validator, can be refined based on db_type
            {$someValidatorStuff}
            ],
        ]
    ],
PHP;
        }

        $fieldDefinitionsString = implode("\n", $fieldDefinitions);

        $php = <<<PHP
<?php

/**
 * Generated File - Date: {$generatedTimestamp}
 * Field definitions for the {$entityName}_{$configType} entity.
 *
 * This file defines how each field should be rendered in forms and lists,
 * including labels, input types, attributes, formatters, and validators.
 */

declare(strict_types=1);

return [
{$fieldDefinitionsString}
];

PHP;

        return $php;
    }

                // $someValidatorStuff = $this->getCodeBlock(type: $formType, blockName: 'validator');
    protected function getCodeBlock(string $type, string $blockName): string
    {
        $block = '';
        switch ($type) {
            // TEXT /////////////////////////////////////////////////////////
            case 'text':
                # code...
                if ($blockName === 'list') {
                    $block = <<<PHP
    'sortable' => false,
    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
    'type'          => 'text',
                'attributes'    => [
                    'placeholder' => 'testy.generic_text.placeholder',
                    // 'required'  => true,     // Used in validation
                    // 'minlength' => 5,        // Used in validation
                    // 'maxlength' => 15,       // Used in validation
                    // 'pattern'   => '/\d/',   // Used in validation
                ],
    PHP;
                } elseif ($blockName === 'formatter') {
                    $block = <<<PHP
        // 'max_length' => 5,
                    // 'truncate_suffix',                   // Defaults to ...
                    // 'truncate_suffix' => '...Read More',
                    // 'null_value' => 'Nothing here',      // Replaces null value with string
                    // 'suffix'     => "Boo",               // Appends to end of text
                    // 'transform'  => 'lowercase',
                    // 'transform'  => 'uppercase',
                    // 'transform'  => 'capitalize',
                    // 'transform'  => 'title',
                    // 'transform'  => 'trim',              // notes-: assuming we did not store clean data
                    // 'transform'  => 'last2char_upper',
    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
        'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                    'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                    // 'ignore_forbidden'  => true,  // Default is false
                    // 'ignore_allowed'    => false, // Default is true
                    //---
                    'required_message'  => "Custom: This field is required.",
                    // 'invalid_message'   => "Custom: Please enter a valid text.",
                    // 'minlength_message' => "Custom: Text must be at least ___ characters.",
                    // 'maxlength_message' => "Custom: Text must not exceed ___ characters.",
                    // 'pattern_message'   => "Custom: Text does not match the required pattern.",
                    // 'allowed_message'   => "Custom: Please select a valid word.",
                    // 'forbidden_message' => "Custom: This word is not allowed.",
    PHP;
                }
                break;
            // EMAIL /////////////////////////////////////////////////////////
            case 'email':
                # code...
                if ($blockName === 'list') {
                    $block = <<<PHP
    'sortable' => false,
    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
    'type'          => 'email',
                'attributes'    => [
                    'placeholder' => 'testy.primary_email.placeholder',
                    'required'    => true,
                    'minlength'   => 12,
                    'maxlength'   => 255,
                    // 'pattern'     => '/^user[a-z0-9._%+-]*@/',
                ],
    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
        'allowed'           => ['ok.com', 'gmail.com'],   // Allowed domains
                    'forbidden'         => ['fook.com'],              // Not allowed domains
                    // 'ignore_forbidden'  => true,  // Default is false
                    // 'ignore_allowed'    => false, // Default is true
                    //---
                    // 'required_message'  => "Custom: Email is required.",
                    // 'invalid_message'   => "Custom: Please enter a valid email address.",
                    // 'minlength_message' => "Custom: Email must be at least ___ characters.",
                    // 'maxlength_message' => "Custom: Email should not exceed ___ characters.",
                    // 'pattern_message'   => "Custom: Email does not match the required pattern.",
                    // 'forbidden_message' => 'Custom: This domain is not allowed.',
                    // 'allowed_message'   => 'Custom: Please select a valid domain.',
    PHP;
                } elseif ($blockName === 'formatter') {
                    $block = <<<PHP
        // 'mask' => true, // Or false, or omit for default
                ],
                'text' => [
                    'transform' => 'lowercase',
    PHP;
                }
                break;
            // TEL /////////////////////////////////////////////////////////
            case 'telephone':
                # code...
                if ($blockName === 'list') {
                    $block = <<<PHP
    'sortable' => false,
    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
    //  'region' => 'US',
                'type'          => 'tel',
                'attributes'    => [
                    'placeholder' => 'testy.telephone.placeholder',
                    // 'required'              => true,
                    // 'list'                  => 'foo',
                    // 'data-char-counter'     => true,     // js-feature
                    // 'data-live-validation'  => true      // js-feature
                    // 'data-mask'             => 'phone', // todo - mast does not validate.
                    // 'data-country'          => 'pt',    // todo - revisit for validation -  'pattern, maxlength
                    // 'style' => 'background: cyan;',
                ],
    PHP;
                } elseif ($blockName === 'formatter') {
                    $block = <<<PHP
        // 'format' => 'default', // no need. FYI National format if its detected
                    // 'format' => 'dashes',  // Force dashes
                    // 'format' => 'dots',    // Force dots
                    // 'format' => 'spaces',  // Force spaces
                    // 'region' => 'PT',      // Optional: provide a specific region context
    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
        // 'required_mess age'  => "Custom: Phone  is required.",
                    // 'invalid_message'   => "Custom: Please enter a valid international phone number
                    //                         (e.g., +15551234567). Invalid Error.",
                    // 'invalid_region_message' => 'Custom: Invalid_region',
                    // 'invalid_parse_message'  => 'Custom: Please enter a valid international phone number
                    //                             (e.g., +15551234567). Parse Error',
    PHP;
                }
                break;

            default:
                if ($blockName === 'list') {
                    $block = <<<PHP
    'sortable' => false,
    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
    'type'          => 'email',
                'attributes'    => [
                ],
    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
    PHP;
                } elseif ($blockName === 'formatter') {
                    $block = <<<PHP
    PHP;
                }
                break;
        }

        return $block;
    }









    /**
     * Maps a database type from schema to an appropriate HTML form input type.
     *
     * @param array<string, mixed> $config The fields configuration from the schema.
     * @param string $fieldName The name of the field.
     * @return string The HTML input type (e.g., 'text', 'number', 'date').
     */
    protected function mapDbTypeToFormType(array $config, string $fieldName): string
    {
        $dbType = $config['db_type'] ?? 'string';
        $length = $config['length'] ?? 0;

        return match ($dbType) {
            'bigIncrements', 'bigInteger', 'integer', 'foreignId' => 'number',
            'boolean' => 'checkbox',
            'decimal', 'float', 'double' => 'number',
            'date' => 'date',
            'dateTime' => 'datetime-local',
            'time' => 'time',
            'text' => 'textarea',
            'string', 'char' => match (true) {
                str_contains($fieldName, 'telephone') => 'telephone',
                str_contains($fieldName, 'email') => 'email',
                str_contains($fieldName, 'password') => 'password',
                str_contains($fieldName, 'url') || str_contains($fieldName, 'address') => 'url',
                str_contains($fieldName, 'color') => 'color',
                $length > 255 => 'textarea', // Long strings might be better as textarea
                default => 'text',
            },
            default => 'text',
        };
    }

    /**
     * Formats a boolean value as 'true' or 'false' string for PHP code generation.
     *
     * @param bool $value The boolean value.
     * @param bool $invert If true, inverts the boolean before formatting.
     * @return string 'true' or 'false'.
     */
    protected function formatBool(bool $value, bool $invert = false): string
    {
        if ($invert) {
            $value = !$value;
        }
        return $value ? 'true' : 'false';
    }
}
