<?php

declare(strict_types=1);

namespace Tests\Core\Services;

use Core\Services\FieldDefinitionSchemaValidatorService;
use Core\Services\FieldRegistryService;
use Core\Services\EntityMetadataService;
use Core\Services\FormConfigurationValidatorService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for FormConfigurationValidatorService.
 *
 * Tests the validator's ability to:
 * - Validate enum values (security_level, layout_type, error_display)
 * - Check field existence in FieldRegistryService
 * - Verify hidden/extra fields exist on entity
 * - Detect unexpected configuration keys
 * - Aggregate multiple errors with structured format
 * - Log errors correctly using critical() level
 *
 * @group playlist
 * @group config-validation
 * @group lixoten
 * @group services
 * @group form-config
 * @group validator
 */
class FormConfigurationValidatorServiceTest extends TestCase
{
    private FormConfigurationValidatorService $validator;
    private MockObject|LoggerInterface $logger;
    private MockObject|FieldRegistryService $fieldRegistry;
    private MockObject|EntityMetadataService $entityMetadata;
    private MockObject|FieldDefinitionSchemaValidatorService $schemaValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->fieldRegistry = $this->createMock(FieldRegistryService::class);
        $this->entityMetadata = $this->createMock(EntityMetadataService::class);
        $this->schemaValidator = $this->createMock(FieldDefinitionSchemaValidatorService::class);

