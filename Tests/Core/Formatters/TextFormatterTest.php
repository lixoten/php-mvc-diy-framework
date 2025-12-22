<?php

declare(strict_types=1);

use Core\Formatters\TextFormatter;
use Core\I18n\I18nTranslator;
use PHPUnit\Framework\TestCase;


/**
 * Important!!! How to run group: vendor/bin/phpunit --testdox --group lixoten
 * @group lixoten
 * @group formatter
 * @group text
 */
class TextFormatterTest extends TestCase
{
    private TextFormatter $formatter;
    private I18nTranslator $mockTranslator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockTranslator = $this->createMock(I18nTranslator::class);
        $this->mockTranslator->method('get')->willReturnCallback(fn($key, $options = []) => $key);

        // ✅ Ensure TextFormatter constructor gets the mock translator
        $this->formatter = new TextFormatter($this->mockTranslator);
    }

    /**
     * @dataProvider basicTransformDataProvider
     */
    public function testTransformReturnsExpectedValue(mixed $value, array $options, string $expected): void
    {
        $this->assertEquals($expected, $this->formatter->transform($value, $options));
    }

    public function basicTransformDataProvider(): array
    {
        return [
            'basic string' => ['Hello, World!', [], 'Hello, World!'],
            'empty string' => ['', [], ''],
            'special characters' => ['!@#$%^&*()', [], '!@#$%^&*()'],
            'numeric value' => [12345, [], '12345'],
            'null with default' => [null, [], ''], // Assuming default null_value is empty string
            'null with custom value' => [null, ['null_value' => 'N/A'], 'N/A'],
            'zero' => [0, [], '0'],
            'float' => [123.45, [], '123.45'],
        ];
    }

    /**
     * @dataProvider truncationDataProvider
     */
    public function testTransformHandlesTruncation(mixed $value, array $options, string $expected): void
    {
        $this->assertEquals($expected, $this->formatter->transform($value, $options));
    }

    public function truncationDataProvider(): array
    {
        return [
            // ✅ Expected: 'This is a very lo...' (17 chars + 3 suffix = 20 total)
            'truncate with default suffix' => ['This is a very long text that needs to be truncated.', ['max_length' => 20], 'This is a very lo...'],
            // ✅ Expected: '...read mo' (suffix itself truncated, as max_length < suffix_length)
            'truncate with custom suffix' => ['Another long sentence.', ['max_length' => 10, 'truncate_suffix' => '...read more'], '...read mo'],
            'no truncation needed' => ['Short text', ['max_length' => 20], 'Short text'],
            'exact length no truncation' => ['Exact', ['max_length' => 5], 'Exact'],
            'exact length with suffix' => ['Exactly', ['max_length' => 7], 'Exactly'], // No truncation if max_length is exactly the string length
            'string shorter than max_length' => ['Hi', ['max_length' => 5], 'Hi'],
            // ✅ Expected: '12...' (2 chars + 3 suffix = 5 total)
            'truncation of numeric' => [1234567, ['max_length' => 5], '12...'],
        ];
    }

    /**
     * @dataProvider transformationDataProvider
     */
    public function testTransformAppliesTransformations(string $value, array $options, string $expected): void
    {
        $this->assertEquals($expected, $this->formatter->transform($value, $options));
    }

    public function transformationDataProvider(): array
    {
        return [
            'uppercase' => ['hello world', ['transform' => 'uppercase'], 'HELLO WORLD'],
            'lowercase' => ['HELLO WORLD', ['transform' => 'lowercase'], 'hello world'],
            'capitalize' => ['hello world', ['transform' => 'capitalize'], 'Hello world'],
            'capitalize mixed case' => ['ANOTHER TEST', ['transform' => 'capitalize'], 'Another test'],
            'title case' => ['this is a title', ['transform' => 'title'], 'This Is A Title'],
            'title case with multiple words' => ['hello world from php', ['transform' => 'title'], 'Hello World From Php'],
            'trim' => ['  hello world  ', ['transform' => 'trim'], 'hello world'],
            'trim no whitespace' => ['test', ['transform' => 'trim'], 'test'],
            'last2char_upper' => ['Hello World', ['transform' => 'last2char_upper'], 'Hello WorLD'],
            'last2char_upper single char' => ['a', ['transform' => 'last2char_upper'], 'a'],
            'last2char_upper two chars' => ['ab', ['transform' => 'last2char_upper'], 'AB'],
            'last2char_upper empty' => ['', ['transform' => 'last2char_upper'], ''],
        ];
    }

    /**
     * @dataProvider suffixDataProvider
     */
    public function testTransformAppendsSuffix(string $value, array $options, string $expected): void
    {
        $this->assertEquals($expected, $this->formatter->transform($value, $options));
    }

    public function suffixDataProvider(): array
    {
        return [
            'simple suffix' => ['Price', ['suffix' => ': $100'], 'Price: $100'],
            'empty value with suffix' => ['', ['suffix' => ' Default'], ' Default'],
        ];
    }

    /**
     * @dataProvider combinedOptionsDataProvider
     */
    public function testTransformWithCombinedOptions(mixed $value, array $options, string $expected): void
    {
        $this->assertEquals($expected, $this->formatter->transform($value, $options));
    }

    public function combinedOptionsDataProvider(): array
    {
        return [
            'truncation, uppercase, suffix' => [
                'super important secret message',
                [
                    'max_length' => 15, // Total length including '...' (3 chars)
                    'truncate_suffix' => '...',
                    'transform' => 'uppercase',
                    'suffix' => ' (CONFIDENTIAL)'
                ],
                // ✅ Expected: 'SUPER IMPORT...' (12 chars + 3 suffix = 15 total) + ' (CONFIDENTIAL)'
                'SUPER IMPORT... (CONFIDENTIAL)'
            ],
            'null value, suffix' => [
                null,
                [
                    'null_value' => 'Not available',
                    'suffix' => ' (Check later)'
                ],
                'Not available (Check later)'
            ],
            'lowercase, truncation, suffix' => [
                'LONG TEXT EXAMPLE',
                [
                    'max_length' => 10, // Total length including '..' (2 chars)
                    'truncate_suffix' => '..',
                    'transform' => 'lowercase',
                    'suffix' => ' END'
                ],
                // ✅ Expected: 'long tex..' (8 chars + 2 suffix = 10 total) + ' END'
                'long tex.. END'
            ],
            'numeric, truncation, suffix' => [
                1234567890,
                [
                    'max_length' => 5, // Total length including '...' (3 chars)
                    'truncate_suffix' => '...',
                    'suffix' => ' (ID)'
                ],
                // ✅ Expected: '12...' (2 chars + 3 suffix = 5 total) + ' (ID)'
                '12... (ID)'
            ],
        ];
    }
}