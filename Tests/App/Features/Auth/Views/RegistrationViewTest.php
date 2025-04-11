<?php

declare(strict_types=1);

namespace Tests\App\Features\Auth\Views;

use Core\Form\View\FormView;
use PHPUnit\Framework\TestCase;

class RegistrationViewTest extends TestCase
{
    public function testSuccessfulRegistrationFlow(): void
    {
        $formViewMock = $this->createMock(FormView::class);
        $formViewMock->method('errorSummary')->willReturn('<div class="error-summary">No errors</div>');
        $formViewMock->method('start')->willReturn('');  // Empty to avoid duplicate form tags
        $formViewMock->method('row')->willReturn('<input name="username" />');
        $formViewMock->method('submit')->willReturn('<button type="submit">Register</button>');
        $formViewMock->method('end')->willReturn('</form>');

        $title = 'Create Account';
        $form = $formViewMock; // Use FormView for rendering

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/registration.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('<input name="username" />', $output);
        $this->assertStringContainsString('<button type="submit">Register</button>', $output);
        $this->assertStringContainsString('Already have an account?', $output);
        $this->assertStringContainsString('<a href="/login">Log in</a>', $output);
        $this->assertStringContainsString('</form>', $output);
    }

    public function testEmailVerificationSuccess(): void
    {
        $title = 'Email Verified';
        $username = 'testuser';

        ob_start();
        include 'd:/xampp/htdocs/my_projects/mvclixo/src/App/Features/Auth/Views/verification_success.php';
        $output = ob_get_clean();

        $this->assertStringContainsString(
            'Congratulations, testuser! Your email address has been verified successfully.',
            $output
        );
    }

    public function testVerificationPendingPageShowsSuccessMessage(): void
    {
        $title = 'Verify Your Email';
        $this->markTestSkipped("This test needs to be updated to match the flash message implementation");
    }
}
