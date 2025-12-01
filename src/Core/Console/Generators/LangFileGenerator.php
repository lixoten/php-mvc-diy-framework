<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Core\Exceptions\SchemaDefinitionException;
use RuntimeException;

// AIChat
// The purpose of this file is to generate the 'main' language file or a 'common' language file
// The main language file"
// --- is related to an entity. Some, not all but some 'translated text' will contain the entity name
// --- So 'This "Post" title is a required field". - u see the work 'Post'.
// The common language file"
// --- is related to an entity. But NONE of the 'translated text' will contain the entity name. it more generic
// --- So 'This "title is a required field". - no mention of 'Post'.
//


/**
 * Service responsible for generating language files (`_lang.php`) for specific entities.
 *
 * This generator reads an entity's schema definition and produces either a 'main'
 * or 'common' language file within the entity's language directory.
 *
 * - The 'main' language file includes the entity name in some translated texts (e.g., "This 'Post' title is required").
 * - The 'common' language file uses generic phrasing, omitting the entity name (e.g., "This title is required").
 *
 * It dynamically generates language keys and their default translations for fields,
 * buttons, and validation messages based on the entity's schema and predefined patterns.
 *
 * @package   MVC LIXO Framework
 * @author    Your Name <your@email.com>
 * @copyright Copyright (c) 2025
 */
class LangFileGenerator
{
    private GeneratorOutputService $generatorOutputService;
    private string $entityNameLowercase;
    private string $entityName;
    private array $fields;
    private string $configType;

    /**
     * @param GeneratorOutputService $generatorOutputService The service for managing generator output.
     */
    public function __construct(
        GeneratorOutputService $generatorOutputService,
    ) {
        $this->generatorOutputService = $generatorOutputService;
    }

