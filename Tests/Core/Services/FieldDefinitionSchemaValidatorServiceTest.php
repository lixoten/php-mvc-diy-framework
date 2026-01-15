<?php

declare(strict_types=1);

namespace Tests\Core\Services;

use Core\Services\FieldDefinitionSchemaValidatorService;
use Core\Exceptions\FieldSchemaValidationException;
use Core\Interfaces\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for FieldDefinitionSchemaValidatorService.
 *
 * Tests the validator's ability to:
 * - Validate field definitions against schema
 * - Enforce context-aware validation (list/form/full)
 * - Detect unknown/misplaced configuration keys
 * - Validate attribute types and enum values
 * - Check for duplicated validation rules
 * - Report structured validation errors with dev codes
 *
 * @group playlist
 * @group config-validation
 * @group lixoten
 * @group services
 * @group field-definition
 * @group schema-validation
 */
class FieldDefinitionSchemaValidatorServiceTest extends TestCase
{
    private FieldDefinitionSchemaValidatorService $validator;
    private MockObject|ConfigInterface $configService;
    private MockObject|LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configService = $this->createMock(ConfigInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->validator = new FieldDefinitionSchemaValidatorService(
            $this->configService,
            $this->logger
        );
    }

    /**
     * ✅ Test that validateFieldDefinition() throws when schema file is missing.
     */
    public function testThrowsWhenSchemaFileNotFound(): void
    {
        $this->configService->expects($this->once())
            ->method('get')
            ->with('forms/schema')
            ->willReturn(null);

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage('Form schema (forms/schema.php) not found');

        $this->validator->validateFieldDefinition(
            ['form' => ['type' => 'text']],
            'title',
            'testy_edit',
            'testy',
            'full'
        );
    }

    /**
     * ✅ Test that 'list' context only validates 'list' section.
     */
    public function testValidateListContextOnlyValidatesListSection(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'list' => [
                'sortable' => true,
                'formatter' => fn($v) => htmlspecialchars((string)$v),
            ],
            'form' => [
                'type' => 'text',
            ],
        ];

