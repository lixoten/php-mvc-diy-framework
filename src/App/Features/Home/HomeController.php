<?php

declare(strict_types=1);

namespace App\Features\Home;

use App\Enums\FlashMessageType;
use Core\Controller;
use App\Helpers\DebugRt as Debug;
use App\Helpers\FlashMessages;
use App\Helpers\Redirector;
use App\Helpers\ReturnPageManager;
use App\Scrap;
use App\Services\Interfaces\FlashMessageServiceInterface;
use App\Services\PageInfoService;
use App\Services\ViewService;
use Core\Database;
use Core\Exceptions\BadRequestException;
use Core\Exceptions\UnauthorizedException;
use Core\Exceptions\ForbiddenException;
use Core\Exceptions\NotFoundException;
use Core\Exceptions\PageNotFoundException;
use Core\Exceptions\RecordNotFoundException;
use Core\Exceptions\ServerErrorException;
use Core\Exceptions\ServiceUnavailableException;
use Core\Exceptions\UnauthenticatedException;
use Core\Exceptions\ValidationException;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;

/**
 * Home controller
 *
 */
class HomeController extends Controller
{
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view,
            $httpFactory,
            $container
        );
    }

    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function indexAction(): ResponseInterface
    {
        $this->flash->add('Welcome to the Home Page.');

        return $this->view(HomeConst::VIEW_HOME_INDEX, [
            'title' => 'Index Action',
            'actionLinks' => $this->getActionLinks('home', ['index', 'test']),
            // 'content' => $this->getActionLinks(['index','test']),
        ]);
    }

    /**
     * Show the index page
     *
     * @return ResponseInterface
     */
    public function testAction(): ResponseInterface
    {
        if (isset($this->route_params['textid'])) {
            return $this->errorPage($this->route_params['textid']);
        }

        return $this->view(HomeConst::VIEW_HOME_TEST, [
            'title' => 'Test Action',
            'errorLinks' => $this->getErrorLinks(),
            'actionLinks' => $this->getActionLinks('home', ['index', 'test']),
        ]);
    }


    public function testResponseAction(): ResponseInterface
    {
        // Create a direct response with no view
        $response = $this->httpFactory->createResponse(200);
        $response->getBody()->write("TESTING DIRECT OUTPUT - NO TEMPLATE");
        return $response;
    }





    private function getErrorLinks(): string
    {
        return "<ul>
            <li><a href=\"/home/test/unauthenticated\">401 - unauthenticated</a></li>
            <li><a href=\"/home/test/forbidden\">403 - forbidden</a></li>
            <li><a href=\"/home/test/pagenotfound\">404 - pagenotfound</a></li>
            <li><a href=\"/home/test/recordnotfound\">404 - recordnotfound</a></li>
            <li><a href=\"/home/test/badrequest\">400 - badrequest</a></li>
            <li><a href=\"/home/test/validation\">422 - validation</a></li>
            <li><a href=\"/home/test/servererror\">500 - servererror</a></li>
            <li><a href=\"/home/test/serviceunavailable\">503 - serviceunavailable</a></li>
            <li></li>
        </ul>";
    }



    // /home/testLogging - Normal request
    // /home/testLogging?error=1 - Test exception logging
    // /home/testLogging?slow=1 - Test performance logging
    public function testLogging(): ResponseInterface
    {
        $response = $this->httpFactory->createResponse();
        $response->getBody()->write("Testing logging middleware - basic test");
        return $response;
    }

    public function testLoggingError(): ResponseInterface
    {
        $response = $this->httpFactory->createResponse();
        $response->getBody()->write("Testing logging middleware - error test");
        throw new \DI\NotFoundException("Test exception for logging");
    }

    public function testLoggingSlow(): ResponseInterface
    {
        // Fix return type and direct output
        $response = $this->httpFactory->createResponse();
        $response->getBody()->write("Testing logging middleware - slow response test");
        sleep(5);
        $response->getBody()->rewind();
        $body = (string)$response->getBody();
        $response->getBody()->write($body . "\nCompleted after delay");
        return $response;
    }


    /**
     * Show the index page
     *
     * @param string $exceptionType
     * @return ResponseInterface
     */
    public function errorPage(string $exceptionType): ResponseInterface
    {
        $content =  $this->getErrorLinks();

        try {
            // Debug::p($exceptionType);
            switch ($exceptionType) {
                case 'divide':
                    $content .= "<p>About to trigger a division by zero exception...</p>";

                    $rr = 5 / 0;
                    break;

                case 'unauthenticated': //401
                    $content .= "<p>About to trigger a unauthenticated exception...</p>";
                    throw new UnauthenticatedException(
                        message: "Oops!!! Please log in again",
                        //code: 401,
                        attemptedResource: "/admin/dashboard", // attempted resource
                        authMethod: "session_cookie",   // auth method
                        reasonCode: "expired_session",  // reason code
                    );
                    break;

                case 'forbidden': // 403
                    $content .= "<p>About to trigger a forbidden exception...</p>";
                    // For permission issues
                    throw new ForbiddenException(
                        // message: "You don't have permission to edit this post",
                        // code: 403,
                        userId: "22", //$user->getId(),
                        requiredPermission: "home.index",
                        userRoles:['guest','user'], //$user->getRoles()
                    );
                    break;

                case 'pagenotfound': //404
                    $content .= "<p>About to trigger a page-not-found exception...</p>";
                    throw new PageNotFoundException(
                        //message: "Page was not fssssound...........",
                        requestedRoute: "Poooost/edit",
                    );
                    break;

                case 'recordnotfound': //404
                    $content .= "<p>About to trigger a record-not-found exception...</p>";
                    throw new RecordNotFoundException(
                        message: "Record not found......",
                        entityType: "Post table",
                        entityId: 22
                    );
                    break;

                case 'badrequest': //400
                    $content .= "<p>About to trigger a bad request exception...</p>";
                    throw new BadRequestException(
                        //message: "Bad Request...........",
                    );
                    break;

                case 'validation': //422
                    $content .= "<p>About to trigger a validation exception...</p>";
                    throw new ValidationException(
                        // message: "Record not found...........",
                        // entityType: "Post table",
                        // entityId: 22
                        [
                            'email' => 'Please enter a valid email address',
                            'password' => 'Password must be at least 8 characters',
                            'terms' => 'You must accept the terms and conditions'
                        ],
                        "Please correct the errors in your form"
                    );
                    break;

                case 'servererror': //500
                    $content .= "<p>About to trigger a server error exception...</p>";
                    throw new ServerErrorException(
                        //message: "Bad Request...........",
                    );
                    break;

                case 'serviceunavailable': //503
                    $content .= "<p>About to trigger a service unavailable exception...</p>";
                    throw new ServiceUnavailableException(
                        //message: "Bad Request...........",
                    );
                    break;

                default:
                    $content .= "<p>No exception triggered. Add ?error=divide or ?error=forbidden to URL to test.</p>";
            }
        } catch (\Exception $e) {
            // Then re-throw to let the global handler take over
             throw $e;
        }
        // finally {
        //     // This will always execute
        //     $content .= "<p>-----------</p>";
        // }


        //$this->view(HomeConst::VIEW_HOME_INDEX, [
        return $this->view('home/index', [
            'title' => 'Welcome Home'
        ]);
    }
}