     /**
     * Generates a language file (`_lang.php`) for a given entity schema.
     *
     * @param array<string, mixed> $schema The entity schema definition.
     * @param string $configType The type of language file to generate ('main' or 'common').
     * @return string The absolute path to the generated language file.
     * @throws SchemaDefinitionException If the schema is invalid or missing the entity name.
     * @throws \RuntimeException If the output directory cannot be created or file cannot be written.
     */
    public function generate(array $schema, string $configType): string
    {
        if (empty($schema['entity']['name'])) {
            throw new SchemaDefinitionException('Invalid schema: missing entity name.');
        }

        $entity = $schema['entity']['name'];

        if ($configType === 'main') {
            $this->entityName          = $schema['entity']['name'];
            $this->entityNameLowercase = strtolower($schema['entity']['name']);
        } else {
            $this->entityName          = 'Common';
            $this->entityNameLowercase = 'common';
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

        if ($configType === 'main') {
            $filePath = $outputDir . strtolower($entity) . "_lang" . '.php';
        } else {
            $filePath = $outputDir . strtolower($this->entityName) . "_lang" . '.php';
        }

        $fileContent = $this->generateContent();

        $success = file_put_contents($filePath, $fileContent);
        if ($success === false) {
            throw new RuntimeException("Failed to write language file: {$filePath}");
        }

        return $filePath;
    }


    /**
     * Generates the PHP code content for the language file.
     *
     * This method orchestrates the generation of various sections of the language file,
     * including generic terms and field-specific translations, with appropriate alignment.
     *
     * @return string The complete PHP code for the language file.
     */
    protected function generateContent(): string
    {
        $generatedTimestamp = $this->generatorOutputService->getGeneratedFileTimestamp();
        $fieldDefinitions = [];

        $xArray = [
            'form' => [
                'hints' => [
                    'required'      => 'Required field',
                    'minlength'     => 'At least %d characters',
                    'maxlength'     => 'Maximum %d characters',
                    'min'           => 'Minimum value: %s',
                    'max'           => 'Maximum value: %s',
                    'date_min'      => 'Not before: %s',
                    'date_max'      => 'Not after: %s',
                    'pattern'       => 'Must match required format',
                    'email'         => 'Must be a valid email address',
                    'tel'           => 'Enter with country code (e.g., +1234567890)',
                    'url'           => 'Must start with http:// or https://',
                ],
                'heading' => 'Edit Recordccccc',
            ],
            'menu'  => [
                'home' => 'Home',
                'test' => 'Test',
                'about' => 'About',
                'contact' => 'Contact',
                'testy' => 'Testy',
                'head' => [
                    'core' => 'Core',
                    'user' => 'User',
                    'store' => 'Store',
                    'admin' => 'Admin',
                ],
                'profile' => 'Profile',
                // 'settings' => 'Settings',
                'user_manage'     => 'Manage Users',
                'admin_dashboard' => 'Admin Dashboard',
                'store_dashboard' => 'Store Dashboard',
                'store_profile'   => 'Store Profile',
                'store_settings'  => 'Store Settings',
                'user_dashboard' => 'User Dashboard',
                'user_profile'   => 'User Profile',
                'user_settings'  => 'User Settings',
                'user_notes'  => 'User Notes',
                'user_list'  => 'User List',
            ],
            'actions' => 'Actions',
            'button'  => [
                'delete' => 'Delete',
                'edit'   => 'Edit',
                'add'    => 'Add',
                'create' => 'CREAdd',
                'view'   => 'View',
                'save'   => 'Save',
                'cancel' => 'Cancel',
                'view_table' => 'Table View',
                'view_list'  => 'List View',
                'view_grid'  => 'Grid View',
            ],
        ];

        $fieldDefinitionsString2 = '';
        if ($this->configType !== 'main') {
            $fieldDefinitions2 = $this->formatLanguageArrayRecursive($xArray, 1); // Start at indent level 1

            $fieldDefinitionsString2 = implode("\n", $fieldDefinitions2);
        }

        $codeSection = [];
        foreach ($this->fields as $fieldName => $config) {
            if ($this->configType === 'main') {
                if (
                    !in_array(
                        $fieldName,
                        [
                            'id',
                            'gender_id',
                            'state_code',
                            'status',
                            'is_verified',
                            'generic_text',
                            'primary_email',
                        ]
                    )
                ) {
                    continue;
                }
                //$rrr = 1;
            } else {
                if (
                    !in_array(
                        $fieldName,
                        [
                            // 'idXxx', 'store_id', 'user_id', 'name', 'content', 'description',
                            // 'created_at', 'updated_at', 'xxx', 'xxx', 'xxx', 'xxx'
                            // 'primary_email',
                            // 'title',
                            'id',
                            'generic_text',
                            'gender_id',
                            'state_code',
                            'status',
                            'is_verified',
                            'primary_email',
                            // 'slug',
                            'status',
                            'super_powers',
                            // 'primary_email', 'telephone', 'status', 'super_powers'
                        ]
                    )
                ) {
                    continue;
                }
            }

            $fieldType = $this->mapDbTypeToFormType($config, $fieldName);

            $wordSentence   = 'x' . $config['comment'] ?? "No fucking idea";
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

            $s04 = '    ';
            $s08 = '        ';
            $s12 = '            ';
            $s16 = '                ';
            $sections = [];
            if (isset($config['lookup']) && isset($config['codes']) && is_array($config['codes'])) {
                $lookup = $config['lookup'];

                $codes = [];
                foreach ($config['codes'] as $code => $value) {
                    $codes[] = <<<PHP
                    $s12'$code' => '{$value}',
                    PHP;
                }

                $codeString = implode("\n", $codes);

                $block = <<<PHP
                $s04'{$lookup}' => [
                $codeString
                $s08],
                PHP;

                $codeSection[] = <<<PHP
                $s04$block
                PHP;
            }





            if (!$skipList) {
                $temp =  $this->getCodeBlock(
                    fieldName:
                    $fieldName,
                    type: $fieldType,
                    blockName: 'list',
                    wordSentence: $wordSentence
                );
                $sections[] = <<<PHP
                    'list' => [
                        {$temp}
                            ],
                    PHP;
            }
            if (!$skipForm) {
                $temp =  $this->getCodeBlock(
                    fieldName:
                    $fieldName,
                    type: $fieldType,
                    blockName: 'form',
                    wordSentence: $wordSentence
                );
                $sections[] = <<<PHP
                    $s08'form' => [
                        {$temp}
                            ],
                    PHP;
            }
            if (!$skipFormatter) {
                $temp =  $this->getCodeBlock(
                    fieldName:
                    $fieldName,
                    type: $fieldType,
                    blockName: 'formatter',
                    wordSentence: $wordSentence
                );
                $sections[] = <<<PHP
                    $s08'formatters' => [
                        {$temp}
                            ],
                    PHP;
            }
            if (!$skipValidator) {
                $temp =  $this->getCodeBlock(
                    fieldName:
                    $fieldName,
                    type: $fieldType,
                    blockName: 'validator',
                    wordSentence: $wordSentence
                );
                $sections[] = <<<PHP
                    $s08'validation' => [
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
        }


        $codeSectionString = implode("\n", $codeSection);

        $codeSectionString = <<<PHP
            $s04'code' => [
            {$codeSectionString}
                ],
            PHP;
        $codeSectionString = $this->cleanBlock($codeSectionString);
        //$fieldDefinitions2 = $this->formatLanguageArrayRecursive($xArray, 1); // Start at indent level 1

        $fieldDefinitionsString = implode("\n", $fieldDefinitions);

        $php = <<<PHP
<?php

/**
 * Generated File - Date: {$generatedTimestamp}
 * Language File for {$this->entityNameLowercase}_{$this->configType}.
 *
 * This file provides localized strings for the application, specifically for a given entity.
 * Depending on the configuration type ('main' or 'common'), it contains:
 * - Labels for fields in lists and forms.
 * - Placeholder texts for input elements.
 * - Button texts (e.g., 'Add', 'Edit', 'Delete', 'Cancel').
 * - Validation messages (e.g., 'is required', 'minlength').
 * - Other general UI texts and actions.
 *
 * The 'main' type includes the entity name in relevant translations (e.g., "Post title is required"),
 * while the 'common' type provides generic, entity-agnostic phrases (e.g., "title is required").
 */

declare(strict_types=1);

return [
{$codeSectionString}
{$fieldDefinitionsString2}
{$fieldDefinitionsString}
];

PHP;

        return $php;
    }



    /**
     * Generates a language block for a specific field segment (e.g., list, form, validator).
     *
     * This method produces PHP array lines containing default labels, placeholders,
     * or validation messages for a given field and block type, potentially including
     * the entity name based on the generator's configuration type.
     *
     * @param string $fieldName The name of the field (e.g., 'title', 'email').
     * @param string $type The HTML input type mapped from DB type (e.g., 'text', 'email').
     * @param string $blockName The context of the block (e.g., 'list', 'form', 'validator', 'formatter').
     * @param string|null $wordSentence A descriptive phrase or comment for the field's value.
     * @return string The generated PHP code block for the language file.
     */
    protected function getCodeBlock(
        string $fieldName,
        string $type,
        string $blockName,
        string $wordSentence = null
    ): string {
        $block = '';
        $s04 = '    ';
        $s08 = '        ';
        $s12 = '            ';
        $s14 = '                ';
        $s16 = '                    ';
        $snn = '    ';

        if ($this->entityName !== 'Common') {
            $focusName = $this->entityName . " ";
        } else {
            $focusName = '';
        }

        switch ($type) {
            // TEXT /////////////////////////////////////////////////////////
            case 'text':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    $s12'placeholder' => 'Enter {$wordSentence}',
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
                        $s12'required'  => '{$focusName}{$wordSentence} is required.',
                        $s12'invalid'   => 'Invalid {$focusName}{$wordSentence}.',
                        $s12'minlength' => '{$focusName}{$wordSentence} must be at least %d characters.',
                        $s12'maxlength' => '{$focusName}{$wordSentence} must not exceed %d characters.',
                        $s12'pattern'   => '{$focusName}{$wordSentence} does not match the required pattern.',
                        $s12'allowed'   => 'Please select a valid {$focusName}{$wordSentence}.',
                        $s12'forbidden' => 'This {$focusName}{$wordSentence} is not allowed.',
                    PHP;
                }
                break;

            // select /////////////////////////////////////////////////////////
            case 'select':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    PHP;
                } elseif ($blockName === 'form') {
                    // $s12'default_choice' => 'Please select your {$focusName}{$wordSentence}.',
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
                        $s12'required'  => '{$focusName}{$wordSentence} is required.',
                        $s12'invalid'   => 'Invalid {$focusName}{$wordSentence}.',
                    PHP;
                }
                break;
            // radio_group /////////////////////////////////////////////////////////
            case 'radio_group':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    // $s12'default_choice' => 'Please select your {$focusName}{$wordSentence}.',
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
                        $s12'required'  => '{$focusName}{$wordSentence} is required.',
                        $s12'invalid'   => 'Invalid {$focusName}{$wordSentence}.',
                    PHP;
                }
                break;
            // checkbox /////////////////////////////////////////////////////////
            case 'checkbox':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    // $s12'default_choice' => 'Please select your {$focusName}{$wordSentence}.',
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
                        $s12'required'  => '{$focusName}{$wordSentence} is required.',
                        $s12'invalid'   => 'Invalid {$focusName}{$wordSentence}.',
                    PHP;
                }
                break;

                // EMAIL /////////////////////////////////////////////////////////
            case 'email':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    $s12'placeholder' => 'lixoten@gmail.com',
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
                        $s12'required'  => '{$focusName}{$wordSentence} is required.',
                        $s12'invalid'   => 'Invalid {$focusName}{$wordSentence}.',
                        $s12'minlength' => '{$focusName}{$wordSentence} must be at least %d characters.',
                        $s12'maxlength' => '{$focusName}{$wordSentence} must not exceed %d characters.',
                        $s12'pattern'   => '{$focusName}{$wordSentence} does not match the required pattern.',
                        $s12'allowed'   => 'Please select a valid {$focusName}{$wordSentence}.',
                        $s12'forbidden' => 'This {$focusName}{$wordSentence} is not allowed.',
                    PHP;
                }

