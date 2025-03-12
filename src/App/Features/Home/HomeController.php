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
use Core\Exceptions\UnauthenticatedException;
use Core\Exceptions\ValidationException;
use Core\View;

/**
 * Home controller
 *
 */
class HomeController extends Controller
{
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view
    ) {
        parent::__construct(
            $route_params,
            $flash,
            $view
        );
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
            <li></li>
        </ul>";
    }


    /**
     * Show the index page
     *
     * @return void
     */
    public function testAction(): void
    {
        if (isset($this->route_params['textid'])) {
            $this->errorPage($this->route_params['textid']);
        }

        $this->view('home/index', [
            'title' => $this->getErrorLinks()
        ]);
    }

    // /home/testLogging - Normal request
    // /home/testLogging?error=1 - Test exception logging
    // /home/testLogging?slow=1 - Test performance logging
    public function testLogging()
    {
        echo "Testing logging middleware - basic test";
    }

    public function testLoggingError()
    {
        echo "Testing logging middleware - error test";
        //throw new \Exception("Test exception for logging");
        throw new \DI\NotFoundException("Test exception for logging");
    }

    public function testLoggingSlow()
    {
        echo "Testing logging middleware - slow response test";
        sleep(5);
        echo "Completed after delay";
    }

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction(): void
    {
        $this->flash->add('Welcome to the Home Page.');

        // $this->view('home/index', [
        $this->view(HomeConst::VIEW_HOME_INDEX, [
            'title' => $this->getErrorLinks()
        ]);
    }


    /**
     * Show the index page
     *
     * @return void
     */
    public function errorPage(string $exceptionType): void
    {
        echo $this->getErrorLinks();

        try {
            // Debug::p($exceptionType);
            switch ($exceptionType) {
                case 'divide':
                    echo "<p>About to trigger a division by zero exception...</p>";
                    $rr = 5 / 0;
                    break;

                case 'unauthenticated': //401
                    echo "<p>About to trigger a unauthenticated exception...</p>";
                    throw new UnauthenticatedException(
                        message: "Oops!!! Please log in again",
                        //code: 401,
                        attemptedResource: "/admin/dashboard", // attempted resource
                        authMethod: "session_cookie",   // auth method
                        reasonCode: "expired_session",  // reason code
                    );
                    break;

                case 'forbidden': // 403
                    echo "<p>About to trigger a forbidden exception...</p>";
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
                    echo "<p>About to trigger a page-not-found exception...</p>";
                    throw new PageNotFoundException(
                        //message: "Page was not fssssound...........",
                        requestedRoute: "Poooost/edit",
                    );
                    break;

                case 'recordnotfound': //404
                    echo "<p>About to trigger a record-not-found exception...</p>";
                    throw new RecordNotFoundException(
                        message: "Record not found......",
                        entityType: "Post table",
                        entityId: 22
                    );
                    break;

                case 'badrequest': //400
                    echo "<p>About to trigger a bad request exception...</p>";
                    throw new BadRequestException(
                        //message: "Bad Request...........",
                    );
                    break;

                case 'validation': //422
                    echo "<p>About to trigger a validation exception...</p>";
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

                default:
                    echo "<p>No exception triggered. Add ?error=divide or ?error=forbidden to URL to test.</p>";
            }
        } catch (\Exception $e) {
            // Then re-throw to let the global handler take over
             throw $e;

             Debug::p(111);
            // Handle the exception
            $bgColor = $e->getCode() == 403 ? '#fff3cd' : '#ffdddd';
            $borderColor = $e->getCode() == 403 ? '#ffeeba' : '#ff0000';

            echo "<div style='background-color: $bgColor;
            border: 1px solid $borderColor; padding: 10px; margin: 10px 0;'>";
            echo "<h3>Caught " . ($e->getCode() == 403 ? 'Forbidden' : 'Exception') . ":</h3>";
            echo "<p>Message: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p>File: " . $e->getFile() . "</p>";
            echo "<p>Line: " . $e->getLine() . "</p>";
            echo "<p>Code: " . $e->getCode() . "</p>";
            echo "</div>";
            Debug::p(22);

            // For a real 403 response, you'd set the HTTP status code:
            if ($e->getCode() == 403) {
                header('HTTP/1.1 403 Forbidden');
            }

            // Log the error (optional)
            error_log($e->getMessage());
        }
        // finally {
        //     // This will always execute
        //     echo "<p>Finally block executed</p>";
        // }


        //$this->view(HomeConst::VIEW_HOME_INDEX, [
        $this->view('home/index', [
            'title' => 'Welcome Home'
        ]);
    }
}
