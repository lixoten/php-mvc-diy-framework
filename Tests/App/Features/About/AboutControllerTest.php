<?php

namespace Tests\App\Features\About;

use PHPUnit\Framework\TestCase;
use App\Features\About\AboutController;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;

class AboutControllerTest extends TestCase
{
    public function testIndexActionReturnsResponse(): void
    {
        // Keep the setup code the same
        $flashMock = $this->createMock(FlashMessageServiceInterface::class);
        $viewMock = $this->createMock(View::class);
        $httpFactoryMock = $this->createMock(HttpFactory::class);
        $containerMock = $this->createMock(ContainerInterface::class);

        $controller = $this->getMockBuilder(AboutController::class)
            ->setConstructorArgs([
                ['controller' => 'about', 'action' => 'index'],
                $flashMock,
                $viewMock,
                $httpFactoryMock,
                $containerMock
            ])
            ->onlyMethods(['view'])
            ->getMock();

        $mockResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $controller->method('view')->willReturn($mockResponse);

        // NEW: Test the return value instead of output
        /** @var AboutController $controller */
        $result = $controller->indexAction();

        // Assert we got the response we expected
        $this->assertSame($mockResponse, $result);
    }

    public function testIndexActionCallsViewWithCorrectParameters(): void
    {
        // Arrange
        $flashMock = $this->createMock(FlashMessageServiceInterface::class);
        $viewMock = $this->createMock(View::class);
        $httpFactoryMock = $this->createMock(HttpFactory::class);
        $containerMock = $this->createMock(ContainerInterface::class);

        $controller = $this->getMockBuilder(AboutController::class)
            ->setConstructorArgs([
                ['controller' => 'about', 'action' => 'index'],
                $flashMock,
                $viewMock,
                $httpFactoryMock,
                $containerMock
            ])
            ->onlyMethods(['view'])
            ->getMock();

        // Create the mock response object
        $mockResponse = $this->createMock(\Psr\Http\Message\ResponseInterface::class);

        // Expect the view method to be called with specific arguments
        $controller->expects($this->once())
            ->method('view')
            ->with(
                $this->equalTo('about/index'),
                $this->callback(function ($params) {
                    return $params['title'] === 'About Index Action'
                        && isset($params['actionLinks']);
                })
            )
            ->willReturn($mockResponse); // Add this line to the chain

        /** @var AboutController $controller */
        $controller->indexAction();
    }
}
