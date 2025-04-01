<?php

// filepath: d:\xampp\htdocs\my_projects\mvclxxxixo\Tests\Core\Form\View\SimpleErrorDisplayTest.php
declare(strict_types=1);

namespace Tests\Core\Form\View;

use Core\Form\FormInterface;
use Core\Form\Field\FieldInterface;
use Core\Form\Renderer\FormRendererInterface;
use Core\Form\View\FormView;
use PHPUnit\Framework\TestCase;

class SimpleErrorDisplayTest extends TestCase
{
    private FormInterface $form;
    private FormRendererInterface $renderer;

    protected function setUp(): void
    {
        // Create basic mocks used in all tests
        $this->renderer = $this->createMock(FormRendererInterface::class);
        $this->form = $this->createMock(FormInterface::class);
        $this->form->method('getRenderer')->willReturn($this->renderer);
        $this->form->method('getRenderOptions')->willReturn([]);
    }

    /**
     * @testdox Summary mode displays errors at the top
     */
    public function testSummaryMode(): void
    {
        // Set up renderer to return an error summary
        $this->renderer->method('renderErrors')
            ->willReturn('<div class="alert">Errors</div>');

        // Create FormView in summary mode
        $formView = new FormView($this->form, ['error_display' => 'summary']);

        // Get error summary
        $output = $formView->errorSummary();

        // Verify summary contains error alert
        $this->assertStringContainsString('alert', $output);
    }

    /**
     * @testdox Inline mode has no summary errors
     */
    public function testInlineMode(): void
    {
        // Create FormView in inline mode
        $formView = new FormView($this->form, ['error_display' => 'inline']);

        // Get error summary
        $output = $formView->errorSummary();

        // Verify summary is empty
        $this->assertEmpty($output);
    }
}
