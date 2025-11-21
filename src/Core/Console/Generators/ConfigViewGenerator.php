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
class ConfigViewGenerator
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

        $filePath = $outputDir . strtolower($entityName) . "_view_{$configType}" . '.php';
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


        $catchLines1 = [];
        $catchLines2 = [];
        $spaces = ($configType === 'list') ? '        ' : '                ';
        foreach ($fields as $fieldName => $config) {
            if (in_array($fieldName, ['id', 'title', 'generic_text', 'primary_email', 'telephone'])) {
                $catchLines1[] = "$spaces'{$fieldName}',";
            } else {
                $catchLines2[] = "$spaces// '{$fieldName}',";
            }
        }
        $fieldList = implode("\n", $catchLines1);
        $fieldList .= "\n$spaces// ------ \n";
        $fieldList .= implode("\n", $catchLines2);


        if ($configType === 'list') {
            $someListOptionStuff       = $this->getCodeBlock(type: 'list', blockName: 'options');
            $someListPaginationStuff   = $this->getCodeBlock(type: 'list', blockName: 'pagination');
            $someListRenderOptionStuff = $this->getCodeBlock(type: 'list', blockName: 'render_options');
            // $someListFieldStuff        = $this->getCodeBlock(type: 'list', blockName: 'list_fields');
            $fieldDefinitions[] = <<<PHP
    'options' => [ // gen
        {$someListOptionStuff}
    ],
    'pagination' => [
        {$someListPaginationStuff}
    ],
    'render_options' => [
        {$someListRenderOptionStuff}
    ],
    'list_fields' => [
{$fieldList}
    ]
PHP;
        } elseif ($configType === 'edit') {
            $someFormRenderOptionStuff = $this->getCodeBlock(type: 'edit', blockName: 'render_options');
            $someFormLayoutStuff       = $this->getCodeBlock(
                type: 'edit',
                blockName: 'form_layout',
                fieldList: $fieldList
            );
            $someFormHiddenFieldStuff  = $this->getCodeBlock(type: 'edit', blockName: 'form_hidden_fields');
            $fieldDefinitions[] = <<<PHP
    'render_options' => [ // gen
        {$someFormRenderOptionStuff}
    ],
    'form_layout' => [
        {$someFormLayoutStuff}
    ],
    'form_hidden_fields' => [
        {$someFormHiddenFieldStuff}
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
    protected function getCodeBlock(string $type, string $blockName, string $fieldList = null): string
    {
        $block = '';
        switch ($type) {
            // TEXT /////////////////////////////////////////////////////////
            case 'list':
                # code...
                if ($blockName === 'options') {
                    $block = <<<PHP
    'default_sort_key' => 'created_at',
            'default_sort_direction' => 'DESC'
    PHP;
                } elseif ($blockName === 'pagination') {
                    $block = <<<PHP
    'per_page' => 12,
            'window_size' => 2, // Optional: for pagination link window
    PHP;
                } elseif ($blockName === 'render_options') {
                    $block = <<<PHP
    'title'                 =>  'list.posts.title 111',
            'show_actions'          => true,
            'show_action_add'       => true,
            'show_action_edit'      => true,
            'show_action_del'       => true,
            'show_action_view'      => true,
            'show_action_status'    => false,
    PHP;
                }
                break;
            // EMAIL /////////////////////////////////////////////////////////
            case 'edit':
                # code...
                if ($blockName === 'render_options') {
                    $block = <<<PHP
    // 'attributes' => [
            //     'data-ajax_save'         => true,    // js-feature
            //     'data-auto_save'         => true,    // js-feature Enable auto-save/draft for the whole form
            //     'data-use_local_storage' => true,
            // ],
            'ajax_save'         => true,     // js-feature
            'auto_save'         => false,    // js-feature Enable auto-save/draft for the whole form
            'use_local_storage' => false,    // js-feature Use localStorage for drafts
            'data-ajax-save'    => true,

            // 'force_captcha'        => false,
            'layout_type'          => 'sequential', //CONST_L::SEQUENTIAL,    // FIELDSETS / SECTIONS / SEQUENTIAL
            // 'security_level'       => 'low', //CONST_SL::LOW,      // HIGH / MEDIUM / LOW
            // 'error_display'        => 'summary', //CONST_ED::SUMMARY,   // SUMMARY / SUMMARY / INLINE
            'html5_validation'     => false,
            // 'css_form_theme_class' => "form-theme-christmas",
            // 'css_form_theme_file'  => "christmas",
            // 'form_heading'         => "Create Post Parent",
            'submit_text'          => "Savefook",
    PHP;
                } elseif ($blockName === 'form_layout') {
                    $block = <<<PHP
    [
                'title'     => 'Your Title',
                'fields'    => [
    {$fieldList}
                ],
                'divider'   => true
            ],
            // [
            //     'title' => 'Your Favorite',
            //     'fields' => [
            //         'content',
            //         // 'generic_text',
            //         // 'telephone',
            //         // 'date_of_birth',
            //         // 'interest_soccer_ind',
            //         // 'interest_baseball_ind',
            //         // 'interest_football_ind',
            //         // 'interest_hockey_ind',
            //     ],
            //     'divider' => true,
            // ],
    PHP;
                } elseif ($blockName === 'form_hidden_fields') {
                    $block = <<<PHP
    // 'id',
            // 'store_id',
            // 'testyXxxx_user_id',
    PHP;
                }
                break;
            // TEL /////////////////////////////////////////////////////////
            case 'xxxxx':
                # code...
                if ($blockName === 'list') {
                    $block = <<<PHP
    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
    PHP;
                } elseif ($blockName === 'formatter') {
                    $block = <<<PHP
    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
    PHP;
                }
                break;

            default:
                if ($blockName === 'xxxxx') {
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
