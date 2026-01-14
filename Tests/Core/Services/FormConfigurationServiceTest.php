<?php

declare(strict_types=1); // ✅ PHP 8.2 compatibility, PSR-2 and strict typing

namespace Tests\Core\Services;

use Core\Exceptions\ConfigurationValidationException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Core\Interfaces\ConfigInterface;
use Psr\Log\LoggerInterface;
use Core\Services\FormConfigurationNormalizerService;
use Core\Services\FormConfigurationValidatorService;
use Core\Services\FormConfigurationService;

/**
 * @group lixoten
 * @group services
 * @group form-config
 */
class FormConfigurationServiceTest extends TestCase
{
    private FormConfigurationService $service;
    private MockObject|ConfigInterface $configService;
    private MockObject|LoggerInterface $logger;
    private MockObject|FormConfigurationNormalizerService $normalizerService;
    private MockObject|FormConfigurationValidatorService $validatorService;

    protected function setUp(): void
    {
        parent::setUp();

        // ✅ These lines create the "mocks" (or test stubs) for your dependencies.
        $this->configService = $this->createMock(ConfigInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->normalizerService = $this->createMock(FormConfigurationNormalizerService::class);
        $this->validatorService = $this->createMock(FormConfigurationValidatorService::class);

        // ✅ The FormConfigurationService is instantiated with these mock objects.
        $this->service = new FormConfigurationService(
            $this->configService,
            $this->logger,
            $this->normalizerService,
            $this->validatorService
        );
    }

    public function testLoadConfigurationReturnsNormalizedConfig(): void
    {
        $pageKey = 'testy_edit';
        $pageName = 'testy';
        $pageAction = 'edit';
        $pageFeature = 'Testy';
        $pageEntity = 'testy';

        $baseConfig = [
            'render_options' => ['default_title' => 'Base Form'],
            'form_layout' => ['base_layout' => 'default'],
        ];
        $pageConfig = [
            'render_options' => ['title' => 'Edit Testy'],
            'form_layout' => ['page_layout' => 'edit_form'],
            'form_hidden_fields' => ['id'],
            'form_extra_fields' => ['captcha'],
        ];

        // Expected merged config before normalization (page > base)
        $expectedMergedConfig = [
            'render_options' => [
                'default_title' => 'Base Form', // From base, not overridden
                'title' => 'Edit Testy' // From page, overrides base if existed
            ],
            'form_layout' => ['page_layout' => 'edit_form'],
            'form_hidden_fields' => ['id'],
            'form_extra_fields' => ['captcha'],
        ];

        $normalizedConfig = ['normalized' => true, 'some_field' => 'value'];

        // Configure mocks
        $this->configService->expects($this->once())
            ->method('get')
            ->with('view.form')
            ->willReturn($baseConfig);

        $this->configService->expects($this->once())
            ->method('getFromFeature')
            ->with($pageFeature, "{$pageName}_view_{$pageAction}")
            ->willReturn($pageConfig);

        $this->normalizerService->expects($this->once())
            ->method('normalize')
            ->with($expectedMergedConfig) // Expect merged config to be passed
            ->willReturn($normalizedConfig);

        $configIdentifier = "{$pageFeature}/Config/{$pageKey}_view.php";
        $this->validatorService->expects($this->once())
            ->method('validate')
            ->with($normalizedConfig, $pageKey, $pageEntity, $configIdentifier)
            ->willReturn(['isValid' => true, 'errors' => []]);

        // Act
        $result = $this->service->loadConfiguration(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity
        );

        // Assert
        $this->assertSame($normalizedConfig, $result);
    }

    public function testLoadConfigurationWithOnlyBaseConfig(): void
    {
        $pageKey = 'testy_add';
        $pageName = 'testy';
        $pageAction = 'add';
        $pageFeature = 'Testy';
        $pageEntity = 'testy';

        $baseConfig = ['render_options' => ['default_title' => 'Base Form Title'], 'form_layout' => ['cols' => 1]];
        $pageConfig = []; // No page-specific config

        $expectedMergedConfig = [
            'render_options' => ['default_title' => 'Base Form Title'],
            'form_layout' => ['cols' => 1], // From base, not overridden
            'form_hidden_fields' => [],
            'form_extra_fields' => []
        ];
        $normalizedConfig = ['normalized_base_only' => true];

        $this->configService->expects($this->once())
            ->method('get')
            ->with('view.form')
            ->willReturn($baseConfig);

        $this->configService->expects($this->once())
            ->method('getFromFeature')
            ->with($pageFeature, "{$pageName}_view_{$pageAction}")
            ->willReturn($pageConfig);

        $this->normalizerService->expects($this->once())
            ->method('normalize')
            ->with($expectedMergedConfig)
            ->willReturn($normalizedConfig);

        $this->validatorService->expects($this->once())
            ->method('validate')
            ->with($normalizedConfig, $pageKey, $pageEntity, "{$pageFeature}/Config/{$pageKey}_view.php");

        $result = $this->service->loadConfiguration(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity
        );

        $this->assertSame($normalizedConfig, $result);
    }

    public function testLoadConfigurationWithOnlyPageConfig(): void
    {
        $pageKey = 'user_login';
        $pageName = 'user';
        $pageAction = 'login';
        $pageFeature = 'Auth';
        $pageEntity = 'user';

        $baseConfig = ['render_options' => ['default_footer' => 'Copyright']]; // Base config exists but some sections are empty
        $pageConfig = [
            'render_options' => ['login_title' => 'User Login'],
            'form_layout' => ['layout' => 'vertical'],
            'form_extra_fields' => ['remember_me']
        ];

        $expectedMergedConfig = [
            'render_options' => [
                'default_footer' => 'Copyright', // From base, not overridden by page
                'login_title' => 'User Login' // From page
            ],
            'form_layout' => ['layout' => 'vertical'],
            'form_hidden_fields' => [],
            'form_extra_fields' => ['remember_me']
        ];
        $normalizedConfig = ['normalized_page_only' => true];

        $this->configService->expects($this->once())
            ->method('get')
            ->with('view.form')
            ->willReturn($baseConfig);

        $this->configService->expects($this->once())
            ->method('getFromFeature')
            ->with($pageFeature, "{$pageName}_view_{$pageAction}")
            ->willReturn($pageConfig);

        $this->normalizerService->expects($this->once())
            ->method('normalize')
            ->with($expectedMergedConfig)
            ->willReturn($normalizedConfig);

        $this->validatorService->expects($this->once())
            ->method('validate')
            ->with($normalizedConfig, $pageKey, $pageEntity, "{$pageFeature}/Config/{$pageKey}_view.php");

        $result = $this->service->loadConfiguration(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity
        );

        $this->assertSame($normalizedConfig, $result);
    }


    /**
     * Test that loadConfiguration throws ConfigurationValidationException
     * when validator returns validation errors.
     */
    public function testLoadConfigurationThrowsExceptionOnValidationFailure(): void
    {
        $pageKey = 'testy_edit';
        $pageName = 'testy';
        $pageAction = 'edit';
        $pageFeature = 'Testy';
        $pageEntity = 'testy';

        $baseConfig = ['render_options' => ['default_title' => 'Base']];
        $pageConfig = ['render_options' => ['title' => 'Edit Testy']];
        $normalizedConfig = ['normalized' => true];

        // ✅ Validator returns validation errors
        $validationErrors = [
            "Render option 'security_level' must be one of ['low', 'medium', 'high']. Found: 'invalid'",
            "Render option 'layout_type' must be one of ['sequential', 'fieldsets', 'sections']. Found: 'badlayout'"
        ];

        $this->configService->expects($this->once())
            ->method('get')
            ->with('view.form')
            ->willReturn($baseConfig);

        $this->configService->expects($this->once())
            ->method('getFromFeature')
            ->with($pageFeature, "{$pageName}_view_{$pageAction}")
            ->willReturn($pageConfig);

        $this->normalizerService->expects($this->once())
            ->method('normalize')
            ->willReturn($normalizedConfig);

        $this->validatorService->expects($this->once())
            ->method('validate')
            ->with($normalizedConfig, $pageKey, $pageEntity, "{$pageFeature}/Config/{$pageKey}_view.php")
            ->willReturn(['isValid' => false, 'errors' => $validationErrors]); // ✅ Validation fails

        // ✅ Expect the orchestrator to throw ConfigurationValidationException
        $this->expectException(ConfigurationValidationException::class);
        $this->expectExceptionMessage("Form 8 Configuration Validation Failed"); // Partial match is fine

        // Act
        $this->service->loadConfiguration(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity
        );
    }

    /**
     * Test that deepMerge handles scalar-to-array overrides correctly.
     */
    public function testLoadConfigurationHandlesScalarToArrayOverrides(): void
    {
        $pageKey = 'type_override_test';
        $pageName = 'override';
        $pageAction = 'test';
        $pageFeature = 'OverrideFeature';
        $pageEntity = 'override_entity';

        // ⚠️ Base has 'buttons' as a string, page has 'buttons' as an array
        $baseConfig = [
            'render_options' => [
                'buttons' => 'simple_string', // Scalar value
            ],
        ];

        $pageConfig = [
            'render_options' => [
                'buttons' => ['save' => 'Save Button'], // Array value (overrides)
            ],
        ];

        // ✅ Expected: Page's array OVERWRITES base's scalar (no deep merge possible)
        $expectedMergedConfig = [
            'render_options' => [
                'buttons' => ['save' => 'Save Button'], // Page wins
            ],
            'form_layout' => [],
            'form_hidden_fields' => [],
            'form_extra_fields' => [],
        ];

        $normalizedConfig = ['normalized_type_override' => true];

        $this->configService->expects($this->once())
            ->method('get')
            ->with('view.form')
            ->willReturn($baseConfig);

        $this->configService->expects($this->once())
            ->method('getFromFeature')
            ->with($pageFeature, "{$pageName}_view_{$pageAction}")
            ->willReturn($pageConfig);

        $this->normalizerService->expects($this->once())
            ->method('normalize')
            ->with($expectedMergedConfig)
            ->willReturn($normalizedConfig);

        $this->validatorService->expects($this->once())
            ->method('validate')
            ->with($normalizedConfig, $pageKey, $pageEntity, "{$pageFeature}/Config/{$pageKey}_view.php")
            ->willReturn(['isValid' => true, 'errors' => []]);

        $result = $this->service->loadConfiguration(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity
        );

        $this->assertSame($normalizedConfig, $result);
    }

    public function testLoadConfigurationWithEmptyConfigs(): void
    {
        $pageKey = 'empty_page';
        $pageName = 'empty';
        $pageAction = 'view';
        $pageFeature = 'EmptyFeature';
        $pageEntity = 'empty_entity';

        $baseConfig = [];
        $pageConfig = [];

        $expectedMergedConfig = [
            'render_options' => [],
            'form_layout' => [],
            'form_hidden_fields' => [],
            'form_extra_fields' => []
        ];
        $normalizedConfig = ['normalized_empty' => true];

        $this->configService->expects($this->once())
            ->method('get')
            ->with('view.form')
            ->willReturn($baseConfig);

        $this->configService->expects($this->once())
            ->method('getFromFeature')
            ->with($pageFeature, "{$pageName}_view_{$pageAction}")
            ->willReturn($pageConfig);

        $this->normalizerService->expects($this->once())
            ->method('normalize')
            ->with($expectedMergedConfig)
            ->willReturn($normalizedConfig);

        $this->validatorService->expects($this->once())
            ->method('validate')
            ->with($normalizedConfig, $pageKey, $pageEntity, "{$pageFeature}/Config/{$pageKey}_view.php");

        $result = $this->service->loadConfiguration(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity
        );

        $this->assertSame($normalizedConfig, $result);
    }

    public function testLoadConfigurationMergingWithNestedRenderOptions(): void
    {
        $pageKey = 'nested_test';
        $pageName = 'nested';
        $pageAction = 'edit';
        $pageFeature = 'NestedFeature';
        $pageEntity = 'nested_entity';

        $baseConfig = [
            'render_options' => [
                'title' => 'Base Title',
                'description' => 'Base Description',
                'buttons' => [
                    'save' => ['text' => 'Base Save'],
                    'cancel' => ['text' => 'Cancel']
                ],
                'styles' => ['font' => 'Arial']
            ],
            'form_layout' => ['structure' => '1-column'],
        ];

        $pageConfig = [
            'render_options' => [
                'title' => 'Page Title Override',
                'buttons' => [
                    'save' => ['icon' => 'save-icon'], // This should merge with base 'save'
                    'delete' => ['text' => 'Delete']
                ],
                'styles' => ['color' => 'blue'] // This should merge with base 'styles'
            ],
            'form_layout' => ['structure' => '2-column'], // This should override base 'form_layout'
            'form_extra_fields' => ['terms_agreed']
        ];

        $expectedMergedConfig = [
            'render_options' => [
                'title' => 'Page Title Override', // Page overrides base
                'description' => 'Base Description', // Only in base
                'buttons' => [
                    'save' => ['text' => 'Base Save', 'icon' => 'save-icon'], // Deep merged
                    'cancel' => ['text' => 'Cancel'], // Only in base
                    'delete' => ['text' => 'Delete'] // Only in page
                ],
                'styles' => ['font' => 'Arial', 'color' => 'blue'] // Deep merged
            ],
            'form_layout' => ['structure' => '2-column'], // Page overrides base
            'form_hidden_fields' => [],
            'form_extra_fields' => ['terms_agreed'] // Only in page
        ];
        $normalizedConfig = ['normalized_nested_merge' => true];

        $this->configService->expects($this->once())
            ->method('get')
            ->with('view.form')
            ->willReturn($baseConfig);

        $this->configService->expects($this->once())
            ->method('getFromFeature')
            ->with($pageFeature, "{$pageName}_view_{$pageAction}")
            ->willReturn($pageConfig);

        $this->normalizerService->expects($this->once())
            ->method('normalize')
            ->with($expectedMergedConfig)
            ->willReturn($normalizedConfig);

        $this->validatorService->expects($this->once())
            ->method('validate')
            ->with($normalizedConfig, $pageKey, $pageEntity, "{$pageFeature}/Config/{$pageKey}_view.php");

        $result = $this->service->loadConfiguration(
            $pageKey,
            $pageName,
            $pageAction,
            $pageFeature,
            $pageEntity
        );

        $this->assertSame($normalizedConfig, $result);
    }
}