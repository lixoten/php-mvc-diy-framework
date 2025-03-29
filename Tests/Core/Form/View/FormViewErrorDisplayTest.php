<?php

// filepath: d:\xampp\htdocs\my_projects\mvclixo\Tests\Core\Form\View\FormViewErrorDisplayTest.php
declare(strict_types=1);

namespace Tests\Core\Form\View;

use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;
use Core\Form\Renderer\FormRendererInterface;
use Core\Form\View\FormView;
use PHPUnit\Framework\TestCase;

class FormViewErrorDisplayTest extends TestCase
{
    /**
     * @testdox FormView displays errors inline when in inline mode
     */
    public function testInlineErrorDisplay(): void
    {
        // Create mocks
        $renderer = $this->createMock(FormRendererInterface::class);
        $form = $this->createMock(FormInterface::class);
        $field = $this->createMock(FieldInterface::class);

        // Setup form
        $form->method('getRenderer')->willReturn($renderer);
        $form->method('getRenderOptions')->willReturn([]);
        $form->method('hasField')->willReturn(true);
        $form->method('getField')->willReturn($field);

        // Configure renderer expectations for inline errors
        $renderer->expects($this->once())
            ->method('renderField')
            ->with(
                $this->equalTo($field),
                $this->callback(function ($options) {
                    return !isset($options['hide_inline_errors']);
                })
            )
            ->willReturn('<div class="mb-3"><input class="is-invalid"><div class="invalid-feedback">Error</div></div>');

        // Create FormView with inline error display
        $formView = new FormView($form, ['error_display' => 'inline']);

        // Render field row
        $output = $formView->row('email');

        // Assert error is shown inline
        $this->assertStringContainsString('invalid-feedback', $output);
        $this->assertEmpty($formView->errorSummary());
    }

    /**
     * @testdox FormView displays errors in summary when in summary mode
     */
    public function testSummaryErrorDisplay(): void
    {
        // Create mocks
        $renderer = $this->createMock(FormRendererInterface::class);
        $form = $this->createMock(FormInterface::class);
        $field = $this->createMock(FieldInterface::class);

        // Setup form
        $form->method('getRenderer')->willReturn($renderer);
        $form->method('getRenderOptions')->willReturn([]);
        $form->method('hasField')->willReturn(true);
        $form->method('getField')->willReturn($field);

        // Configure renderer for summary errors
        $renderer->expects($this->once())
            ->method('renderErrors')
            ->willReturn('<div class="alert alert-danger">Please correct the errors</div>');

        // Configure renderer for field without inline errors
        $renderer->expects($this->once())
            ->method('renderField')
            ->with(
                $this->equalTo($field),
                $this->callback(function ($options) {
                    return isset($options['hide_inline_errors']) &&
                           $options['hide_inline_errors'] === true;
                })
            )
            ->willReturn('<div class="mb-3"><input></div>');

        // Create FormView with summary error display
        $formView = new FormView($form, ['error_display' => 'summary']);

        // Get outputs
        $summaryOutput = $formView->errorSummary();
        $fieldOutput = $formView->row('email');

        // Assert errors are in summary but not inline
        $this->assertStringContainsString('alert-danger', $summaryOutput);
        $this->assertStringNotContainsString('invalid-feedback', $fieldOutput);
    }

    /**
     * @testdox FormView sets hide_inline_errors automatically in summary mode
     */
    public function testHideInlineErrorsOption(): void
    {
        // Create a form mock
        $form = $this->createMock(FormInterface::class);
        $form->method('getRenderOptions')->willReturn([]);

        // Create FormView with summary mode
        $formView = new FormView($form, ['error_display' => 'summary']);

        // Use reflection to check internal options
        $reflection = new \ReflectionClass(FormView::class);
        $optionsProperty = $reflection->getProperty('options');
        $optionsProperty->setAccessible(true);
        $options = $optionsProperty->getValue($formView);

        // Assert hide_inline_errors is set
        $this->assertArrayHasKey('hide_inline_errors', $options);
        $this->assertTrue($options['hide_inline_errors']);
    }
}
