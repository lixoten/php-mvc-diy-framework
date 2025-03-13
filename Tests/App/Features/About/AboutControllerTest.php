<?php

namespace Tests\App\Features\About;

use PHPUnit\Framework\TestCase;
use App\Features\About\AboutController;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\View;

class AboutControllerTest extends TestCase
{
    public function testIndexActionOutputsHello(): void
    {
        // Create a mock for the flash service
        $flashMock = $this->createMock(FlashMessageServiceInterface::class);
        $viewMock = $this->createMock(View::class);

        // Create a partial mock that overrides the view method
        $controller = $this->getMockBuilder(AboutController::class)
            ->setConstructorArgs([
                ['controller' => 'about', 'action' => 'index'],
                $flashMock,
                $viewMock
            ])
            ->onlyMethods(['view']) // Only mock the view method
            ->getMock();

        // Set up the view method to do nothing (we're testing the echo, not the view)
        // For void methods, use willReturnCallback with an empty function
        /** @var AboutController&\PHPUnit\Framework\MockObject\MockObject $controller */
        $controller->method('view')->willReturnCallback(function () {
            // Do nothing
        });

        // Capture the output
        ob_start();
        $controller->indexAction();
        $output = ob_get_clean();

        // Assert that it outputs "hello"
        $this->assertStringContainsString('hello', $output);
    }

    public function testIndexActionCallsViewWithCorrectParameters(): void
    {
        // Arrange
        $flashMock = $this->createMock(FlashMessageServiceInterface::class);
        $viewMock = $this->createMock(View::class);

        $controller = $this->getMockBuilder(AboutController::class)
            ->setConstructorArgs([
                ['controller' => 'about', 'action' => 'index'],
                $flashMock,
                $viewMock
            ])
            ->onlyMethods(['view'])
            ->getMock();

        // Expect the view method to be called with specific arguments
        $controller->expects($this->once())
            ->method('view')
            ->with(
                $this->equalTo('about/index'),
                $this->equalTo(['title' => 'Welcome About'])
            );

        /** @var AboutController $controller */
        $controller->indexAction();
    }
}
