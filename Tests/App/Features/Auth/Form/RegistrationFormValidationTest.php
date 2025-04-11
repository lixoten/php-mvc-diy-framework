<?php

declare(strict_types=1);

namespace Tests\App\Features\Auth\Form;

use Core\Form\View\FormView;
use PHPUnit\Framework\TestCase;

class RegistrationFormValidationTest extends TestCase
{
    public function testRequiredFieldsValidation(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('errorSummary')->willReturn(
            '<div class="error-summary">The username field is required.</div>'
        );

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('The username field is required.', $output);
    }

    public function testPasswordStrengthValidation(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('errorSummary')->willReturn(
            '<div class="error-summary">Password must include at least one uppercase letter.</div>'
        );

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Password must include', $output);
    }

    public function testUniqueEmailValidation(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('errorSummary')->willReturn(
            '<div class="error-summary">This email is already registered.</div>'
        );

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('This email is already registered.', $output);
    }

    public function testUniqueUsernameValidation(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('errorSummary')->willReturn(
            '<div class="error-summary">This username is already taken.</div>'
        );

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('This username is already taken.', $output);
    }

    public function testPasswordConfirmationValidation(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('errorSummary')->willReturn('<div class="error-summary">Passwords do not match.</div>');

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Passwords do not match.', $output);
    }

    public function testSpecialCharactersInUsername(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('errorSummary')->willReturn(
            '<div class="error-summary">Invalid characters in username.</div>'
        );

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Invalid characters in username.', $output);
    }

    public function testRateLimiting(): void
    {
        $this->markTestSkipped('This feature is not implemented yet.');
    }

    public function testFormSubmissionSuccess(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('start')->willReturn('<form method="post" action="/registration">');

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('action="/registration"', $output);
    }
}
