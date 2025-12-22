<?php

declare(strict_types=1);

namespace Tests\Core\Form\Validation\Rules;

use Core\Form\Validation\Rules\TextValidator;
use PHPUnit\Framework\TestCase;

/**
 * @group lixoten
 * @group validators
 * @group text
 */
class TextValidatorTest extends TestCase
{
    private TextValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new TextValidator();
    }

    public function testGetNameReturnsText(): void
    {
        $this->assertSame('text', $this->validator->getName());
    }

    /**
     * @dataProvider validTextProvider
     */
    public function testValidTexts($value, $options = [])
    {
        $this->assertNull($this->validator->validate($value, $options));
    }

    public static function validTextProvider(): array
    {
        return [
            'Simple valid string' => ['Hello world', [], null],
            'String with minlength fulfilled' => ['abc', ['minlength' => 2], null],
            'String with maxlength fulfilled' => ['abcdef', ['maxlength' => 10], null],
            'String matching pattern' => ['abc123', ['pattern' => '/^[a-z0-9]+$/i'], null],
            'String in allowed list' => ['allowed', ['allowed' => ['allowed', 'ok']], null],
            'String not in forbidden list' => ['notforbidden', ['forbidden' => ['bad', 'evil']], null],
            'String with special characters' => ['This is a test with spaces and !@#$%^&*()', [], null],
            'Numeric string value' => ['12345', [], null], // Text validator should treat numeric string as valid text
        ];
    }

    /**
     * @dataProvider invalidTextProvider
     */
    public function testInvalidTexts($value, $options, $expectedMessage)
    {
        $this->assertSame($expectedMessage, $this->validator->validate($value, $options));
    }

    public static function invalidTextProvider(): array
    {
        return [
            // Type
            'Invalid type: integer' => [123, [], 'validation.invalid'],
            // Min length
            'Min length violation' => ['a', ['minlength' => 2], 'validation.minlength'],
            // Max length
            'Max length violation' => ['abcdef', ['maxlength' => 3], 'validation.maxlength'],
            // Pattern
            'Pattern mismatch' => ['abc!', ['pattern' => '/^[a-z]+$/'], 'validation.pattern'],
            // Allowed (Value not in the allowed list)
            'Value not in allowed list' => ['not_allowed_value', ['allowed' => ['yes', 'ok']], 'validation.allowed'],
            // Forbidden
            'Value in forbidden list' => ['bad', ['forbidden' => ['bad', 'evil']], 'validation.forbidden'],
        ];
    }

    public function testNullAndEmptyAreSkipped(): void
    {
        $this->assertNull($this->validator->validate(null));
        $this->assertNull($this->validator->validate(''));
    }
}
