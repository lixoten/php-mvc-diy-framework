<?php

declare(strict_types=1);

namespace Tests\Core\Formatters;

use Core\Formatters\EmailFormatter;
use Core\Services\IdnConverterService;
use PHPUnit\Framework\TestCase;


/**
 * @group lixoten
 * @group formatters
 * @group email
 */
class EmailFormatterTest extends TestCase
{
    private EmailFormatter $formatter;
    private IdnConverterService $idnConverter;

    protected function setUp(): void
    {
        $this->idnConverter = new \Core\Services\IdnConverterService();
        // $this->formatter = new EmailFormatter();
        $this->formatter = new EmailFormatter($this->idnConverter);
    }

    public function testGetName(): void
    {
        $this->assertSame('email', $this->formatter->getName());
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
            'string value' => ['test@example.com', true],
            'null value' => [null, true],
            'empty string' => ['', true],
            'integer value' => [123, false],
            'float value' => [12.34, false],
            'boolean true' => [true, false],
            'boolean false' => [false, false],
            'array value' => [['test@example.com'], false],
            'object value' => [new \stdClass(), false],
        ];
    }

    /**
     * @dataProvider transformDataProvider
     */
    public function testTransform(
        mixed $value,
        array $options,
        string $expected
    ): void {
        $this->assertSame($expected, $this->formatter->transform($value, $options));
    }

    public static function transformDataProvider(): array
    {
        return [
            // No masking
            'no masking - default options' => [
                'test@example.com', [], 'test@example.com'
            ],
            'no masking - explicit false' => [
                'test@example.com', ['mask' => false], 'test@example.com'
            ],
            'empty string no masking' => [
                '', [], ''
            ],
            'null no masking' => [
                null, [], ''
            ],

            // Masking enabled
            'masking basic email' => [
                //'user@example.com', ['mask' => true], 'u***@e*****.com'
                'user@example.com', ['mask' => true], 'u***@e******.com'
            ],
            'masking subdomain' => [
                // 'user@sub.example.com', ['mask' => true], 'u***@s*********.com'
                // 'user@sub.example.com', ['mask' => true], 'u***@s**********.com'
                'user@sub.example.com', ['mask' => true], 'u***@s**.e******.com'
            ],
            'masking with long local part' => [
                // 'longusername@example.com', ['mask' => true], 'l************@e*****.com'
                // 'longusername@example.com', ['mask' => true], 'l************@e******.com'
                'longusername@example.com', ['mask' => true], 'l***********@e******.com'
            ],
            'masking with long domain part' => [
                // 'user@verylongdomainname.co.uk', ['mask' => true], 'u***@v*******************.uk'
                // 'user@verylongdomainname.co.uk', ['mask' => true], 'u***@v******************.uk'
                'user@verylongdomainname.co.uk', ['mask' => true], 'u***@v*****************.c*.uk'
            ],
            'masking single character local and domain' => [
                'a@b.com', ['mask' => true], 'a@b.com' // Special case: returned unmasked
            ],
            'masking short local and domain' => [
                'ab@cd.efg', ['mask' => true], 'a*@c*.efg'
            ],
            'masking localhost' => [
                'admin@localhost', ['mask' => true], 'a****@l********'
            ],
            'masking without TLD (single part domain)' => [
                'john.doe@intranet', ['mask' => true], 'j*******@i*******'
            ],
            'empty string with masking' => [
                '', ['mask' => true], ''
            ],
            'null with masking' => [
                null, ['mask' => true], ''
            ],
            'email with numeric local part' => [
                '12345@domain.com', ['mask' => true], '1****@d*****.com'
            ],
            'email with special characters in local part' => [
                // 'u.ser+alias@domain.co.uk', ['mask' => true], 'u**********@d********.uk'
                'u.ser+alias@domain.co.uk', ['mask' => true], 'u**********@d*****.c*.uk'
            ],
            'email with umlaut in local part (UTF-8)' => [
                // 'üser@example.com', ['mask' => true], 'ü***@e*****.com'
                'üser@example.com', ['mask' => true], 'ü***@e******.com'
            ],
            'email with umlaut in domain part (UTF-8)' => [
                // 'user@exämple.com', ['mask' => true], 'u***@e******.com'
                'user@exämple.com', ['mask' => true], 'u***@e******.com' // Punycode: xn--exmple-cua.com
            ],
            'email with missing at symbol' => [
                'invalidemail.com', ['mask' => true], 'invalidemail.com' // Should return original if not parsable
            ],
            'email with multiple at symbols (invalid but handled gracefully)' => [
                'user@ex@ample.com', ['mask' => true], 'user@ex@ample.com' // Should return original if not parsable
            ],
            'email with a very short TLD' => [
                'user@domain.x', ['mask' => true], 'u***@d*****.x'
            ],
            'email with multiple dots in local part' => [
                // 'first.last.name@example.com', ['mask' => true], 'f**************@e*****.com'
                'first.last.name@example.com', ['mask' => true], 'f**************@e******.com'
            ],
            'email with single char local and multi-part domain' => [
                // 'a@long.domain.name.com', ['mask' => true], 'a@l**************.com'
                // 'a@long.domain.name.com', ['mask' => true], 'a@l***************.com'
                'a@long.domain.name.com', ['mask' => true], 'a@l***.d*****.n***.com'
            ],
            'email with single char local and single char domain, but long TLD' => [
                'a@b.test', ['mask' => true], 'a@b.test'
            ],
            'email with Chinese characters (Unicode)' => [
                '用户@例子.中国', ['mask' => true], '用*@例*.中国'
            ],
            'email with Long Chinese characters (Unicode)' => [
                '张伟_测试工程师@例子.中国', ['mask' => true], '张*******@例*.中国'
            ],
            'email with Farsi characters (Unicode)' => [
                'کاربر-تست@نمونه.ایران', ['mask' => true], 'ک********@ن****.ایران'
            ],
        ];
    }

    /**
     * Test email denormalization (Punycode to Unicode)
     */
    public function testEmailDenormalization(): void
    {
        // Test the specific case from your example
        $this->assertSame(
            '用户@例子.中国',
            $this->formatter->transform('用户@xn--fsqu00a.xn--fiqs8s')
        );

        // Test that Unicode emails remain unchanged
        $this->assertSame(
            '用户@例子.中国',
            $this->formatter->transform('用户@例子.中国')
        );

        // Test regular ASCII emails
        $this->assertSame(
            'user@example.com',
            $this->formatter->transform('user@example.com')
        );
    }

    /**
     * Test email denormalization with masking
     */
    public function testEmailDenormalizationWithMasking(): void
    {
        $result = $this->formatter->transform('用户@xn--fsqu00a.xn--fiqs8s', ['mask' => true]);

        // Should be denormalized first, then masked
        $this->assertStringContainsString('用*@', $result);
        $this->assertStringContainsString('@', $result);
        $this->assertStringContainsString('.', $result);
    }

    /**
     * Test the IdnConverterService directly
     */
    public function testIdnConverterService(): void
    {
        // Test denormalization
        $this->assertSame(
            '用户@例子.中国',
            $this->idnConverter->denormalizeEmail('用户@xn--fsqu00a.xn--fiqs8s')
        );

        // Test normalization
        $this->assertSame(
            '用户@xn--fsqu00a.xn--fiqs8s',
            $this->idnConverter->normalizeEmail('用户@例子.中国')
        );

        // Test that ASCII emails are unchanged
        $this->assertSame(
            'user@example.com',
            $this->idnConverter->denormalizeEmail('user@example.com')
        );
        $this->assertSame(
            'user@example.com',
            $this->idnConverter->normalizeEmail('user@example.com')
        );
    }


}