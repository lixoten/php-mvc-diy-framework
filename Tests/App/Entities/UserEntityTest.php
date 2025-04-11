<?php

declare(strict_types=1);

namespace Tests\App\Entities;

use App\Entities\User;
use PHPUnit\Framework\TestCase;

class UserEntityTest extends TestCase
{
    public function testPasswordIsHashed(): void
    {
        $user = new User();
        $user->setPassword('plaintextpassword');

        $this->assertNotEquals('plaintextpassword', $user->getPasswordHash());
        $this->assertTrue(password_verify('plaintextpassword', $user->getPasswordHash()));
    }

    public function testEmailVerificationFlow(): void
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('testuser@example.com');
        $user->generateActivationToken();

        $this->assertNotNull($user->getActivationToken());
        $this->assertFalse($user->isActive());

        // Simulate clicking the verification link
        $user->activate();

        $this->assertTrue($user->isActive());
        $this->assertNull($user->getActivationToken());
    }

    public function testActivationTokenGeneration(): void
    {
        $user = new User();
        $token = $user->generateActivationToken();

        $this->assertNotNull($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
        $this->assertNotNull($user->getActivationTokenExpiry());
    }

    public function testUserRoles(): void
    {
        $user = new User();
        $user->setRoles(['user']);

        $this->assertTrue($user->hasRole('user'));
        $this->assertFalse($user->hasRole('admin'));

        $user->addRole('admin');
        $this->assertTrue($user->hasRole('admin'));

        $user->removeRole('admin');
        $this->assertFalse($user->hasRole('admin'));
    }
}
