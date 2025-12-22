<?php

declare(strict_types=1);

namespace Tests\Core\Formatters;

use Core\Formatters\BooleanFormatter;
use Core\I18n\I18nTranslator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;


/**
 * @group lixoten
 * @group formatters
 * @group boolean
 * @group checkbox
 */
class BooleanFormatterTest extends TestCase
{
    private BooleanFormatter $formatter;
    private MockObject|I18nTranslator $translatorMock;

    protected function setUp(): void
    {
        $this->translatorMock = $this->createMock(I18nTranslator::class);
        $this->formatter = new BooleanFormatter($this->translatorMock);
    }

    public function testGetNameReturnsBoolean(): void
    {
        $this->assertSame('boolean', $this->formatter->getName());
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, $this->formatter->supports($value));
    }

    public static function supportsDataProvider(): array
    {
        return [
            'boolean true' => [true, true],
            'boolean false' => [false, true],
            'string "1"' => ['1', true],
            'string "0"' => ['0', true],
            'integer 1' => [1, true],
            'integer 0' => [0, true],
            'string "true"' => ['true', false],
            'string "false"' => ['false', false],
            'null' => [null, false],
            'array' => [['value' => true], false],
            'object' => [new \stdClass(), false],
        ];
    }

    /**
     * @dataProvider transformWithLabelDataProvider
     */
    public function testTransformWithLabel(mixed $value, array $options, string $expectedLabel, string $expectedResult): void
    {
        $this->translatorMock->method('get')
            ->with($expectedLabel, []) // ✅ Expects empty array for $replacements
            ->willReturn($expectedResult);

        $this->assertSame($expectedResult, $this->formatter->transform($value, $options));
    }

    public static function transformWithLabelDataProvider(): array
    {
        return [
            'true with label' => [
                true,
                ['label' => 'translation.key.true'],
                'translation.key.true',
                'Translated True Label'
            ],
            'false with label' => [
                false,
                ['label' => 'translation.key.false'],
                'translation.key.false',
                'Translated False Label'
            ],
            'string "1" with label' => [
                '1',
                ['label' => 'translation.key.one'],
                'translation.key.one',
                'Translated One Label'
            ],
            'string "0" with label' => [
                '0',
                ['label' => 'translation.key.zero'],
                'translation.key.zero',
                'Translated Zero Label'
            ]
        ];
    }

    /**
     * @dataProvider transformWithoutLabelDataProvider
     */
    public function testTransformWithoutLabel(mixed $value, array $options, string $expectedResult): void
    {
        $this->assertSame($expectedResult, $this->formatter->transform($value, $options));
    }

    public static function transformWithoutLabelDataProvider(): array
    {
        return [
            'true' => [true, [], '1'],
            'false' => [false, [], '0'],
            'string "1"' => ['1', [], '1'],
            'string "0"' => ['0', [], '0'],
            'null' => [null, [], ''],
            'array' => [['value' => true], [], ''],
        ];
    }

    public function testHtmlspecialcharsApplied(): void
    {
        $this->translatorMock->method('get')
            ->with('translation.key.special', [], null, true) // Changed this line
            ->willReturn('&lt;Special&gt;');

        $result = $this->formatter->transform('<Special>', ['label' => 'translation.key.special']);

        $this->assertStringContainsString('&lt;', $result);
        $this->assertStringContainsString('&gt;', $result);
    }

    public function testPageNameContext(): void
    {
        $this->translatorMock->method('get')
            ->willReturnMap([
                // [key, replacements, pageName, htmlSafe, returnValue]
                ['translation.key.home', [], 'home', true, 'Home Page'],
                ['translation.key.home', [], 'admin', true, 'Admin Home'],
            ]);

        $this->assertSame('Home Page', $this->formatter->transform(true, ['label' => 'translation.key.home', 'page_name' => 'home']));
        $this->assertSame('Admin Home', $this->formatter->transform(true, ['label' => 'translation.key.home', 'page_name' => 'admin']));
    }
}