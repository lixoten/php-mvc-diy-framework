<?php

declare(strict_types=1);

namespace Tests\Core\Form;

use Core\Form\DataSanitizer;
use Core\Form\Field\FieldInterface;
use PHPUnit\Framework\TestCase;


/**
 * @group lixoten
 * @group sanitizer
 */
class DataSanitizerTest extends TestCase
{
    private DataSanitizer $sanitizer;

    protected function setUp(): void
    {
        $this->sanitizer = new DataSanitizer();
    }

    /**
     * @dataProvider sanitizeProvider
     */
    public function testSanitize(array $input, array $fields, array $expected): void
    {
        $this->assertSame($expected, $this->sanitizer->sanitize($input, $fields));
    }

    public static function sanitizeProvider(): array
    {
        return [
            'trims strings' => [
                ['name' => '  Alice  '],
                ['name' => self::createField('text')],
                ['name' => 'Alice'],
            ],
            'removes control characters' => [
                ['text' => "Hello\x00World"],
                ['text' => self::createField('text')],
                ['text' => 'HelloWorld'],
            ],
            'converts empty string to null' => [
                ['email' => ''],
                ['email' => self::createField('email')],
                ['email' => null],
            ],
            'casts number type' => [
                ['age' => '25'],
                ['age' => self::createField('number')],
                ['age' => 25.0],
            ],
            'casts checkbox to boolean' => [
                ['terms' => 'on'],
                ['terms' => self::createField('checkbox')],
                ['terms' => true],
            ],
            'casts integer type' => [
                ['count' => '42'],
                ['count' => self::createField('integer')],
                ['count' => 42],
            ],
            'casts float type' => [
                ['price' => '12.34'],
                ['price' => self::createField('float')],
                ['price' => 12.34],
            ],
            'null remains null for number' => [
                ['amount' => null],
                ['amount' => self::createField('number')],
                ['amount' => null],
            ],
            'empty string remains null for number' => [
                ['amount' => ''],
                ['amount' => self::createField('number')],
                ['amount' => null],
            ],
            'applies custom sanitize closure' => [
                ['username' => '  Admin  '],
                ['username' => self::createField('text', ['sanitize' => fn($v) => strtolower(trim($v))])],
                ['username' => 'admin'],
            ],
            'sanitize closure receives config and data' => [
                ['foo' => 'Bar', 'baz' => 'Qux'],
                ['foo' => self::createField('text', [
                    'sanitize' => function ($value, $config, $data) {
                        return $value . '-' . ($data['baz'] ?? '');
                    }
                ]),
                'baz' => self::createField('text')],
                ['foo' => 'Bar-Qux', 'baz' => 'Qux'],
            ],
            'unknown type returns as is' => [
                ['custom' => 'value'],
                ['custom' => self::createField('customtype')],
                ['custom' => 'value'],
            ],
        ];
    }

    private static function createField(string $type, array $options = []): FieldInterface
    {
        return new TestField('', $type, $options);
    }
}