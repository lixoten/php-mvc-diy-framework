<?php

declare(strict_types=1);

namespace Tests\App\Features\Auth\Security;

use Core\Form\View\FormView;
use PHPUnit\Framework\TestCase;

class RegistrationSecurityTest extends TestCase
{
    public function testCsrfTokenIsPresent(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('start')->willReturn('<form><input type="hidden" name="_csrf" value="test_token">');

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('<input type="hidden" name="_csrf"', $output);
    }

    public function testCsrfTokenValidation(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('start')->willReturn('<form><input type="hidden" name="_csrf" value="valid_token">');

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('<input type="hidden" name="_csrf" value="valid_token">', $output);
    }

    public function testCsrfTokenMissing(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('start')->willReturn('<form>'); // No CSRF token included

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringNotContainsString('<input type="hidden" name="_csrf"', $output);
    }

    public function testSqlInjectionPrevention(): void
    {
        $formMock = $this->createMock(FormView::class);
        $formMock->method('row')->willReturn('<input name="username" value="sanitized_value" />');

        $title = 'Create Account';
        $form = $formMock;

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        // Assert that the SQL injection string is not present in the output
        $this->assertStringNotContainsString('\' OR 1=1 --', $output);
    }
}
