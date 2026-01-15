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
 * - Normalize nested structures (form_layout, themes)
 *
 * @group playlist
 * @group config-validation
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
        $input = []; // Empty input

        $result = $this->normalizer->normalize($input);

        // ✅ Should contain all default top-level keys
        $this->assertArrayHasKey('render_options', $result);
        $this->assertArrayHasKey('form_layout', $result);
        $this->assertArrayHasKey('form_hidden_fields', $result);
        $this->assertArrayHasKey('form_extra_fields', $result);

        // ✅ render_options should have default values
        $this->assertIsArray($result['render_options']);
        $this->assertArrayHasKey('security_level', $result['render_options']);
        $this->assertSame('low', $result['render_options']['security_level']);
    }

    /**
     * Test that normalize() casts string booleans to actual booleans.
     */
    public function testNormalizeCastsStringBooleansToActualBooleans(): void
    {
        $input = [
            'render_options' => [
                'ajax_save' => 'true',              // ✅ String 'true' → boolean true
                'auto_save' => '1',                 // ✅ String '1' → boolean true
                'html5_validation' => 'false',      // ✅ String 'false' → boolean false
            ],
        ];

        // ✅ Expect warning for type casting (match actual service message)
        $this->logger->expects($this->atLeastOnce())
            ->method('warning')
            ->with($this->stringContains('was not a boolean, casting to boolean'));

        $result = $this->normalizer->normalize($input);

        // ✅ Should convert to actual booleans
        $this->assertIsBool($result['render_options']['ajax_save']);
        $this->assertTrue($result['render_options']['ajax_save']);

        $this->assertIsBool($result['render_options']['auto_save']);
        $this->assertTrue($result['render_options']['auto_save']);

        $this->assertIsBool($result['render_options']['html5_validation']);
        $this->assertFalse($result['render_options']['html5_validation']);
    }

    /**
     * Test that normalize() ensures arrays remain arrays (not cast to bool).
     */
    public function testNormalizeEnsuresArraysRemainArrays(): void
    {
        $input = [
            'form_hidden_fields' => ['field1', 'field2'],
            'form_extra_fields' => [],
        ];

        $result = $this->normalizer->normalize($input);

        $this->assertIsArray($result['form_hidden_fields']);
        $this->assertCount(2, $result['form_hidden_fields']);
        $this->assertIsArray($result['form_extra_fields']);
        $this->assertEmpty($result['form_extra_fields']);
    }

    /**
     * Test that normalize() handles empty input gracefully.
     */
    public function testNormalizeHandlesEmptyInputGracefully(): void
    {
        $input = [];

        $result = $this->normalizer->normalize($input);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('render_options', $result);
        $this->assertArrayHasKey('form_layout', $result);
    }

    /**
     * Test that normalize() preserves valid existing values.
     */
    public function testNormalizePreservesValidExistingValues(): void
    {
        $input = [
            'render_options' => [
                'security_level' => 'high',
                'layout_type' => 'tabbed',
                'error_display' => 'summary',
            ],
        ];

        $result = $this->normalizer->normalize($input);

        // ✅ Should keep existing valid values
        $this->assertSame('high', $result['render_options']['security_level']);
        $this->assertSame('tabbed', $result['render_options']['layout_type']);
        $this->assertSame('summary', $result['render_options']['error_display']);
    }

    /**
     * Test that normalize() handles form_layout normalization.
     */
    public function testNormalizeHandlesFormLayoutNormalization(): void
    {
        $input = [
            'form_layout' => [
                ['title' => 'Section 1', 'fields' => ['field1', 'field2']],
                ['title' => 'Section 2', 'fields' => ['field3']],
            ],
        ];

        $result = $this->normalizer->normalize($input);

        $this->assertIsArray($result['form_layout']);
        $this->assertCount(2, $result['form_layout']);
        $this->assertArrayHasKey('title', $result['form_layout'][0]);
        $this->assertArrayHasKey('fields', $result['form_layout'][0]);
    }

    /**
     * Test that normalize() handles nested theme configuration.
     */
    public function testNormalizeHandlesNestedThemeConfiguration(): void
    {
        $input = [
            'render_options' => [
                'themes' => [
                    'primary' => 'bootstrap',
                    'fallback' => 'vanilla',
                ],
            ],
        ];

        $result = $this->normalizer->normalize($input);

        $this->assertIsArray($result['render_options']['themes']);
        $this->assertArrayHasKey('primary', $result['render_options']['themes']);
        $this->assertSame('bootstrap', $result['render_options']['themes']['primary']);
    }

    /**
     * Test that normalize() handles integer values in render_options without casting.
     */
    public function testNormalizePreservesIntegerValues(): void
    {
        $input = [
            'render_options' => [
                'max_file_size' => 5242880, // 5MB in bytes
                'timeout' => 30,
            ],
        ];

        $result = $this->normalizer->normalize($input);

        $this->assertIsInt($result['render_options']['max_file_size']);
        $this->assertSame(5242880, $result['render_options']['max_file_size']);
        $this->assertIsInt($result['render_options']['timeout']);
        $this->assertSame(30, $result['render_options']['timeout']);
    }

    /**
     * Test that normalize() handles mixed valid/invalid boolean strings.
     */
    public function testNormalizeHandlesMixedBooleanStrings(): void
    {
        $input = [
            'render_options' => [
                'ajax_save' => true, // ✅ Already boolean
                'force_captcha' => '1', // ❌ String, should cast
                'show_error_container' => false, // ✅ Already boolean
            ],
        ];

        // ✅ Expect warning ONLY for 'force_captcha' (1 time)
        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('force_captcha'));

        $result = $this->normalizer->normalize($input);

        $this->assertIsBool($result['render_options']['ajax_save']);
        $this->assertTrue($result['render_options']['ajax_save']);
        $this->assertIsBool($result['render_options']['force_captcha']);
        $this->assertTrue($result['render_options']['force_captcha']);
        $this->assertIsBool($result['render_options']['show_error_container']);
        $this->assertFalse($result['render_options']['show_error_container']);
    }

    /**
     * Test that normalize() handles deeply nested configuration structures.
     */
    public function testNormalizeHandlesDeeplyNestedConfiguration(): void
    {
        $input = [
            'render_options' => [
                'themes' => [
                    'primary' => 'bootstrap',
                    'variants' => [
                        'dark_mode' => true,
                        'compact' => false,
                    ],
                ],
            ],
        ];

        $result = $this->normalizer->normalize($input);

        $this->assertIsArray($result['render_options']['themes']);
        $this->assertIsArray($result['render_options']['themes']['variants']);
        $this->assertTrue($result['render_options']['themes']['variants']['dark_mode']);
        $this->assertFalse($result['render_options']['themes']['variants']['compact']);
    }

    /**
     * Test that normalize() applies defaults while preserving user-provided overrides.
     */
    public function testNormalizeAppliesDefaultsWhilePreservingOverrides(): void
    {
        $input = [
            'render_options' => [
                'security_level' => 'high', // ✅ User override
                // 'layout_type' missing → should get default
            ],
            'form_layout' => [
                ['title' => 'Custom Section', 'fields' => ['custom_field']],
            ],
        ];

        $result = $this->normalizer->normalize($input);

        // ✅ Should preserve user override
        $this->assertSame('high', $result['render_options']['security_level']);

        // ✅ Should apply default for missing key
        $this->assertArrayHasKey('layout_type', $result['render_options']);
        $this->assertSame('sequential', $result['render_options']['layout_type']);

        // ✅ Should preserve user-provided form_layout
        $this->assertCount(1, $result['form_layout']);
        $this->assertSame('Custom Section', $result['form_layout'][0]['title']);
    }
}
