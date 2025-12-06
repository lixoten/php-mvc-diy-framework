<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Core\Exceptions\SchemaDefinitionException;
use Core\Form\Schema\FieldSchema;
use Core\Interfaces\ConfigInterface;
use Core\Logger;
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
    private FieldSchema $fieldSchema;
    private Logger $logger;
    private ConfigInterface $config;

    /**
     * param SchemaLoaderService $schemaLoaderService The service for loading schema definitions.
     * @param GeneratorOutputService $generatorOutputService The service for managing generator output.
     */
    public function __construct(
        GeneratorOutputService $generatorOutputService,
        FieldSchema $fieldSchema,
        Logger $logger,
        ConfigInterface $config
    ) {
        $this->generatorOutputService = $generatorOutputService;
        $this->fieldSchema = $fieldSchema;
        $this->logger = $logger;
        $this->config = $config;
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
        $rolling = [];

        foreach ($this->fields as $fieldName => $config) {
            if ($this->configType === 'root') {
                if (
                    !in_array(
                        $fieldName,
                        [
                            // 'primary_email',
                            // 'title',
                            // 'created_at', 'updated_at',
                            'id',
                            'generic_text',
                            'primary_email',
                            // 'slug',
                            'status',
                            // 'super_powers',
                            'gender_id',
                            'state_code',
                            'is_verified',
                            'generic_number',
                            // 'primary_email', 'telephone', 'status', 'super_powers'
                        ]
                    )
                ) {
                    continue;
                }
            } elseif ($this->configType === 'base') {
                if (
                    !in_array(
                        $fieldName,
                        [
                            'created_at', 'updated_at', 'xxx', 'xxx', 'xxx', 'xxx'
                        ]
                    )
                ) {
                    continue;
                }
            } elseif ($this->configType === 'list') {
                if (!in_array($fieldName, ['id', 'xxx', 'xxx'])) {
                    continue;
                }
            } elseif ($this->configType === 'edit') {
                if (!in_array($fieldName, ['id', 'xxx', 'xxx'])) {
                    continue;
                }
            }

            $rolling[] = "// {$fieldName}";

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
                $temp =  $this->getCodeBlock(
                    config: $config,
                    fieldName: $fieldName,
                    type: $fieldType,
                    blockName: 'list'
                );
                $sections[] = <<<PHP
                    'list' => [
                        {$temp}
                            ],
                    PHP;
            }
            if (!$skipForm) {
                $temp =  $this->getCodeBlock(
                    config: $config,
                    fieldName: $fieldName,
                    type: $fieldType,
                    blockName: 'form',
                );
                $sections[] = <<<PHP
                    $s08'form' => [
                        {$temp}
                            ],
                    PHP;
            }
            if (!$skipFormatter) {

                $temp =  $this->getCodeBlock(
                    config: $config,
                    fieldName: $fieldName,
                    type: $fieldType,
                    blockName: 'formatter',
                );
                $sections[] = <<<PHP
                    $s08'formatters' => [
                    {$temp}
                            ],
                    PHP;
            }
            if (!$skipValidator) {
                $temp =  $this->getCodeBlock(
                    config: $config,
                    fieldName: $fieldName,
                    type: $fieldType,
                    blockName: 'validator'
                );
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

        $rolling = implode("\n", $rolling);
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

{$rolling}
return [
{$fieldDefinitionsString}
];

PHP;

        return $php;
    }




    protected function getFormAttr(
        string $fieldName,
        array $config,
        string $fieldType,
        array $fieldFormSchema
    ): ?string {
        if (stripos($fieldName, 'slug') !== false) {
            return null;
        }
        //$fieldFormSchema = $this->fieldSchema->get($fieldType);

        if ($fieldType === 'radio_group') { // it's s string, but this does not use placeholder
            $fieldFormSchema['placeholder'] = null;
            $fieldFormSchema['maxlength'] = null;
            $fieldFormSchema['pattern'] = null;
        }


        $formAttr = [];
        $spaces = '                ';
        if (isset($fieldFormSchema['placeholder'])) {
            $placeholderKey = "{$fieldName}.form.placeholder";
            $formAttr[] = "$spaces'placeholder' => '$placeholderKey',";
        }
        if (in_array($fieldType, ['select'])) {
            // $formAttr[] = "$spaces'class'       => 'form-select',";
        }
        if (isset($fieldFormSchema['required'])) {
            if (isset($config['required']) && $config['required']) {
                $formAttr[] = "$spaces'required'    => {$this->formatBool($config['required'])},";
            } else {
                $formAttr[] = "$spaces// 'required'    => false,";
            }
        }

        if (isset($fieldFormSchema['minlength'])) {
            if ((isset($config['minlength']))) {
                $formAttr[] = "$spaces'minlength'   => {$config['minlength']},";
            }
        }

        if (isset($fieldFormSchema['maxlength'])) {
            if (isset($config['length'])) {
                if ((isset($config['maxlength']) && $config['maxlength'] <= $config['length'])) {
                    $formAttr[] = "$spaces'maxlength'   => {$config['maxlength']},";
                } else {
                    $formAttr[] = "$spaces'maxlength'   => {$config['length']},";
                }
            } else {
                $this->logger->warningDev("'length' is missing in 'Form Schema' File for $fieldName", "ERR-DEV93");
            }
        }


        if (isset($fieldFormSchema['min'])) {
            if ((isset($config['min']))) {
                $formAttr[] = "$spaces'min'   => {$config['min']},";
            }
        }

        if (isset($fieldFormSchema['max'])) {
            if ((isset($config['max']))) {
                $formAttr[] = "$spaces'max'   => {$config['max']},";
            }
        }

        if (isset($fieldFormSchema['pattern'])) {
            if ((isset($config['pattern']))) {
                $formAttr[] = "$spaces'pattern'     => '{$config['pattern']}',";
            } else {
                if ($fieldType == 'email') {
                    $formAttr[] = "$spaces// 'pattern'     => '/^user[a-z0-9._%+-]*@/',";
                } else {
                    $formAttr[] = "$spaces// 'pattern'     => '/^[a-z0-9./',";
                }
            }
        }

        if (isset($fieldFormSchema['style'])) {
            if ((isset($config['style']))) {
                $formAttr[] = "$spaces'style'       => {$config['style']},";
            } else {
                $formAttr[] = "$spaces// 'style'       => 'background:yellow;',";
            }
        }

        if (isset($fieldFormSchema['class'])) {
            if ((isset($config['class']))) {
                $formAttr[] = "$spaces'class'       => {$config['class']},";
            }
        }

        if (isset($fieldFormSchema['data-char-counter'])) {
            if (in_array('text', ['text'])) {
                if (isset($config['data-char-counter']) && $config['data-char-counter']) {
                    $val = $this->formatBool($config['data-char-counter']);
                    $formAttr[] = $spaces . "'data-char-counter'    => " . $val . ",";
                } else {
                    $formAttr[] = "$spaces// 'data-char-counter'    => false,";
                }
            }
        }

        if (isset($fieldFormSchema['data-live-validation'])) {
            if (isset($config['data-live-validation']) && $config['data-live-validation']) {
                $formAttr[] =
                "$spaces'data-live-validation' => {$this->formatBool($config['data-live-validation'])},";
            } else {
                $formAttr[] = "$spaces// 'data-live-validation' => false,";
            }
        }


        if (isset($fieldFormSchema['data-mask'])) {
            if (in_array($fieldType, ['telephone'])) {
                if ((isset($config['data-mask']))) {
                    $formAttr[] = "$spaces'data-mask' => {$config['data-mask']},";
                } else {
                    $formAttr[] = "$spaces// 'data-mask' => 'data-mask',";
                }
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
        $formAttr = implode("\n", $formAttr);

        return $formAttr;
    }


    protected function getFormOptionsProvider(
        string $fieldName,
        array $config,
        // string $fieldType,
    ): ?string {
        // return null;
        $s12 = '            ';
        if (isset($config['lookup'])) {
            $lookup = $config['lookup'];

            if (isset($config['enum_class']) && str_contains($config['enum_class'], 'Status')) {
                $numClass = $config['enum_class'];
                $temp[] = "$s12'options_provider' => [\App\Enums\\{$numClass}::class, 'toSelectArray'],";
            } elseif (isset($config['db_type']) && $config['db_type'] === 'boolean') {
                return null;
            } else {
                $temp[] = "$s12'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, " .
                                                                                                 "'getSelectChoices'],";
                $temp[] = "$s12'options_provider_params' => ['type' => '{$lookup}'],";
            }

            if (isset($config['form_input_type']) && $config['form_input_type'] === 'radio_group') {
                $temp[] = "$s12// 'default_choice'   => '{$fieldName}.form.default_choice',";
            } else {
                $temp[] = "$s12'default_choice'   => '{$fieldName}.form.default_choice',";
            }
            // $temp[] = "xxx";
            $temp = implode("\n", $temp);
            return $temp;
        }

        return null;
    }

    protected function getFormatterOptionsProvider(
        string $fieldName,
        array $config,
        // string $fieldType,
    ): ?string {
        // return null;
        $s12 = '            ';
        $s16 = '                ';
        if (isset($config['lookup'])) {
            $lookup = $config['lookup'];

            // if ($config['db_type'] === 'enum') {
            if (isset($config['enum_class']) && str_contains($config['enum_class'], 'Status')) {
                $numClass = $config['enum_class'];
                $temp[] = "$s12'text' => [";
                $temp[] = "    $s12'options_provider' => [\App\Enums\\{$numClass}::class, 'getFormatterOptions'],";
                $temp[] = "$s12],";
                $temp[] = "$s12// 'badge' => [";
                $temp[] = "$s12//     'options_provider' => [TestyStatus::class, 'getFormatterOptions'],";
                $temp[] = "$s12// ],";
            } else {
                $temp[] = "$s12'text' => [";
                $temp[] = "    $s12'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, " .
                                                                                              "'getFormatterOptions'],";
                $temp[] = "    $s12'options_provider_params' => ['type' => '{$lookup}'],";
                $temp[] = "$s12],";
            }
            // $temp[] = "xxx";

            $temp = implode("\n", $temp);
            return $temp;
        }

        return null;
    }



    protected function getValidatorAttr(
        string $fieldName,
        array $config,
        string $fieldType,
        array $fieldFormSchema
    ): ?string {
        if (stripos($fieldName, 'slug') !== false) {
            return null;
        }
        //$fieldFormSchema = $this->fieldSchema->get($fieldType);

        $items = [];
        $spaces = '                ';
        $spaces = '';
        $valItems = $fieldFormSchema['val_fields'];



        foreach ($valItems as $item => $foo) {
            $commentOut = '';
            if (
                str_contains($item, 'allowed') ||
                str_contains($item, 'forbidden') ||
                str_contains($item, 'positive') ||
                str_contains($item, 'negative') ||
                str_contains($item, 'enforce')
            ) {
                $commentOut = '// ';
            }

            if (!str_contains($item, 'message')) {
                // $items[] = "$spaces'{$item}' => {$foo['default']},";
                if ($item === 'allowed' || $item === 'forbidden') {
                    $rrr = implode(', ', $foo['default']);
                    $items[] = "$spaces$commentOut'{$item}' => [$rrr],";
                } else {
                    $items[] = "$spaces$commentOut'{$item}' => {$this->formatBool((bool)$foo['default'])},";
                }
            }
        }

        $items[] = "$spaces// --";

        if (isset($fieldFormSchema['required'])) {
            $items[] = "$spaces'required_message' => '{$fieldName}.validation.required',";
        }
        // if (isset($fieldFormSchema['invalid'])) {
            $items[] = "$spaces'invalid_message' => '{$fieldName}.validation.invalid',";
        // }
        if (isset($fieldFormSchema['minlength'])) {
            $items[] = "$spaces'minlength_message' => '{$fieldName}.validation.minlength',";
        }
        if (isset($fieldFormSchema['maxlength'])) {
            $items[] = "$spaces'maxlength_message' => '{$fieldName}.validation.maxlength',";
        }

        if (isset($fieldFormSchema['min'])) {
            $items[] = "$spaces'min_message' => '{$fieldName}.validation.min',";
        }
        if (isset($fieldFormSchema['max'])) {
            $items[] = "$spaces'max_message' => '{$fieldName}.validation.max',";
        }

        if (isset($fieldFormSchema['pattern'])) {
            $items[] = "$spaces'pattern_message' => '{$fieldName}.validation.pattern',";
        }



        foreach ($valItems as $item => $foo) {
            $commentOut = '';
            if (
                str_contains($item, 'allowed') ||
                str_contains($item, 'forbidden') ||
                str_contains($item, 'positive') ||
                str_contains($item, 'negative') ||
                str_contains($item, 'enforce')
            ) {
                $commentOut = '// ';
            }

            if (!str_contains($item, 'message') && !str_contains($item, 'ignore')) {
                $items[] = "$spaces{$commentOut}'{$item}_message' => '{$fieldName}.validation.{$item}',";

            }
        }

        $items = $this->alignArrayDefinition($items, 4); // Start at indent level 1

        $items = implode("\n", $items);
        $s08 = '        ';
        $s12 = '            ';

        $items = <<<PHP
        $s08'{$fieldType}' => [
        $items
        $s12],
        PHP;

        return $items;
    }



    protected function getCodeBlock(
        array $config = null,
        string $fieldName,
        string $type,
        string $blockName,
    ): string {
        $block = '';
        $s04 = '    ';
        $s08 = '        ';
        $s12 = '            ';
        $s14 = '                ';
        $s16 = '                    ';
        $snn = '    ';

        if ($blockName === 'list') {
            $sortable = isset($config['sortable']) ? ($config['sortable'] ? 'true' : 'false') : 'false';
        } elseif ($blockName === 'form') {
            $fieldFormSchema          = $this->fieldSchema->get($type);
            $formAttr                 = $this->getFormAttr($fieldName, $config, $type, $fieldFormSchema);
            $formOptionsProvider      = $this->getFormOptionsProvider($fieldName, $config);
        } elseif ($blockName === 'formatter') {
            // $formatter = isset($config['formatter']) ? 'null' : 'null'; // Can be enhanced to support closures
            $formatterOptionsProvider = $this->getFormatterOptionsProvider($fieldName, $config);
        } elseif ($blockName === 'validator') {
            $fieldFormSchema          = $this->fieldSchema->get($type);
            $formValidator = $this->getValidatorAttr($fieldName, $config, $type, $fieldFormSchema);
        }


        switch ($type) {
            case 'number':
                # code...
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'sortable'   => $sortable,
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'type'       => '{$type}',
                    $s12'attributes' => [
                    $formAttr
                    $s12],
                    PHP;
                } elseif ($blockName === 'formatter') {
                    $textFormatterSample = $this->getTextFormatterSample();
                    $block .= $textFormatterSample;
                } elseif ($blockName === 'validator') {
                    $block .= <<<PHP
                    $formValidator
                    PHP;
                }
                break;


            // EMAIL /////////////////////////////////////////////////////////
            case 'email':
                # code...
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'sortable'   => false,
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'type'       => '{$type}',
                    $s12'attributes' => [
                    $formAttr
                    $s12],
                    PHP;
                } elseif ($blockName === 'formatter') {
                    $block = <<<PHP
                    $s12'{$type}' => [
                        $s12// 'mask'             => true, // Or false, or omit for default
                    $s12],\n
                    PHP;
                    $block .= $this->getTextFormatterSample();
                } elseif ($blockName === 'validator') {
                    $block .= <<<PHP
                    $formValidator
                    PHP;
                }

                break;
            // TEXT /////////////////////////////////////////////////////////
            case 'text':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'sortable'   => false,
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'type'       => '{$type}',
                    $s12'attributes' => [
                    $formAttr
                    $s12],
                    PHP;
                } elseif ($blockName === 'formatter') {
                    $block .= $this->getTextFormatterSample();
                } elseif ($blockName === 'validator') {
                    $block .= <<<PHP
                    $formValidator
                    PHP;
                }
                break;

            // xxxxxx /////////////////////////////////////////////////////////
            // select /////////////////////////////////////////////////////////
            case 'select':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'sortable'   => false,
                    PHP;
                } elseif ($blockName === 'form') {
                    // $s12'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getSelectChoices'],
                    // $s12'options_provider_params' => ['type' => '{$lookup}'],
                    // $s12'default_choice'   => '{$fieldName}.{$blockName}.default_choice',
                    $lookup = $config['lookup'];
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'type'       => '{$type}',
                    $formOptionsProvider
                    $s12'attributes' => [
                    $formAttr
                    $s12],
                    PHP;
                } elseif ($blockName === 'formatter') {
                    // $s12'text' => [
                    //     $s12'options_provider' => [
                    //         $s16\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'
                    //     $s12],
                    //     $s12'options_provider_params' => ['type' => '{$lookup}'],
                    // $s12],
                    $lookup = $config['lookup'];
                    $block = <<<PHP
                    $formatterOptionsProvider
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block .= <<<PHP
                    $formValidator
                    PHP;
                }
                break;

            // xxxxxx /////////////////////////////////////////////////////////
            // radio_group /////////////////////////////////////////////////////
            case 'radio_group':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'sortable'   => false,
                    PHP;
                } elseif ($blockName === 'form') {
                    // $s12'options_provider' => [\Core\Interfaces\CodeLookupServiceInterface::class, 'getSelectChoices'],
                    // $s12'options_provider_params' => ['type' => '{$lookup}'],
                    // $s12// 'default_choice'   => '{$fieldName}.{$blockName}.default_choice',
                    $lookup = $config['lookup'];
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'type'       => '{$type}',
                    $formOptionsProvider
                    $s12'attributes' => [
                    $formAttr
                    $s12],
                    PHP;
                } elseif ($blockName === 'formatter') {
                    // $s12'text' => [
                    //     $s12'options_provider' => [
                    //         $s16\Core\Interfaces\CodeLookupServiceInterface::class, 'getFormatterOptions'
                    //     $s12],
                    //     $s12'options_provider_params' => ['type' => '{$lookup}'],
                    // $s12],
                    $lookup = $config['lookup'];
                    $block = <<<PHP
                    $formatterOptionsProvider
                    PHP;
                } elseif ($blockName === 'validator') {
                     $block .= <<<PHP
                    $formValidator
                    PHP;
                }
                break;
            // checkbox /////////////////////////////////////////////////////
            case 'checkbox':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'sortable'   => false,
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'type'       => '{$type}',
                    $s12'attributes' => [
                    $formAttr
                    $s12],
                    PHP;
                } elseif ($blockName === 'formatter') {
                    $lookup = $config['lookup'];
                    $block = <<<PHP
                    $formatterOptionsProvider
                    $s12// 'badge' => [
                    $s12//     'options_provider' => [\App\Features\Testy\Testy::class, 'getIsVerifiedBadgeOptions'],
                    $s12// ],
                    PHP;
                } elseif ($blockName === 'validator') {
                    $block .= <<<PHP
                    $formValidator
                    PHP;
                }
                break;

            // xxxxxx /////////////////////////////////////////////////////////
            // DATETIME-LOCAL /////////////////////////////////////////////////
            case 'datetime-local':
                if ($blockName === 'list') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'sortable'   => false,
                    PHP;
                } elseif ($blockName === 'form') {
                    $block = <<<PHP
                    $s08'label'      => '{$fieldName}.{$blockName}.label',
                    $s12'type'       => '{$type}',
                    $s12'attributes' => [
                    $formAttr
                    $s12],
                    PHP;
                } elseif ($blockName === 'formatter') {
                    $block = '';
                } elseif ($blockName === 'validator') {
                    $block = '';
                }
                break;

            // xxxxxx /////////////////////////////////////////////////////////

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


    private function getTextFormatterSample(): string
    {
        $s04 = '    ';
        $s08 = '        ';
        $s12 = '            ';
        $block = <<<PHP
        $s12'text' => [
            $s12// 'xxxxxxmax_length' => 5,
            $s12// 'truncate_suffix' => '...',          // Defaults to ...
            $s12// 'null_value' => 'Nothing here',      // Replaces null value with string
            $s12// 'suffix'     => "Boo",               // Appends to end of text
            $s12// 'transform'  => 'lowercase',
            $s12// 'transform'  => 'uppercase',
            $s12// 'transform'  => 'capitalize',
            $s12// 'transform'  => 'title',
            $s12// 'transform'  => 'trim',              // notes-: assuming we did not store clean data
            $s12// 'transform'  => 'last2char_upper',
        $s12],
        PHP;
        return $block;
    }



    private function getTextValidatorSample(string $fieldName, string $type): string
    {
        $s04 = '    ';
        $s08 = '        ';
        $s12 = '            ';

        if ($type === 'number') {
            $block = <<<PHP
            $s08'{$type}' => [ // Default validator, can be refined based on db_type
                $s12'required_message'  => '{$fieldName}.validation.required',
                $s12'invalid_message'   => '{$fieldName}.validation.invalid',
                $s12'min_message'       => '{$fieldName}.validation.min',
                $s12'max_message'       => '{$fieldName}.validation.max',
                $s12'pattern_message'   => '{$fieldName}.validation.pattern',
            $s12],
            PHP;
        } else {
            $block = <<<PHP
            $s08'{$type}' => [ // Default validator, can be refined based on db_type
                $s12'forbidden'         => ['fook', 'shit'], // allows to add on to existing
                $s12'allowed'           => ['fee', 'foo'],   // allows to add on to existing
                $s12// 'ignore_forbidden'  => true,  // Default is false
                $s12// 'ignore_allowed'    => false, // Default is true
                $s12//---
                $s12'required_message'  => '{$fieldName}.validation.required',
                $s12'invalid_message'   => '{$fieldName}.validation.invalid',
                $s12'minlength_message' => '{$fieldName}.validation.minlength',
                $s12'maxlength_message' => '{$fieldName}.validation.maxlength',
                $s12'pattern_message'   => '{$fieldName}.validation.pattern',
                $s12'allowed_message'   => '{$fieldName}.validation.allowed',
                $s12'forbidden_message' => '{$fieldName}.validation.forbidden',
            $s12],
            PHP;
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

        if (isset($config['codes']) && is_array($config['codes'])) {
            if ($config['db_type'] === 'boolean') {
                return 'checkbox';
            } elseif (isset($config['form_input_type'])) {
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


    protected function alignArrayDefinition(array $lines, int $indentationLevel) {
        // 1. Find the maximum key length for alignment
        $max_key_length = 0;

        $currentIndent = str_repeat('    ', $indentationLevel); // 4 spaces per level


        // A regular expression to capture the key (the part before '=>')
        // It looks for a sequence of non-whitespace characters that are NOT '=>',
        // preceded by optional whitespace and a single quote/double quote,
        // and followed by the '=>' operator.
        $regex = "/^(\s*['\"]?)([^'\"]+?)(['\"]?\s*)=>/";
        $regex = "/^(\s*(?:\/\/)?\s*['\"]?)([^'\"]+?)(['\"]?\s*)=>/";

        foreach ($lines as $line) {
            // Only process lines that contain an assignment operator '=>'
            if (strpos($line, '=>') !== false) {
                if (preg_match($regex, $line, $matches)) {
                    // $matches[2] is the key itself (e.g., 'required_message' or the longer commented key)
                    $key = $matches[2];
                    $max_key_length = max($max_key_length, strlen($key));
                }
            }
        }

        // Adjust for quotes and delimiters if needed. Since the keys might be quoted or not,
        // we'll pad the key part *before* the '=>' operator.

        // 2. Format and pad each line
        $formatted_lines = [];
        foreach ($lines as $line) {
            if (strpos($line, '=>') !== false) {
                if (preg_match($regex, $line, $matches)) {
                    $leading_indent = $matches[1]; // e.g., "               // '" or "                '"
                    $key = $matches[2];             // e.g., 'required_message'
                    $trailing_separator = $matches[3]; // e.g., "' " or ""

                    // The part of the line that comes *after* '=>'
                    $value_part = substr($line, strpos($line, '=>') + 2);

                    // Calculate padding needed to reach the max key length
                    $padding = str_repeat(' ', $max_key_length - strlen($key));

                    // Reconstruct the line with padding
                    $formatted_line = $leading_indent . $key . $trailing_separator . $padding . '=>' . $value_part;
                    $formatted_lines[] = $currentIndent . $formatted_line;

                } else {
                    // If it contains '=>' but doesn't match the regex, add the original line
                    $formatted_lines[] = $currentIndent . $line;
                }
            } else {
                // Lines without '=>' are added as is
                $formatted_lines[] = $currentIndent . $line;
            }
        }

        return $formatted_lines;
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