                break;
            // Number /////////////////////////////////////////////////////////
            case 'number':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'       => '{$wordSentence}',
                    $s12'placeholder' => 'Enter {$wordSentence}',
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block = <<<PHP
                        $s12'required'   => '{$focusName}{$wordSentence} is required.',
                        $s12'invalid'   => '{$focusName}{$wordSentence} must be at least %d characters.',
                        $s12'minlength' => '{$focusName}{$wordSentence} must not exceed %d characters.',
                        $s12'maxlength' => 'Invalid {$focusName}{$wordSentence}.',
                        $s12'pattern'   => '{$focusName}{$wordSentence} does not match the required pattern.',
                        $s12'allowed'   => 'Please select a valid {$focusName}{$wordSentence}.',
                        $s12'forbidden' => 'This {$focusName}{$wordSentence} is not allowed.',
                    PHP;
                }

                break;
            // EMAIL /////////////////////////////////////////////////////////
            case 'email2':
                break;
        }

        return $block;
    }


    private function cleanBlock($block): string
    {
        // 1. Split the string into an array of lines
        $lines = explode("\n", $block);
        // 2. Filter out empty lines (after trimming whitespace from each line)
        $filteredLines = array_filter($lines, function (string $line): bool {
            return trim($line) !== '';
        });

        // 3. Join the remaining lines back into a single string
        $cleanBlock = implode("\n", $filteredLines);

        return $cleanBlock;
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

        if (isset($config['codes']) && is_array($config['codes'])) {
            if (isset($config['form_input_type'])) {
                return $config['form_input_type'];
            } else {
                return 'select';
            }
        }


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


    /**
     * Recursively formats an associative array into aligned PHP array lines for a language file.
     *
     * This method traverses a nested array, applying proper indentation and aligning
     * '=>' operators at each level to generate a readable PHP array string.
     * The entity name is prefixed to string values based on the generator's configType.
     *
     * @param array<string, mixed> $data The associative array containing language key/value pairs.
     * @param int $indentationLevel The current level of indentation (1 for top-level keys, increases
     *                              by 1 for each nested array).
     * @return array<string> An array of formatted PHP code lines.
     */
    private function formatLanguageArrayRecursive(array $data, int $indentationLevel): array
    {
        if ($this->entityName !== 'Common') {
            $focusName = $this->entityName . " ";
        } else {
            $focusName = '';
        }

        $lines = [];
        $currentIndent = str_repeat('    ', $indentationLevel); // 4 spaces per level

        // Calculate max key length for alignment at this specific level
        $maxKeyLen = 0;
        foreach (array_keys($data) as $key) {
            $maxKeyLen = max($maxKeyLen, strlen("'$key'")); // Account for quotes around the key
        }

        foreach ($data as $key => $value) {
            $paddedKey = str_pad("'$key'", $maxKeyLen, ' ', STR_PAD_RIGHT);

            if (is_string($value)) {
                // Assuming 'Testy' is a fixed prefix you want to add
                $lines[] = "{$currentIndent}{$paddedKey} => '{$focusName}{$value}',";
            } elseif (is_array($value)) {
                $lines[] = "{$currentIndent}{$paddedKey} => [";
                // Recursively call for nested array, increasing indentation
                $lines = array_merge($lines, $this->formatLanguageArrayRecursive($value, $indentationLevel + 1));
                $lines[] = "{$currentIndent}],";
            }
            // Add other types if needed (e.g., numbers, booleans)
        }

        return $lines;
    }
}
