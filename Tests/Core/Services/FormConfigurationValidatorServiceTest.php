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
 * - Aggregate multiple errors
 * - Log errors correctly
 *
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
            'render_options' => ['security_level' => 'ultra_high'], // ❌ Invalid
            'form_layout' => [['title' => 'Test', 'fields' => ['field1']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        // Mock field registry to return valid field
        $this->fieldRegistry->method('getFieldWithFallbacks')->willReturn(['label' => 'Field 1']);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        // ✅ Should fail validation
        $this->assertFalse($result['isValid']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString("'security_level' must be one of", $result['errors'][0]);
        $this->assertStringContainsString('ultra_high', $result['errors'][0]);
    }

    /**
     * Test that validate() rejects invalid layout_type enum value.
     */
    public function testValidateRejectsInvalidLayoutType(): void
    {
        $config = [
            'render_options' => ['layout_type' => 'grid_layout'], // ❌ Invalid
            'form_layout' => [['title' => 'Test', 'fields' => ['field1']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')->willReturn(['label' => 'Field 1']);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("'layout_type' must be one of", $result['errors'][0]);
    }

    /**
     * Test that validate() rejects invalid error_display enum value.
     */
    public function testValidateRejectsInvalidErrorDisplay(): void
    {
        $config = [
            'render_options' => ['error_display' => 'popup'], // ❌ Invalid
            'form_layout' => [['title' => 'Test', 'fields' => ['field1']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')->willReturn(['label' => 'Field 1']);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("'error_display' must be one of", $result['errors'][0]);
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

        // ✅ Mock FieldRegistryService to return null for 'nonexistent_field'
        $this->fieldRegistry->expects($this->once())
            ->method('getFieldWithFallbacks')
            ->with('nonexistent_field', 'testy_edit', 'testy')
            ->willReturn(null);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("'nonexistent_field'", $result['errors'][0]);
        $this->assertStringContainsString("could not be found via FieldRegistryService", $result['errors'][0]);
    }

    /**
     * Test that validate() checks hidden fields exist on entity.
     */
    public function testValidateChecksHiddenFieldExistsOnEntity(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [['title' => 'Test', 'fields' => ['title']]],
            'form_hidden_fields' => ['nonexistent_property'],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')->willReturn(['label' => 'Title']);

        // ✅ Mock EntityMetadataService to indicate field does NOT exist
        $this->entityMetadata->expects($this->once())
            ->method('hasField')
            ->with('App\Features\Testy\Testy', 'nonexistent_property')
            ->willReturn(false);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("'nonexistent_property'", $result['errors'][0]);
        $this->assertStringContainsString("not found as a property/getter", $result['errors'][0]);
    }

    /**
     * Test that validate() checks extra fields exist on entity.
     */
    public function testValidateChecksExtraFieldExistsOnEntity(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [['title' => 'Test', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => ['invalid_extra'],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')->willReturn(['label' => 'Title']);

        $this->entityMetadata->expects($this->once())
            ->method('hasField')
            ->with('App\Features\Testy\Testy', 'invalid_extra')
            ->willReturn(false);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("'invalid_extra'", $result['errors'][0]);
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
            'surprise_key' => 'unexpected!', // ❌ Should be flagged
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')->willReturn(['label' => 'Title']);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("Unexpected top-level configuration key found: 'surprise_key'", $result['errors'][0]);
    }

    /**
     * Test that validate() aggregates multiple errors.
     */
    public function testValidateAggregatesMultipleErrors(): void
    {
        $config = [
            'render_options' => [
                'security_level' => 'invalid', // ❌ Error 1
                'layout_type' => 'unknown', // ❌ Error 2
            ],
            'form_layout' => [], // ❌ Error 3 (empty layout)
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertGreaterThanOrEqual(3, count($result['errors'])); // At least 3 errors
    }

    /**
     * Test that validate() logs errors on failure.
     */
    public function testValidateLogsErrorsOnFailure(): void
    {
        $config = [
            'render_options' => ['security_level' => 'invalid'],
            'form_layout' => [['title' => 'Test', 'fields' => ['title']]],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $this->fieldRegistry->method('getFieldWithFallbacks')->willReturn(['label' => 'Title']);

        // ✅ Expect logger->error() to be called once
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Form configuration validation errors detected:',
                $this->callback(function ($context) {
                    return isset($context['errors']) && is_array($context['errors']);
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
                ['title' => 'Section 2', 'fields' => ['status']],
            ],
            'form_hidden_fields' => ['id', 'created_at'],
            'form_extra_fields' => ['telephone', 'address'],
        ];

        // ✅ Mock dependencies to return valid responses
        $this->fieldRegistry->method('getFieldWithFallbacks')->willReturn(['label' => 'Mock Field']);
        $this->entityMetadata->method('hasField')->willReturn(true);

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        // ✅ Should pass validation
        $this->assertTrue($result['isValid']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test that validate() detects empty form_layout.
     */
    public function testValidateDetectsEmptyFormLayout(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [], // ❌ Empty layout
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("'form_layout' cannot be empty", $result['errors'][0]);
    }

    /**
     * Test that validate() handles form_layout sections with missing 'fields' key.
     */
    public function testValidateDetectsFormLayoutSectionWithoutFields(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [
                ['title' => 'Section Without Fields'], // ❌ Missing 'fields'
            ],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("missing required 'fields' array", $result['errors'][0]);
    }

    /**
     * Test that validate() handles form_layout sections with empty 'fields' array.
     */
    public function testValidateDetectsFormLayoutSectionWithEmptyFields(): void
    {
        $config = [
            'render_options' => [],
            'form_layout' => [
                ['title' => 'Section With Empty Fields', 'fields' => []], // ❌ Empty fields
            ],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertFalse($result['isValid']);
        $this->assertStringContainsString("'fields' array cannot be empty", $result['errors'][0]);
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

        $this->fieldRegistry->method('getFieldWithFallbacks')->willReturn(['label' => 'Title']);

        // ✅ Logger should NOT be called when validation passes
        $this->logger->expects($this->never())->method('error');

        $result = $this->validator->validate($config, 'testy_edit', 'testy', 'test_config.php');

        $this->assertTrue($result['isValid']);
    }
}
