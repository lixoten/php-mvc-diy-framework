<?php

namespace Tests\App\Services;

use PHPUnit\Framework\TestCase;
use App\Enums\FlashMessageType;
use Tests\App\Services\MockFlashMessageService;

class FlashMessageServiceTest extends TestCase
{
    private MockFlashMessageService $flashService;

    protected function setUp(): void
    {
        // Use the mock service instead of real one
        $this->flashService = new MockFlashMessageService();
    }

    public function testAddFlashMessage(): void
    {
        // Add a message
        $this->flashService->add('Test message');

        // Check if the message exists
        $messages = $this->flashService->peek();

        $this->assertNotEmpty($messages);
        $this->assertEquals('Test message', $messages['info'][0]['message']);
    }

    public function testHasFlashMessage(): void
    {
        // Initially no messages
        $this->assertFalse($this->flashService->has());

        // Add a message
        $this->flashService->add('Test message');

        // Now should have messages
        $this->assertTrue($this->flashService->has());
    }

    public function testGetClearsMessages(): void
    {
        // Add a message
        $this->flashService->add('Test message');

        // Get should return and clear messages
        $messages = $this->flashService->get();
        $this->assertNotEmpty($messages);

        // Should now be empty
        $this->assertEmpty($this->flashService->peek());
    }
}
