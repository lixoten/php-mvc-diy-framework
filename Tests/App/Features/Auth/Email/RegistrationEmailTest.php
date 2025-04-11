<?php

declare(strict_types=1);

namespace Tests\App\Features\Auth\Email;

use App\Entities\User;
use App\Services\Email\EmailNotificationService;
use PHPUnit\Framework\TestCase;

class RegistrationEmailTest extends TestCase
{
    public function testResendVerificationEmail(): void
    {
        $user = new User();
        $user->setEmail('testuser@example.com');
        $user->generateActivationToken();

        /** @var \App\Services\Email\EmailNotificationService|\PHPUnit\Framework\MockObject\MockObject $emailServiceMock */
        $emailServiceMock = $this->createMock(EmailNotificationService::class);
        $emailServiceMock->expects($this->once())
            ->method('sendVerificationEmail')
            ->with($user, $user->getActivationToken());

        $emailServiceMock->sendVerificationEmail($user, $user->getActivationToken());
    }
}
