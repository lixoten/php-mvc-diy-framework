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
    private string $entityName;
    private array $fields;
    private string $configType;

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
     * @param string $configType The type of configuration to generate (e.g., 'list', 'form', 'edit', 'root', 'base').
     * @return string The absolute path to the generated field config file.
     * @throws SchemaDefinitionException If the schema is invalid or not found.
     * @throws \RuntimeException If the output directory cannot be created or file cannot be written.
     */
    public function generate(array $schema, string $configType): string
    {
        if (empty($schema['entity']['name'])) {
            throw new SchemaDefinitionException('Invalid schema: missing entity name.');
        }
        $entity = $schema['entity']['name'];

        // $this->entityName = strtolower($schema['entity']['name']);

        if ($configType === 'base') {
            $this->entityName          = 'basefield';
            //$this->entityNameLowercase = 'basefield';
        } else {
            $this->entityName          = strtolower($schema['entity']['name']);
            //$this->entityNameLowercase = strtolower($schema['entity']['name']);
        }

        $this->configType = $configType;
        $this->fields     = $schema['fields'] ?? [];

        if (empty($this->fields)) {
            throw new SchemaDefinitionException("Schema for '{$entity}' has no fields defined.");
        }

        // Get the output directory for the feature's config
        $outputDir = $this->generatorOutputService->getEntityOutputDir($entity);

        // Ensure the directory exists
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0777, true) && !is_dir($outputDir)) {
                throw new RuntimeException("Failed to create output directory: {$outputDir}");
            }
        }

        if ($configType === 'base') {
            $filePath = $outputDir . "base_fields" . '.php';
        } else {
            $filePath = $outputDir . strtolower($entity) . "_fields_{$this->configType}" . '.php';
        }


        $fileContent = $this->generateContent();

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
     * @param string $configType The type of configuration to generate (e.g., 'list', 'form', 'edit', 'root', 'base').
     * @return string The PHP code for the field config file.
     */
    // protected function generateConfigFieldFileContent(string $entityName, array $fields, string $configType): string
    protected function generateContent(): string
    {
        $generatedTimestamp = $this->generatorOutputService->getGeneratedFileTimestamp();
        // $entityNameLower = strtolower($this->entityName);
        $fieldDefinitions = [];

        foreach ($this->fields as $fieldName => $config) {
            if (
                !in_array(
                    $fieldName,
                    [
                        // 'primary_email',
                        // 'title',
                        'id',
                        'generic_text',
                        'primary_email',
                        // 'slug',
                        // 'status',
                        // 'super_powers',
                        // 'primary_email', 'telephone', 'status', 'super_powers'
                    ]
                )
            ) {
                continue;
            }
            if ($this->configType === 'root') {
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
            if ($this->configType === 'base') {
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
            if ($this->configType === 'list') {
                if (!in_array($fieldName, ['id', 'xxx', 'xxx'])) {
                    continue;
                }
            }
            if ($this->configType === 'edit') {
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

            $fieldType = $this->mapDbTypeToFormType($config, $fieldName);
            if ($fieldType === 'telephone') {
                $defaultValidatorName = 'tel';
                $defaultFormatterName = 'tel';
            } else {
                $defaultValidatorName = $fieldType;
                $defaultFormatterName = $fieldType;
            }
            // $labelKey = "{$this->entityName}.{$fieldName}";
            $placeholderKey = "{$this->entityName}.{$fieldName}.form.placeholder";

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




            $formAttr = $this->getFormAttr($fieldName, $config, $placeholderKey, $fieldType);

            $skipList      = false;
            $skipForm      = false;
            $skipFormatter = false;
            $skipValidator = false;
            if (isset($config['primary']) && (bool)$config['primary'] === true) {
                $skipList      = false;
                $skipForm      = true;
                $skipFormatter = true;
                $skipValidator = true;
            }

            $s08 = '        ';
            $sections = [];
            if (!$skipList) {
                $temp =  $this->getCodeBlock(fieldName: $fieldName, type: $fieldType, blockName: 'list');
                $sections[] = <<<PHP
                    'list' => [
                        {$temp}
                            ],
                    PHP;
            }
            if (!$skipForm) {
                $temp =  $this->getCodeBlock(
                    fieldName: $fieldName,
                    type: $fieldType,
                    blockName: 'form',
                    formAttr: $formAttr
                );
                $sections[] = <<<PHP
                    $s08'form' => [
                        {$temp}
                            ],
                    PHP;
            }
            if (!$skipFormatter) {
                $temp =  $this->getCodeBlock(fieldName: $fieldName, type: $fieldType, blockName: 'formatter');
                $sections[] = <<<PHP
                    $s08'formatters' => [
                        {$temp}
                            ],
                    PHP;
            }
            if (!$skipValidator) {
                $temp =  $this->getCodeBlock(fieldName: $fieldName, type: $fieldType, blockName: 'validator');
                $sections[] = <<<PHP
                    $s08'validators' => [
                        {$temp}
                            ],
                    PHP;
            }

            $x = implode("\n", $sections);
            $fieldDefinitions[] = <<<PHP
        '{$fieldName}' => [
            $x
        ],
    PHP;

            $rrr = 4;
        }

        $fieldDefinitionsString = implode("\n", $fieldDefinitions);

        $php = <<<PHP
<?php

/**
 * Generated File - Date: {$generatedTimestamp}
 * Field definitions for the {$this->entityName}_{$this->configType} entity.
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




    protected function getFormAttr(
        string $fieldName,
        array $config,
        string $placeholderKey,
        string $fieldType,
    ): ?string {
        if (stripos($fieldName, 'slug') !== false) {
            return null;
        }

        $formAttr = [];
        $spaces = '                ';
        if ($config['db_type'] === 'string') {
            $formAttr[] = "$spaces'placeholder' => '$placeholderKey',";

            if (isset($config['required']) && $config['required']) {
                $formAttr[] = "$spaces'required'    => {$this->formatBool($config['required'])},";
            } else {
                $formAttr[] = "$spaces// 'required'    => false,";
            }

            if ((isset($config['minlength']))) {
                $formAttr[] = "$spaces'minlength'   => {$config['minlength']},";
            } else {
                $formAttr[] = "$spaces// 'minlength'   => 1,";
            }

            if (isset($config['length'])) {
                if ((isset($config['maxlength']) && $config['maxlength'] <= $config['length'])) {
                    $formAttr[] = "$spaces'maxlength'   => {$config['maxlength']},";
                } else {
                    $formAttr[] = "$spaces'maxlength'   => {$config['length']},";
                }
            } else {
                $formAttr[] = "$spaces// 'maxlength'    => 1,";
            }

            if ((isset($config['pattern']))) {
                $formAttr[] = "$spaces'pattern'     => '{$config['pattern']}',";
            } else {
                if ($fieldType == 'email') {
                    $formAttr[] = "$spaces// 'pattern'     => '/^user[a-z0-9._%+-]*@/',";
                } else {
                    $formAttr[] = "$spaces// 'pattern'     => '/^[a-z0-9]/',";
                }
            }

            if ((isset($config['style']))) {
                $formAttr[] = "$spaces'style'       => {$config['style']},";
            } else {
                $formAttr[] = "$spaces// 'style'       => 'background:yellow;',";
            }

            if (in_array('text', ['text'])) {
                if (isset($config['data-char-counter']) && $config['data-char-counter']) {
                    $formAttr[] = "$spaces'data-char-counter'    => {$this->formatBool($config['data-char-counter'])},";
                } else {
                    $formAttr[] = "$spaces// 'data-char-counter'    => false,";
                }
            }

            if (isset($config['data-live-validation']) && $config['data-live-validation']) {
                $formAttr[] =
                "$spaces'data-live-validation' => {$this->formatBool($config['data-live-validation'])},";
            } else {
                $formAttr[] = "$spaces// 'data-live-validation' => false,";
            }


            if (in_array($fieldType, ['telephone'])) {
                if ((isset($config['data-mask']))) {
                    $formAttr[] = "$spaces'data-mask' => {$config['data-mask']},";
                } else {
                    $formAttr[] = "$spaces// 'data-mask' => 'data-mask',";
                }
            }


            // if ((isset($config['sssss']))) {
            //     $formAttr[] = "$spaces'sssss' => {$config['sssss']},";
            // } else {
            //     $formAttr[] = "$spaces// 'sssss'    => 'sssss',";
            // }

            // if (isset($config['ttttt']) && $config['ttttt']) {
            //     $formAttr[] = "$spaces'ttttt'  => {$this->formatBool($config['ttttt'])},";
            // } else {
            //     $formAttr[] = "$spaces// 'ttttt'    => false,";
            // }
        }
        $formAttr = implode("\n", $formAttr);

        return $formAttr;
    }

    protected function getCodeBlock(
        string $fieldName,
        string $type,
        string $blockName,
        string $formAttr = null
    ): string {
        $block = '';
        $s04 = '    ';
        $s08 = '        ';
        $s12 = '            ';
        $s14 = '                ';
        $s16 = '                    ';
        $snn = '    ';
        switch ($type) {
            // TEXT /////////////////////////////////////////////////////////
            case 'text':
                # code...
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'      => '{$this->entityName}.{$fieldName}.{$blockName}.label',
                    $s12'sortable'   => false,
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'      => '{$this->entityName}.{$fieldName}.{$blockName}.label',
                    $s12'type'       => '{$type}',
                    $s12'attributes' => [
                    $formAttr
                    $s12],
                    PHP;
                } elseif ($blockName === 'formatter') {
                    $block = <<<PHP
                    $s08'{$type}' => [
                        $s12// 'max_length' => 5,
                        $s12// 'truncate_suffix',                   // Defaults to ...
                        $s12// 'truncate_suffix' => '...Read More',
                        $s12// 'null_value' => 'Nothing here',      // Replaces null value with string
                        $s12// 'suffix'     => "Boo",               // Appends to end of text
                        $s12// 'transform'  => 'lowercase',
                        $s12// 'transform'  => 'uppercase',
                        $s12// 'transform'  => 'capitalize',
                        $s12// 'transform'  => 'title',
                        $s12// 'transform'  => 'trim',              // notes-: assuming we did not store clean data
                        $s12// 'transform'  => 'last2char_upper',
                    $s12]
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
                    $s08'{$type}' => [ // Default validator, can be refined based on db_type
                        $s12'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                        $s12'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                        $s12// 'ignore_forbidden'  => true,  // Default is false
                        $s12// 'ignore_allowed'    => false, // Default is true
                        $s12//---
                        $s12'required_message'  => '{$this->entityName}.{$fieldName}.validation.required',
                        $s12'invalid_message'   => '{$this->entityName}.{$fieldName}.validation.invalid',
                        $s12'minlength_message' => '{$this->entityName}.{$fieldName}.validation.minlength',
                        $s12'maxlength_message' => '{$this->entityName}.{$fieldName}.validation.maxlength',
                        $s12'pattern_message'   => '{$this->entityName}.{$fieldName}.validation.pattern',
                        $s12'allowed_message'   => '{$this->entityName}.{$fieldName}.validation.allowed',
                        $s12'forbidden_message' => '{$this->entityName}.{$fieldName}.validation.forbidden',
                    $s12]
                    PHP;
                }
                break;

            // number /////////////////////////////////////////////////////////
            case 'number':
                # code...
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'      => '{$this->entityName}.{$fieldName}.{$blockName}.label',
                    $s12'sortable'   => false,
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'      => '{$this->entityName}.{$fieldName}.{$blockName}.label',
                    $s12'type'       => '{$type}',
                    $s12'attributes' => [
                    $formAttr
                    $s12],
                    PHP;
                } elseif ($blockName === 'formatter') {
                    $block = <<<PHP
                    $s08'{$type}' => [
                        $s12// 'max_length' => 5,
                        $s12// 'truncate_suffix',                   // Defaults to ...
                        $s12// 'truncate_suffix' => '...Read More',
                        $s12// 'null_value' => 'Nothing here',      // Replaces null value with string
                        $s12// 'suffix'     => "Boo",               // Appends to end of text
                        $s12// 'transform'  => 'lowercase',
                        $s12// 'transform'  => 'uppercase',
                        $s12// 'transform'  => 'capitalize',
                        $s12// 'transform'  => 'title',
                        $s12// 'transform'  => 'trim',              // notes-: assuming we did not store clean data
                        $s12// 'transform'  => 'last2char_upper',
                    $s12]
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
                    $s08'{$type}' => [ // Default validator, can be refined based on db_type
                        $s12'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                        $s12'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                        $s12// 'ignore_forbidden'  => true,  // Default is false
                        $s12// 'ignore_allowed'    => false, // Default is true
                        $s12//---
                        $s12'required_message'  => '{$this->entityName}.{$fieldName}.validation.required',
                        $s12'invalid_message'   => '{$this->entityName}.{$fieldName}.validation.invalid',
                        $s12'minlength_message' => '{$this->entityName}.{$fieldName}.validation.minlength',
                        $s12'maxlength_message' => '{$this->entityName}.{$fieldName}.validation.maxlength',
                        $s12'pattern_message'   => '{$this->entityName}.{$fieldName}.validation.pattern',
                        $s12'allowed_message'   => '{$this->entityName}.{$fieldName}.validation.allowed',
                        $s12'forbidden_message' => '{$this->entityName}.{$fieldName}.validation.forbidden',
                    $s12]
                    PHP;
                }
                break;


            // EMAIL /////////////////////////////////////////////////////////
            case 'email':
                # code...
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'      => '{$this->entityName}.{$fieldName}.{$blockName}.label',
                    $s12'sortable'   => false,
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'      => '{$this->entityName}.{$fieldName}.{$blockName}.label',
                    $s12'type'       => '{$type}',
                    $s12'attributes' => [
                    $formAttr
                    $s12],
                    PHP;
                } elseif ($blockName === 'formatter') {
                    $block = <<<PHP
                    $s08'{$type}' => [
                        $s12// 'max_length' => 5,
                        $s12// 'truncate_suffix',                   // Defaults to ...
                        $s12// 'truncate_suffix' => '...Read More',
                        $s12// 'null_value' => 'Nothing here',      // Replaces null value with string
                        $s12// 'suffix'     => "Boo",               // Appends to end of text
                        $s12// 'transform'  => 'lowercase',
                        $s12// 'transform'  => 'uppercase',
                        $s12// 'transform'  => 'capitalize',
                        $s12// 'transform'  => 'title',
                        $s12// 'transform'  => 'trim',              // notes-: assuming we did not store clean data
                        $s12// 'transform'  => 'last2char_upper',
                    $s12],
                    $s12'text' => [
                        $s12 // 'mask'             => true, // Or false, or omit for default
                    $s12]
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
                    $s08'{$type}' => [ // Default validator, can be refined based on db_type
                        $s12'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                        $s12'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                        $s12// 'ignore_forbidden'  => true,  // Default is false
                        $s12// 'ignore_allowed'    => false, // Default is true
                        $s12//---
                        $s12'required_message'  => '{$this->entityName}.{$fieldName}.validation.required',
                        $s12'invalid_message'   => '{$this->entityName}.{$fieldName}.validation.invalid',
                        $s12'minlength_message' => '{$this->entityName}.{$fieldName}.validation.minlength',
                        $s12'maxlength_message' => '{$this->entityName}.{$fieldName}.validation.maxlength',
                        $s12'pattern_message'   => '{$this->entityName}.{$fieldName}.validation.pattern',
                        $s12'allowed_message'   => '{$this->entityName}.{$fieldName}.validation.allowed',
                        $s12'forbidden_message' => '{$this->entityName}.{$fieldName}.validation.forbidden',
                    $s12],
                    PHP;
                }

                break;
            // EMAIL /////////////////////////////////////////////////////////
            case 'email2':
                # code...
                if ($blockName === 'list') {
                    $block = <<<PHP
    'label'      => '{$blockName}.{$fieldName}',
                'sortable'   => false,
    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
    'label'      => '{$blockName}.{$fieldName}',
                'type'       => 'email',
                'attributes'    => [
    $formAttr
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
    'label'      => '{$blockName}.{$fieldName}',
                'sortable'   => false,
    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
    //  'region' => 'US',
                'label'      => '{$blockName}.{$fieldName}',
                'type'       => 'tel',
                'attributes' => [
    $formAttr
                    // 'xxrequired'              => true,
                    // 'xxlist'                  => 'foo',
                    // 'xxdata-char-counter'     => true,     // js-feature
                    // 'data-live-validation'  => true      // js-feature
                    // 'xxdata-mask'             => 'phone', // todo - mast does not validate.
                    // 'xxdata-country'          => 'pt',    // todo - revisit for validation -  'pattern, maxlength
                    // 'xxstyle' => 'background: cyan;',
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
    'label'      => '{$blockName}.{$fieldName}',
                'sortable'   => false,
    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
    'label'      => '{$blockName}.{$fieldName}',
                'type'       => 'email',
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
