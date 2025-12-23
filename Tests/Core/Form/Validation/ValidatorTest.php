<?php

declare(strict_types=1);

namespace Tests\Core\Form\Validation;

use Core\Form\Field\FieldInterface;
use Core\Form\Schema\FieldSchema;
use Core\Form\Validation\Validator;
use Core\Form\Validation\ValidatorRegistry;
use Core\I18n\I18nTranslator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;

/**
 *
 * @group core
 * @group validators
 */
class ValidatorTest extends TestCase
{
    private Validator $validator;
    private ValidatorRegistry $mockRegistry;
    private FieldSchema $mockSchema;
    private LoggerInterface $mockLogger;

    protected function setUp(): void
    {
        // ✅ Assign mock objects to CLASS PROPERTIES using $this->
        $this->mockRegistry = $this->createMock(ValidatorRegistry::class);
        $this->mockSchema   = $this->createMock(FieldSchema::class);
        $this->mockLogger   = $this->createMock(LoggerInterface::class);

        // Instantiate Validator with these class properties
        $this->validator = new Validator(
            $this->mockRegistry,
            $this->mockSchema,
            $this->mockLogger
        );
    }

   // ✅ Renamed for clarity and removed direct property access from setUp
    public function testDependenciesInjected(): void
    {
        // Using Reflection to verify assignment of protected/private properties
        // This is done to avoid adding public getters to the production class
        // and is generally accepted for foundational DI tests if no public API
        // can observe the assignment.
        $reflector = new ReflectionClass(Validator::class);

        $registryProperty = $reflector->getProperty('registry');
        $registryProperty->setAccessible(true);
        $this->assertSame($this->mockRegistry, $registryProperty->getValue($this->validator));

        $fieldSchemaProperty = $reflector->getProperty('fieldSchema');
        $fieldSchemaProperty->setAccessible(true);
        $this->assertSame($this->mockSchema, $fieldSchemaProperty->getValue($this->validator));

        $loggerProperty = $reflector->getProperty('logger');
        $loggerProperty->setAccessible(true);
        $this->assertSame($this->mockLogger, $loggerProperty->getValue($this->validator));
    }

