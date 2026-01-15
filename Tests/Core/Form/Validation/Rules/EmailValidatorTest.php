<?php

declare(strict_types=1);

namespace Tests\Core\Form\Validation\Rules;

use Core\Form\Validation\Rules\EmailValidator;
use PHPUnit\Framework\TestCase;

/**
 * @group lixoten2
 * @group lixoten
 * @group validators
 * @group email
 */
class EmailValidatorTest extends TestCase
{
    private EmailValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new EmailValidator(
            new \Core\Services\IdnConverterService() 
        );
    }

    /**
     * @dataProvider validEmailProvider
     */
    public function testValidEmails($email, $options = [])
    {
        $this->assertNull($this->validator->validate($email, $options));
        // $result = $this->validator->validate($email, $options);
        // if ($result !== null) {
        //     $this->fail("Validation failed for email: '$email'. Error: '$result'");
        // }
        // $this->assertNull($result);
    }


    public static function validEmailProvider(): array
    {
        return [
            // Basic valid emails
            ['user@example.com'],
            ['john.doe@sub.domain.com'],
            ['user+alias@domain.co.uk'],
            ['user@domain.x'], // Single letter TLD
            ['user@verylongdomainname.co.uk'],
            ['a@b.com'], // This should now pass with correct filter_var usage

            // With min/max length
            // ['short@em.com', ['minlength' => 5, 'maxlength' => 50]],
            'With min/max length' => ['short@em.com', ['minlength' => 5, 'maxlength' => 50]],

            ['longemailaddress@verylongdomainname.co.uk', ['minlength' => 10, 'maxlength' => 100]],

            // With allowed domain
            ['user@allowed.com', ['allowed' => ['allowed.com']]],

            // With forbidden domain (should pass if not forbidden)
            ['user@notforbidden.com', ['forbidden' => ['forbidden.com']]],

            // With pattern (must start with 'user')
            'With pattern (must start with \'user\')' => ['user123@domain.com', ['pattern' => '/^user[a-z0-9._%+-]*@/']],
        ];
    }

    public function testUnicodeEmailSupport()
    {
        $this->assertNull($this->validator->validate('maryeee@ggggg.com'));
        $this->assertNull($this->validator->validate('üser@exämple.com'));
        $this->assertNull($this->validator->validate('用户@例子.中国'));
        $this->assertNull($this->validator->validate('пользователь@пример.рф'));
    }



    // /**
    //  * @dataProvider invalidEmailProvider
    //  */
    // public function testInvalidEmails($email, $options, $expectedMessage)
    // {
    //     $this->assertSame($expectedMessage, $this->validator->validate($email, $options));
    // }

    // public static function invalidEmailProvider(): array
    // {
    //     return [
    //         // Invalid format (Expected: 'validation.invalid' unless custom message provided)
    //         ['not-an-email', [], 'validation.invalid'],
    //         ['user@', [], 'validation.invalid'],
    //         ['@domain.com', [], 'validation.invalid'],
    //         ['user@.com', [], 'validation.invalid'],
    //         // Min length (Expected: 'validation.minlength')
    //         // 'a@b.c' has length 5.
    //         ['a@b.c', ['minlength' => 10], 'validation.minlength'],
    //         // Max length (Expected: 'validation.maxlength')
    //         // 'aaaa...a@example.com' with 60 'a's has length 72.
    //         [str_repeat('a', 60) . '@example.com', ['maxlength' => 20], 'validation.maxlength'],
    //         // Pattern (Expected: 'validation.pattern')
    //         ['admin@domain.com', ['pattern' => '/^user[a-z0-9._%+-]*@/'], 'validation.pattern'],
    //         // Not allowed domain (Expected: 'validation.allowed')
    //         ['user@forbidden.com', ['allowed' => ['allowed.com']], 'validation.allowed'],
    //         // Forbidden domain (Expected: 'validation.forbidden')
    //         ['user@forbidden.com', ['forbidden' => ['forbidden.com']], 'validation.forbidden'],
    //         // Custom error message for basic invalid format
    //         ['bademail', ['invalid_message' => 'Custom error from invalid_message!'], 'Custom error from invalid_message!'],
    //         // Custom general error message for basic invalid format
    //         ['bademail2', ['message' => 'General custom error!'], 'General custom error!'],
    //     ];
    // }
    public static function invalidEmailProvider(): array
    {
        return [
            // Invalid format (Expected: 'validation.invalid' unless custom message provided)
            ['not-an-email', [], 'validation.invalid'],
            ['user@', [], 'validation.invalid'],
            ['@domain.com', [], 'validation.invalid'],
            ['user@.com', [], 'validation.invalid'],

            // Min length (Expected: 'validation.minlength')
            ['a@b.c', ['minlength' => 10], 'validation.minlength'],

            // Max length (Expected: 'validation.maxlength')
            [str_repeat('a', 60) . '@example.com', ['maxlength' => 20], 'validation.maxlength'],

            // Pattern (Expected: 'validation.pattern')
            ['admin@domain.com', ['pattern' => '/^user[a-z0-9._%+-]*@/'], 'validation.pattern'],

            // Not allowed domain (Expected: 'validation.allowed')
            ['user@forbidden.com', ['allowed' => ['allowed.com']], 'validation.allowed'],

            // Forbidden domain (Expected: 'validation.forbidden')
            ['user@forbidden.com', ['forbidden' => ['forbidden.com']], 'validation.forbidden'],

            // Custom error message for basic invalid format
            ['bademail', ['invalid_message' => 'Custom error from invalid_message!'], 'Custom error from invalid_message!'],

            // Custom general error message for basic invalid format
            ['bademail2', ['message' => 'General custom error!'], 'General custom error!'],
        ];
    }

    public function testNullAndEmptyAreSkipped()
    {
        $this->assertNull($this->validator->validate(null));
        $this->assertNull($this->validator->validate(''));
    }

    public function testGetName()
    {
        $this->assertSame('email', $this->validator->getName());
    }
}