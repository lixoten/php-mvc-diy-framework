<?php

declare(strict_types=1);

namespace Tests\Core\Form\Validation\Rules;

use Core\Form\Validation\Rules\CheckboxValidator;
use PHPUnit\Framework\TestCase;

/**
 * @group lixoten
 * @group validators
 * @group checkbox
 */
class CheckboxValidatorTest extends TestCase
{
    private CheckboxValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new CheckboxValidator();
    }

    public function testGetNameReturnsCheckbox(): void
    {
        $this->assertSame('checkbox', $this->validator->getName());
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function testValidate(mixed $value, array $options, ?string $expectedError): void
    {
        $this->assertSame($expectedError, $this->validator->validate($value, $options));
    }

    public static function validationDataProvider(): array
    {
        return [
            // Null and empty values should be skipped by AbstractValidator's shouldSkipValidation
            'null value' => [null, [], null],
            'empty string value' => ['', [], null],

            // Any non-empty value should currently pass, as no specific validation logic is implemented
            'true boolean' => [true, [], null],
            'false boolean' => [false, [], null],
            'string "on"' => ['on', [], null],
            'string "off"' => ['off', [], null],
            'string "1"' => ['1', [], null],
            'string "0"' => ['0', [], null],
            'integer 1' => [1, [], null],
            'integer 0' => [0, [], null],
            'arbitrary string' => ['some_value', [], null],
            'array' => [['a', 'b'], [], null], // Checkbox validator typically expects single value, but current logic passes
        ];
    }
}
