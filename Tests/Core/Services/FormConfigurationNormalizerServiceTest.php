<?php

declare(strict_types=1);

namespace Tests\Core\Services;

use Core\Services\FormConfigurationNormalizerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for FormConfigurationNormalizerService.
 *
 * Tests the normalizer's ability to:
 * - Apply default values for missing keys
 * - Cast types (string → bool, ensure arrays are arrays)
 * - Log warnings for correctable data issues
 * - Handle edge cases (null, empty arrays, wrong types)
 *
 * @group lixoten
 * @group services
 * @group form-config
 * @group normalizer
 */
class FormConfigurationNormalizerServiceTest extends TestCase
{
    private FormConfigurationNormalizerService $normalizer;
    private MockObject|LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->normalizer = new FormConfigurationNormalizerService($this->logger);
    }

    /**
     * Test that normalize() applies default values for missing top-level keys.
     */
    public function testNormalizeAppliesDefaultsForMissingTopLevelKeys(): void
    {
        $input = [];

        $result = $this->normalizer->normalize($input);

        // ✅ All top-level keys should exist with defaults
        $this->assertArrayHasKey('render_options', $result);
        $this->assertArrayHasKey('form_layout', $result);
        $this->assertArrayHasKey('form_hidden_fields', $result);
        $this->assertArrayHasKey('form_extra_fields', $result);

        // ✅ form_layout, form_hidden_fields, form_extra_fields should be empty arrays
        $this->assertSame([], $result['form_layout']);
        $this->assertSame([], $result['form_hidden_fields']);
        $this->assertSame([], $result['form_extra_fields']);

        // ✅ render_options should be populated with defaults
        $this->assertIsArray($result['render_options']);
    }

    /**
     * Test that normalize() applies default values for missing render_options.
     */
    public function testNormalizeAppliesDefaultsForMissingRenderOptions(): void
    {
        $input = ['render_options' => []];

        $result = $this->normalizer->normalize($input);

        // ✅ Boolean defaults
        $this->assertFalse($result['render_options']['ajax_save']);
        $this->assertFalse($result['render_options']['force_captcha']);
        $this->assertFalse($result['render_options']['csrf_token']);
        $this->assertFalse($result['render_options']['show_required_asterisks']);
        $this->assertFalse($result['render_options']['show_optional_labels']);
        $this->assertFalse($result['render_options']['auto_focus_first_field']);

        // ✅ String defaults
        $this->assertSame('low', $result['render_options']['security_level']);
        $this->assertSame('sequential', $result['render_options']['layout_type']);
        $this->assertSame('inline', $result['render_options']['error_display']);

        // ✅ Array defaults
        $this->assertSame([], $result['render_options']['attributes']);
        $this->assertSame([], $result['render_options']['themes']);
    }

    /**
     * Test that normalize() casts boolean strings to actual booleans.
     */
    public function testNormalizeCastsBooleanStringsToActualBooleans(): void
    {
        $input = [
            'render_options' => [
                'ajax_save' => 'true',
                'force_captcha' => '1',
                'csrf_token' => 1,
                'show_required_asterisks' => 'false',
                'show_optional_labels' => '0',
                'auto_focus_first_field' => 0,
            ]
        ];

        $result = $this->normalizer->normalize($input);

        // ✅ Truthy values cast to true
        $this->assertTrue($result['render_options']['ajax_save']);
        $this->assertTrue($result['render_options']['force_captcha']);
        $this->assertTrue($result['render_options']['csrf_token']);

        // ✅ Falsy values cast to false
        $this->assertFalse($result['render_options']['show_required_asterisks']);
        $this->assertFalse($result['render_options']['show_optional_labels']);
        $this->assertFalse($result['render_options']['auto_focus_first_field']);
    }

    /**
     * Test that normalize() ensures string render_options remain strings.
     */
    public function testNormalizeEnsuresStringRenderOptionsRemainStrings(): void
    {
        $input = [
            'render_options' => [
                'security_level' => 123, // Non-string, should be cast
                'layout_type' => 'sequential', // Already string
                'error_display' => null, // Null, should get default
            ]
        ];

        $result = $this->normalizer->normalize($input);

        // ✅ Non-string cast to string
        $this->assertSame('123', $result['render_options']['security_level']);

        // ✅ Already string preserved
        $this->assertSame('sequential', $result['render_options']['layout_type']);

        // ✅ Null replaced with default
        $this->assertSame('inline', $result['render_options']['error_display']);
    }

    /**
     * Test that normalize() ensures array render_options remain arrays.
     */
    public function testNormalizeEnsuresArrayRenderOptionsRemainArrays(): void
    {
        $input = [
            'render_options' => [
                'attributes' => 'not_an_array', // Should be cast to []
                'themes' => ['theme1' => ['css' => 'style.css']], // Already array
            ]
        ];

        // ✅ Logger should warn about 'attributes' type mismatch
        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains("'attributes' was not an array"));

        $result = $this->normalizer->normalize($input);

        // ✅ Non-array cast to empty array
        $this->assertSame([], $result['render_options']['attributes']);

        // ✅ Already array preserved
        $this->assertIsArray($result['render_options']['themes']);
        $this->assertArrayHasKey('theme1', $result['render_options']['themes']);
    }

    /**
     * Test that normalize() filters invalid form_layout sections.
     */
    public function testNormalizeFiltersInvalidFormLayoutSections(): void
    {
        $input = [
            'form_layout' => [
                ['title' => 'Valid Section', 'fields' => ['field1']],
                'not_an_array', // Invalid, should be skipped
                null, // Invalid, should be skipped
                ['title' => 'Another Valid', 'fields' => ['field2']],
            ]
        ];

        $result = $this->normalizer->normalize($input);

        // ✅ Only valid sections remain
        $this->assertCount(2, $result['form_layout']);
        $this->assertSame('Valid Section', $result['form_layout'][0]['title']);
        $this->assertSame('Another Valid', $result['form_layout'][1]['title']);
    }

    /**
     * Test that normalize() ensures form_hidden_fields is an array.
     */
    public function testNormalizeEnsuresFormHiddenFieldsIsArray(): void
    {
        $input = [
            'form_hidden_fields' => 'id,created_at', // Invalid, should be cast to []
        ];

        $result = $this->normalizer->normalize($input);

        // ✅ Non-array cast to empty array
        $this->assertSame([], $result['form_hidden_fields']);
    }

    /**
     * Test that normalize() ensures form_extra_fields is an array.
     */
    public function testNormalizeEnsuresFormExtraFieldsIsArray(): void
    {
        $input = [
            'form_extra_fields' => null, // Invalid, should be cast to []
        ];

        $result = $this->normalizer->normalize($input);

        // ✅ Null cast to empty array
        $this->assertSame([], $result['form_extra_fields']);
    }

    /**
     * Test that normalize() handles deeply nested themes correctly.
     */
    public function testNormalizeHandlesDeeplyNestedThemes(): void
    {
        $input = [
            'render_options' => [
                'themes' => [
                    'valid_theme' => ['css' => 'style.css', 'class' => 'my-class'],
                    'invalid_theme' => 'not_an_array', // Should be normalized
                    123 => ['css' => 'numeric_key.css'], // Non-string key, should be skipped
                ]
            ]
        ];

        $result = $this->normalizer->normalize($input);

        // ✅ Valid theme preserved
        $this->assertArrayHasKey('valid_theme', $result['render_options']['themes']);
        $this->assertSame('style.css', $result['render_options']['themes']['valid_theme']['css']);

        // ✅ Invalid theme normalized to defaults
        $this->assertArrayHasKey('invalid_theme', $result['render_options']['themes']);
        $this->assertSame('', $result['render_options']['themes']['invalid_theme']['css']);
        $this->assertSame('', $result['render_options']['themes']['invalid_theme']['class']);

        // ✅ Numeric key removed
        $this->assertArrayNotHasKey(123, $result['render_options']['themes']);
    }

    /**
     * Test that normalize() logs warnings for type mismatches.
     */
    public function testNormalizeLogsWarningsForTypeMismatches(): void
    {
        $input = [
            'render_options' => [
                'attributes' => 'should_be_array',
                'themes' => 'should_be_array',
            ]
        ];

        // ✅ Expect 2 warnings (one for 'attributes', one for 'themes')
        $this->logger->expects($this->exactly(2))
            ->method('warning')
            ->with($this->stringContains('was not an array'));

        $this->normalizer->normalize($input);
    }

    /**
     * Test that normalize() preserves valid nested render_options.
     */
    public function testNormalizePreservesValidNestedRenderOptions(): void
    {
        $input = [
            'render_options' => [
                'ajax_save' => true,
                'security_level' => 'high',
                'attributes' => ['data-test' => 'value'],
                'themes' => [
                    'dark' => ['css' => 'dark.css', 'class' => 'dark-mode']
                ]
            ]
        ];

        $result = $this->normalizer->normalize($input);

        // ✅ Valid values preserved exactly
        $this->assertTrue($result['render_options']['ajax_save']);
        $this->assertSame('high', $result['render_options']['security_level']);
        $this->assertSame(['data-test' => 'value'], $result['render_options']['attributes']);
        $this->assertSame('dark.css', $result['render_options']['themes']['dark']['css']);
    }

    /**
     * Test that normalize() handles completely empty input.
     */
    public function testNormalizeHandlesCompletelyEmptyInput(): void
    {
        $input = [];

        $result = $this->normalizer->normalize($input);

        // ✅ Should return fully defaulted structure
        $this->assertIsArray($result);
        $this->assertArrayHasKey('render_options', $result);
        $this->assertArrayHasKey('form_layout', $result);
        $this->assertArrayHasKey('form_hidden_fields', $result);
        $this->assertArrayHasKey('form_extra_fields', $result);
    }

    /**
     * Test that normalize() handles null values in render_options.
     */
    public function testNormalizeHandlesNullValuesInRenderOptions(): void
    {
        $input = [
            'render_options' => [
                'ajax_save' => null,
                'security_level' => null,
                'attributes' => null,
            ]
        ];

        $result = $this->normalizer->normalize($input);

        // ✅ Nulls replaced with defaults
        $this->assertFalse($result['render_options']['ajax_save']); // Boolean default
        $this->assertSame('low', $result['render_options']['security_level']); // String default
        $this->assertSame([], $result['render_options']['attributes']); // Array default
    }
}