        // ✅ Should NOT throw exception (list section is valid)
        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_list',
            'testy',
            'list'
        );

        $this->assertTrue(true); // ✅ If we reach here, validation passed
    }

    /**
     * ✅ Test that 'form' context only validates 'form' and 'validators' sections.
     */
    public function testValidateFormContextOnlyValidatesFormSection(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'list' => [
                'sortable' => 'invalid', // ❌ This would fail in 'list' context
            ],
            'form' => [
                'type' => 'text',
                'attributes' => [
                    'maxlength' => 100,
                ],
            ],
        ];

        // ✅ Should NOT throw exception (form section is valid, list section is ignored)
        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'form'
        );

        $this->assertTrue(true);
    }

    /**
     * ✅ Test that 'full' context validates both 'list' and 'form' sections.
     */
    public function testValidateFullContextValidatesBothSections(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'list' => [
                'sortable' => true,
            ],
            'form' => [
                'type' => 'text',
            ],
        ];

        // ✅ Should NOT throw exception (both sections are valid)
        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'full'
        );

        $this->assertTrue(true);
    }

    /**
     * ❌ Test detection of unknown keys in 'list' section.
     */
    public function testDetectsUnknownListKeys(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'list' => [
                'sortable' => true,
                'unknown_key' => 'invalid', // ❌ Unknown key
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("has unknown keys in 'list' section: unknown_key");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_list',
            'testy',
            'list'
        );
    }

    /**
     * ❌ Test detection of invalid 'sortable' type (must be boolean).
     */
    public function testDetectsInvalidSortableType(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'list' => [
                'sortable' => 'yes', // ❌ Should be boolean
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("'list.sortable' must be a boolean");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_list',
            'testy',
            'list'
        );
    }

    /**
     * ⚠️ Test detection of both 'formatter' (singular) and 'formatters' (plural).
     */
    public function testDetectsDuplicateFormatterKeys(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'list' => [
                'formatter' => fn($v) => $v,
                'formatters' => [['name' => 'badge']], // ⚠️ Both present
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("has BOTH 'formatter' (singular) AND 'formatters' (plural)");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'status',
            'testy_list',
            'testy',
            'list'
        );
    }

    /**
     * ❌ Test detection of missing 'form' section in form context.
     */
    public function testDetectsMissingFormSection(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'list' => [
                'sortable' => true,
            ],
            // ❌ Missing 'form' section
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("is missing 'form' section");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of missing 'type' in 'form' section.
     */
    public function testDetectsMissingTypeInFormSection(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                // ❌ Missing 'type'
                'attributes' => ['class' => 'form-control'],
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("has a 'form' section but no 'type' defined");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of unknown field type.
     */
    public function testDetectsUnknownFieldType(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                'type' => 'magic_field', // ❌ Unknown type
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("uses unknown form type 'magic_field'");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of misplaced attribute (should be in 'form.attributes').
     */
    public function testDetectsMisplacedAttribute(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                'type' => 'text',
                'maxlength' => 100, // ❌ Should be in 'attributes'
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("found directly under 'form'");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of unknown attributes in 'form.attributes'.
     */
    public function testDetectsUnknownFormAttributes(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                'type' => 'text',
                'attributes' => [
                    'maxlength' => 100,
                    'unknown_attr' => 'invalid', // ❌ Unknown attribute
                ],
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("has unknown attributes in 'form.attributes': unknown_attr");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of explicitly disallowed attribute for field type.
     */
    public function testDetectsDisallowedAttributeForFieldType(): void
    {
        $schema = [
            'global' => [
                'class' => ['values' => 'string'],
            ],
            'checkbox' => [
                'class' => false, // ❌ Explicitly disallowed
            ],
        ];

        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                'type' => 'checkbox',
                'attributes' => [
                    'class' => 'form-check-input', // ❌ Disallowed for checkbox
                ],
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("uses attribute 'class' which is explicitly disallowed for type 'checkbox'");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'active',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of duplicated validation rules.
     */
    public function testDetectsDuplicatedValidationRules(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                'type' => 'text',
                'attributes' => [
                    'maxlength' => 100, // ⚠️ Also in validators
                ],
            ],
            'validators' => [
                'text' => [
                    'maxlength' => 100, // ❌ Duplicated
                    'maxlength_message' => 'Too long',
                ],
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("Duplicated validation rule(s)");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of invalid attribute value type.
     */
    public function testDetectsInvalidAttributeValueType(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                'type' => 'text',
                'attributes' => [
                    'maxlength' => 'hundred', // ❌ Should be int
                ],
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("Invalid value for 'maxlength'");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of invalid enum value.
     */
    public function testDetectsInvalidEnumValue(): void
    {
        $schema = [
            'global' => [],
            'text' => [
                'step' => ['values' => [1, 5, 10]], // ✅ Enum
            ],
        ];

        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                'type' => 'text',
                'attributes' => [
                    'step' => 3, // ❌ Not in [1, 5, 10]
                ],
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("expected one of [1, 5, 10]");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'quantity',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of unknown validator options.
     */
    public function testDetectsUnknownValidatorOptions(): void
    {
        $schema = [
            'global' => [],
            'text' => [
                'default_validation_rules' => [
                    'maxlength' => ['values' => 'int'],
                ],
            ],
        ];

        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                'type' => 'text',
            ],
            'validators' => [
                'text' => [
                    'unknown_rule' => 100, // ❌ Unknown
                ],
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("has unknown validator options in 'validators.text': unknown_rule");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of 'formatters' key under 'form' for non-tel fields.
     */
    public function testDetectsFormattersKeyUnderFormForNonTelField(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                'type' => 'text',
                'formatters' => [['name' => 'phone']], // ❌ Only allowed for 'tel'
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("has 'formatters' key under 'form', but this is only allowed for 'tel' type");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'phone',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ❌ Test detection of unknown form-level configuration keys.
     */
    public function testDetectsUnknownFormLevelConfigKeys(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'form' => [
                'type' => 'text',
                'unknown_config' => 'invalid', // ❌ Unknown
            ],
        ];

        $this->expectException(FieldSchemaValidationException::class);
        $this->expectExceptionMessage("has unknown form-level configuration key(s) under 'form': unknown_config");

        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'form'
        );
    }

    /**
     * ✅ Test that valid configuration passes all validations.
     */
    public function testValidConfigurationPassesAllValidations(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $fieldDefinition = [
            'list' => [
                'sortable' => true,
                'formatter' => fn($v) => htmlspecialchars((string)$v),
            ],
            'form' => [
                'type' => 'text',
                'attributes' => [
                    'maxlength' => 100,
                    'class' => 'form-control',
                ],
            ],
            'validators' => [
                'text' => [
                    'minlength' => 2,
                    'minlength_message' => 'Too short',
                ],
            ],
        ];

        // ✅ Should NOT throw exception
        $this->validator->validateFieldDefinition(
            $fieldDefinition,
            'title',
            'testy_edit',
            'testy',
            'full'
        );

        $this->assertTrue(true);
    }

    /**
     * ❌ Test that invalid context parameter throws exception.
     */
    public function testThrowsOnInvalidContextParameter(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid validation context: 'invalid'");

        $this->validator->validateFieldDefinition(
            ['form' => ['type' => 'text']],
            'title',
            'testy_edit',
            'testy',
            'invalid' // ❌ Invalid context
        );
    }

    /**
     * ✅ Test that empty field definition is handled gracefully.
     */
    public function testHandlesEmptyFieldDefinitionGracefully(): void
    {
        $schema = $this->getValidSchema();
        $this->configService->method('get')->willReturn($schema);

        // ✅ Should NOT throw exception for 'list' context (list section is optional)
        $this->validator->validateFieldDefinition(
            [],
            'title',
            'testy_list',
            'testy',
            'list'
        );

        $this->assertTrue(true);
    }

    /**
     * Helper method to create a valid schema for testing.
     */
    private function getValidSchema(): array
    {
        return [
            'global' => [
                'class' => ['values' => 'string'],
                'maxlength' => ['values' => 'int'],
                'minlength' => ['values' => 'int'],
                'required' => ['values' => 'bool'],
            ],
            'text' => [
                'default_validation_rules' => [
                    'maxlength' => ['values' => 'int'],
                    'minlength' => ['values' => 'int'],
                ],
            ],
            'tel' => [
                'default_validation_rules' => [
                    'pattern' => ['values' => 'string'],
                ],
            ],
            'checkbox' => [],
        ];
    }
}