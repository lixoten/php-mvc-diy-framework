<?php

// filepath: d:\xampp\htdocs\my_projects\mvcxxxlixo\Tests\Core\Form\View\FormViewTest.php
declare(strict_types=1);

namespace Tests\Core\Form\View;

use Core\Form\FormInterface;
use Core\Form\Renderer\FormRendererInterface;
use Core\Form\View\FormView;
use PHPUnit\Framework\TestCase;

class FormViewTest extends TestCase
{
    /**
     * Test that form theme class is correctly applied when rendering the form
     * @testdox FormView correctly applies theme class to form HTML
     */
    public function testFormThemeClassIsApplied(): void
    {
        // Create mocks
        $renderer = $this->createMock(FormRendererInterface::class);
        $form = $this->createMock(FormInterface::class);

        // Configure form mock
        $form->method('getRenderer')->willReturn($renderer);
        $form->method('getRenderOptions')->willReturn([
            'css_form_theme_class' => 'form-theme-dotted'
        ]);

        // Configure renderer expectations
        $renderer->expects($this->once())
            ->method('renderStart')
            ->with(
                $this->equalTo($form),
                $this->callback(function (array $options): bool {
                    // Verify that options contain the theme class
                    return isset($options['css_form_theme_class']) &&
                           $options['css_form_theme_class'] === 'form-theme-dotted';
                })
            )
            ->willReturn('<form method="post" class="form-theme-dotted needs-validation" novalidate>');

        // Create FormView and test it
        $formView = new FormView($form);
        $output = $formView->start();

        // Assert the rendered form contains the theme class
        $this->assertStringContainsString('form-theme-dotted', $output);
    }
}