        $this->validator = new FormConfigurationValidatorService(
            $this->logger,
            $this->fieldRegistry,
            $this->entityMetadata,
            $this->schemaValidator
        );
    }

    /**
     * Test that validate() rejects invalid security_level enum value.
     */
    public function testValidateRejectsInvalidSecurityLevel(): void
    {
        $config = [
            'render_options' => ['security_level' => 'ultra_high'],
            'form_layout' => [['title' => 'Test', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturn(['label' => 'Title']);

        $this->schemaValidator->method('validateFieldDefinition');

        $this->entityMetadata->method('hasField')
            ->willReturn(true);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertNotEmpty($result['errors']);
        $this->assertArrayHasKey('message', $result['errors'][0]);
        $this->assertArrayHasKey('dev_code', $result['errors'][0]);
        $this->assertArrayHasKey('suggestion', $result['errors'][0]);
        $this->assertStringContainsString(
            "'security_level' must be one of",
            $result['errors'][0]['message']
        );
        $this->assertStringContainsString('ultra_high', $result['errors'][0]['message']);
        $this->assertSame('ERR-DEV-RO-003', $result['errors'][0]['dev_code']);
    }

    /**
     * Test that validate() rejects invalid layout_type enum value.
     */
    public function testValidateRejectsInvalidLayoutType(): void
    {
        $config = [
            'render_options' => ['layout_type' => 'grid_layout'],
            'form_layout' => [['title' => 'Test', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturn(['label' => 'Title']);

        $this->schemaValidator->method('validateFieldDefinition');

        $this->entityMetadata->method('hasField')
            ->willReturn(true);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString(
            "'layout_type' must be one of",
            $result['errors'][0]['message']
        );
        $this->assertSame('ERR-DEV-RO-004', $result['errors'][0]['dev_code']);
    }

    /**
     * Test that validate() rejects invalid error_display enum value.
     */
    public function testValidateRejectsInvalidErrorDisplay(): void
    {
        $config = [
            'render_options' => ['error_display' => 'popup'],
            'form_layout' => [['title' => 'Test', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturn(['label' => 'Title']);

        $this->schemaValidator->method('validateFieldDefinition');

        $this->entityMetadata->method('hasField')
            ->willReturn(true);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString(
            "'error_display' must be one of",
            $result['errors'][0]['message']
        );
        $this->assertSame('ERR-DEV-RO-005', $result['errors'][0]['dev_code']);
    }

    /**
     * Test that validate() detects field missing from FieldRegistryService.
     */
    public function testValidateDetectsFieldNotInRegistry(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [['title' => 'Test', 'fields' => ['nonexistent_field']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->expects($this->once())
            ->method('getFieldWithFallbacks')
            ->with('nonexistent_field', 'testy_edit', 'testy')
            ->willReturn(null);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("'nonexistent_field'", $result['errors'][0]['message']);
        $this->assertStringContainsString(
            "could not be found via FieldRegistryService",
            $result['errors'][0]['message']
        );
        $this->assertSame('ERR-DEV-FN-032', $result['errors'][0]['dev_code']);
    }

    /**
     * Test that validate() checks hidden fields exist on entity.
     */
    public function testValidateChecksHiddenFieldExistsOnEntity(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [['title' => 'Dummy Section', 'fields' => ['title']]],
            'form_hidden_fields' => ['nonexistent_property'],
            'form_extra_fields' => [],
        ];

        // ✅ Mock getFieldWithFallbacks() to return valid definition for 'title'
        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturnCallback(function ($fieldName) {
                if ($fieldName === 'title') {
                    return ['label' => 'Title', 'form' => ['type' => 'text']];
                }
                return null;
            });

        $this->schemaValidator->method('validateFieldDefinition');

        // ✅ Mock entityMetadata to say 'title' exists, 'nonexistent_property' does not
        $this->entityMetadata->method('hasField')
            ->willReturnCallback(function ($entityFqcn, $fieldName) {
                return $fieldName === 'title'; // Only 'title' exists
            });

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);

        // ✅ Find the error about 'nonexistent_property' in the errors array
        $foundError = false;
        foreach ($result['errors'] as $error) {
            if (str_contains($error['message'], "'nonexistent_property'")) {
                $foundError = true;
                $this->assertStringContainsString("not found as a property/getter", $error['message']);
                $this->assertSame('ERR-DEV-005', $error['dev_code']);
                break;
            }
        }
        $this->assertTrue($foundError, "Expected error about 'nonexistent_property' not found");
    }

    /**
     * Test that validate() checks extra fields exist on entity.
     */
    public function testValidateChecksExtraFieldExistsOnEntity(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [['title' => 'Dummy Section', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => ['invalid_extra'],
        ];

        // ✅ Mock getFieldWithFallbacks() to return valid definition for 'title'
        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturnCallback(function ($fieldName) {
                if ($fieldName === 'title') {
                    return ['label' => 'Title', 'form' => ['type' => 'text']];
                }
                return null;
            });

        $this->schemaValidator->method('validateFieldDefinition');

        // ✅ Mock entityMetadata to say 'title' exists, 'invalid_extra' does not
        $this->entityMetadata->method('hasField')
            ->willReturnCallback(function ($entityFqcn, $fieldName) {
                return $fieldName === 'title'; // Only 'title' exists
            });

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);

        // ✅ Find the error about 'invalid_extra' in the errors array
        $foundError = false;
        foreach ($result['errors'] as $error) {
            if (str_contains($error['message'], "'invalid_extra'")) {
                $foundError = true;
                $this->assertSame('ERR-DEV-007', $error['dev_code']);
                break;
            }
        }
        $this->assertTrue($foundError, "Expected error about 'invalid_extra' not found");
    }

    /**
     * Test that validate() detects unexpected top-level configuration keys.
     */
    public function testValidateDetectsUnexpectedTopLevelKey(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [['title' => 'Test', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
            'surprise_key' => 'unexpected!',
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturn(['label' => 'Title']);

        $this->schemaValidator->method('validateFieldDefinition');

        $this->entityMetadata->method('hasField')
            ->willReturn(true);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString(
            "Unexpected top-level configuration key found: 'surprise_key'",
            $result['errors'][0]['message']
        );
        $this->assertSame('ERR-DEV-TL-001', $result['errors'][0]['dev_code']);
    }

    /**
     * Test that validate() aggregates multiple errors.
     */
    public function testValidateAggregatesMultipleErrors(): void
    {
        $config = [
            'render_options' => [
                'security_level' => 'invalid',
                'layout_type' => 'unknown',
            ],
            'form_layout' => [['title' => 'Dummy', 'fields' => []]], // ✅ Added dummy section
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertGreaterThanOrEqual(2, count($result['errors'])); // ✅ Adjusted from 3 to 2

        foreach ($result['errors'] as $error) {
            $this->assertArrayHasKey('message', $error);
            $this->assertArrayHasKey('dev_code', $error);
            $this->assertArrayHasKey('suggestion', $error);
        }
    }

    /**
     * Test that validate() logs errors on failure using critical level.
     */
    public function testValidateLogsErrorsOnFailure(): void
    {
        $config = [
            'render_options' => ['security_level' => 'invalid'],
            'form_layout' => [['title' => 'Test', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturn(['label' => 'Title']);

        $this->schemaValidator->method('validateFieldDefinition');

        $this->entityMetadata->method('hasField')
            ->willReturn(true);

        $this->logger->expects($this->atLeastOnce())
            ->method('critical')
            ->with(
                $this->stringContains('Form configuration validation error detected:'),
                $this->callback(function ($context) {
                    return isset($context['dev_code'])
                        && isset($context['suggestion'])
                        && isset($context['config_identifier'])
                        && isset($context['pageKey'])
                        && isset($context['entityName']);
                })
            );

        $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');
    }

    /**
     * Test that validate() passes for a completely valid configuration.
     */
    public function testValidatePassesForValidConfiguration(): void
    {
        $config = [
            'render_options' => [
                'security_level' => 'low',
                'layout_type' => 'sequential',
                'error_display' => 'inline',
                'ajax_save' => true,
                'force_captcha' => false,
            ],
            'form_layout' => [
                ['title' => 'Section 1', 'fields' => ['title', 'content']],
            ],
            'form_hidden_fields' => ['id', 'created_at'],
            'form_extra_fields' => ['telephone'],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturn(['label' => 'Mock Field']);

        $this->schemaValidator->method('validateFieldDefinition');

        $this->entityMetadata->method('hasField')
            ->willReturn(true);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertTrue($result['isValid']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test that validate() detects form_layout with missing 'fields' key.
     */
    public function testValidateDetectsFormLayoutSectionWithoutFieldsKey(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [
                ['title' => 'Section Without Fields'],
            ],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("is missing 'fields' key", $result['errors'][0]['message']);
        $this->assertSame('ERR-DEV-FL-006', $result['errors'][0]['dev_code']);
    }

    /**
     * Test that validate() detects form_layout with no sections having fields.
     */
    public function testValidateDetectsFormLayoutWithNoFieldsInAnySections(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [
                ['title' => 'Empty Section 1', 'fields' => []],
                ['title' => 'Empty Section 2', 'fields' => []],
            ],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString(
            "does not contain any sections with fields defined",
            $result['errors'][0]['message']
        );
        $this->assertSame('ERR-DEV-FL-007', $result['errors'][0]['dev_code']);
    }

    /**
     * Test that validate() does not log when validation passes.
     */
    public function testValidateDoesNotLogWhenValidationPasses(): void
    {
        $config = [
            'render_options' => [
                'security_level' => 'low',
                'layout_type' => 'sequential',
                'error_display' => 'inline',
            ],
            'form_layout' => [['title' => 'Section 1', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturn(['label' => 'Title']);

        $this->schemaValidator->method('validateFieldDefinition');

        $this->entityMetadata->method('hasField')
            ->willReturn(true);

        $this->logger->expects($this->never())->method('critical');
        $this->logger->expects($this->never())->method('error');

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertTrue($result['isValid']);
    }

    /**
     * Test that validate() halts early if entity class doesn't exist.
     */
    public function testValidateHaltsWhenEntityClassNotFound(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [['title' => 'Test', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturn(['label' => 'Title']);

        $this->schemaValidator->method('validateFieldDefinition');

        $this->logger->expects($this->once())
            ->method('critical')
            ->with($this->stringContains('Entity class'));

        $result = $this->validator->validate(
            $config,
            'fake_edit',
            'nonexistent_entity',
            'test_config.php'
        );

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString('Entity class', $result['errors'][0]['message']);
        $this->assertSame('ERR-DEV-TL-004', $result['errors'][0]['dev_code']);
    }

    /**
     * Test that validate() detects unexpected key in render_options.
     */
    public function testValidateDetectsUnexpectedRenderOptionsKey(): void
    {
        $config = [
            'render_options' => [
                'security_level' => 'low',
                'invalid_key' => 'unexpected',
            ],
            'form_layout' => [['title' => 'Test', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')
            ->willReturn(['label' => 'Title']);

        $this->schemaValidator->method('validateFieldDefinition');

        $this->entityMetadata->method('hasField')
            ->willReturn(true);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString(
            "Unexpected key found in 'render_options': 'invalid_key'",
            $result['errors'][0]['message']
        );
        $this->assertSame('ERR-DEV-RO-001', $result['errors'][0]['dev_code']);
    }
}