    public function testConstructorFailsWithInvalidDependencies(): void
    {
        // This should throw an error if the Validator expects specific types
        $this->expectException(\TypeError::class);
        $validator = new Validator(
            'invalid-registry', // Not a ValidatorRegistry
            $this->createMock(FieldSchema::class), // Pass valid mocks for other params to pinpoint the error
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testConstructorFailsWithInvalidSchemaDependency(): void
    {
        $this->expectException(\TypeError::class);
        $validator = new Validator(
            $this->createMock(ValidatorRegistry::class),
            'invalid-schema',   // Not a FieldSchema
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testConstructorFailsWithInvalidLoggerDependency(): void
    {
        $this->expectException(\TypeError::class);
        $validator = new Validator(
            $this->createMock(ValidatorRegistry::class),
            $this->createMock(FieldSchema::class),
            'invalid-logger'    // Not a LoggerInterface
        );
    }

    // --- PHASE 2: Core Validation Logic Tests ---

    /**
     * @group core_validation
     * @group validate_data
     * @dataProvider validateDataRequiredFieldProvider
     */
    public function testValidateDataRequiredField(array $data, array $rules, array $expectedErrors): void
    {
        // Mock the 'required' validator
        $mockRequiredValidator = $this->createMock(\Core\Form\Validation\ValidatorInterface::class);
        // It will return an error message if the value is empty, null otherwise
        $mockRequiredValidator->method('validate')
                              ->willReturnCallback(function ($value) {
                                  return (empty($value) && $value !== '0') ? 'validation.required' : null; // Simple required logic for mock
                              });

        // Configure the registry to return our mock 'required' validator
        $this->mockRegistry->method('get')
                           ->with('required')
                           ->willReturn($mockRequiredValidator);

        $errors = $this->validator->validateData($data, $rules);

        $this->assertEquals($expectedErrors, $errors);
    }

    public static function validateDataRequiredFieldProvider(): array
    {
        return [
            'Required field missing' => [
                'data' => ['name' => ''],
                'rules' => ['name' => ['required' => true]],
                'expectedErrors' => ['name' => ['validation.required']],
            ],
            'Required field present' => [
                'data' => ['name' => 'John Doe'],
                'rules' => ['name' => ['required' => true]],
                'expectedErrors' => [],
            ],
            'Required field is 0' => [ // 0 is a valid value for required
                'data' => ['age' => '0'],
                'rules' => ['age' => ['required' => true]],
                'expectedErrors' => [],
            ],
            'Multiple required fields, some missing' => [
                'data' => ['name' => 'Jane', 'email' => ''],
                'rules' => ['name' => ['required' => true], 'email' => ['required' => true]],
                'expectedErrors' => ['email' => ['validation.required']],
            ],
        ];
    }

    /**
     * @group core_validation
     * @group validate_data
     * @dataProvider validateDataLengthAndPatternProvider
     */
    public function testValidateDataWithLengthAndPatternRules(array $data, array $rules, array $expectedErrors): void
    {
        // Mock a 'text' validator (handles minlength, maxlength, pattern conceptually)
        $mockTextValidator = $this->createMock(\Core\Form\Validation\ValidatorInterface::class);
        $mockTextValidator->method('validate')
                          ->willReturnCallback(function ($value, $options) {
                              if (isset($options['minlength']) && strlen($value) < $options['minlength']) {
                                  return 'validation.minlength';
                              }
                              if (isset($options['maxlength']) && strlen($value) > $options['maxlength']) {
                                  return 'validation.maxlength';
                              }
                              if (isset($options['pattern']) && !preg_match($options['pattern'], $value)) {
                                  return 'validation.pattern';
                              }
                              return null;
                          });

        $this->mockRegistry->method('get')
                           ->with('text')
                           ->willReturn($mockTextValidator);

        $errors = $this->validator->validateData($data, $rules);
        $this->assertEquals($expectedErrors, $errors);
    }

    public static function validateDataLengthAndPatternProvider(): array
    {
        return [
            'Valid text' => [
                'data' => ['title' => 'Valid Title'],
                'rules' => ['title' => ['text' => ['minlength' => 5, 'maxlength' => 20]]],
                'expectedErrors' => [],
            ],
            'Text too short' => [
                'data' => ['title' => 'abc'],
                'rules' => ['title' => ['text' => ['minlength' => 5]]],
                'expectedErrors' => ['title' => ['validation.minlength']],
            ],
            'Text too long' => [
                'data' => ['title' => str_repeat('a', 25)],
                'rules' => ['title' => ['text' => ['maxlength' => 20]]],
                'expectedErrors' => ['title' => ['validation.maxlength']],
            ],
            'Pattern mismatch' => [
                'data' => ['code' => 'abc!'],
                'rules' => ['code' => ['text' => ['pattern' => '/^[a-z]+$/']]],
                'expectedErrors' => ['code' => ['validation.pattern']],
            ],
            'Multiple rules failing' => [
                'data' => ['username' => 'a'],
                'rules' => ['username' => ['text' => ['minlength' => 5, 'pattern' => '/^[a-z]+$/']]],
                'expectedErrors' => ['username' => ['validation.minlength']], // Assumes minlength is checked first
            ],
        ];
    }

    /**
     * @group core_validation
     * @group validate_field
     * @dataProvider validateFieldProvider
     */
    public function testValidateField(
        string $fieldName,
        mixed $fieldValue,
        array $fieldValidators,
        array $expectedErrors
    ): void {
        // Create a mock FieldInterface
        $mockField = $this->createMock(FieldInterface::class);
        $mockField->method('getName')->willReturn($fieldName);
        $mockField->method('getValue')->willReturn($fieldValue);
        $mockField->method('getValidators')->willReturn($fieldValidators);

        // Mock a generic validator to handle all rule types for this test
        $mockGenericValidator = $this->createMock(\Core\Form\Validation\ValidatorInterface::class);
        $mockGenericValidator->method('validate')
                             ->willReturnCallback(function ($value, $options) {
                                 // Simple logic for required rule (from TextValidator, etc.)
                                 if (isset($options['required']) && empty($value) && $value !== '0') {
                                     return 'validation.required';
                                 }
                                 // Simple logic for minlength (from TextValidator, etc.)
                                 if (isset($options['minlength']) && is_string($value) && strlen($value) < $options['minlength']) {
                                     return 'validation.minlength';
                                 }
                                 // Simple logic for pattern (from TextValidator, etc.)
                                 if (isset($options['pattern']) && is_string($value) && !preg_match($options['pattern'], $value)) {
                                     return 'validation.pattern';
                                 }
                                 // Simple logic for forbidden (from TextValidator, etc.)
                                 if (isset($options['forbidden']) && in_array($value, $options['forbidden'], true)) {
                                     return 'validation.forbidden';
                                 }
                                 return null; // No error
                             });

        // Configure the registry to return our mock for any requested validator name
        // (assuming validator names are the rule keys like 'required', 'text', etc.)
        $this->mockRegistry->method('get')
                           ->willReturn($mockGenericValidator);

        $errors = $this->validator->validateField($mockField);
        $this->assertEquals($expectedErrors, $errors);
    }

    public static function validateFieldProvider(): array
    {
        return [
            'Field with no rules (skipped if empty)' => [
                'name' => 'optional_field',
                'value' => '',
                'validators' => [],
                'expectedErrors' => [],
            ],
            'Required field missing' => [
                'name' => 'email',
                'value' => '',
                'validators' => ['text' => ['required' => true, 'minlength' => 1]], // The generic mock validator will check 'required'
                'expectedErrors' => ['email' => ['validation.required']],
            ],
            'Field too short' => [
                'name' => 'username',
                'value' => 'us',
                'validators' => ['text' => ['minlength' => 5]], // The generic mock validator will check 'minlength'
                'expectedErrors' => ['username' => ['validation.minlength']],
            ],
            'Field with invalid pattern' => [
                'name' => 'password',
                'value' => 'weakpass',
                'validators' => ['text' => ['pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/']], // Requires uppercase, lowercase, digit, min 8
                'expectedErrors' => ['password' => ['validation.pattern']],
            ],
            'Field with forbidden value' => [
                'name' => 'tag',
                'value' => 'admin',
                'validators' => ['text' => ['forbidden' => ['admin', 'root']]],
                'expectedErrors' => ['tag' => ['validation.forbidden']],
            ],
            'Field is valid with multiple rules' => [
                'name' => 'valid_name',
                'value' => 'TestName1',
                'validators' => ['text' => ['minlength' => 5, 'maxlength' => 20, 'pattern' => '/^[a-zA-Z0-9]+$/']],
                'expectedErrors' => [],
            ],
            'Field is valid when 0 is required' => [
                'name' => 'count',
                'value' => '0',
                'validators' => ['text' => ['required' => true]],
                'expectedErrors' => [],
            ],
            'Field with mixed rules and some passing, some failing' => [
                'name' => 'bio',
                'value' => 'short', // Fails minlength 10, but passes pattern
                'validators' => ['text' => ['minlength' => 10, 'pattern' => '/^[a-z]+$/i']],
                'expectedErrors' => ['bio' => ['validation.minlength']],
            ],
        ];
    }

}