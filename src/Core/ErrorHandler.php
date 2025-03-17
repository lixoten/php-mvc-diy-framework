<?php

declare(strict_types=1);

namespace Core;

use Core\Errors\ErrorsController;
use App\Helpers\FlashMessages;
use App\Helpers\Redirector;
use App\Helpers\ReturnPageManager;
use App\Helpers\DebugRt as Debug;
use App\Services\ActivationTokenGenerationException;
use Core\Exceptions\BadRequestException;
use Core\Exceptions\DatabaseException;
use Core\Exceptions\ForbiddenException;
use Core\Exceptions\HttpException;
use Core\Exceptions\PageNotFoundException;
use Core\Exceptions\RecordNotFoundException;
use Core\Exceptions\UnauthenticatedException;
use Exception;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Throwable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Core\Http\HttpFactory;

class ErrorHandler
{
    private bool $developmentMode;
    private Logger $logger;
    private ?ContainerInterface $container;
    private ?HttpFactory $httpFactory;

    public function __construct(
        bool $developmentMode = false,
        Logger $logger = null,
        ?ContainerInterface $container = null,
        ?HttpFactory $httpFactory = null
    ) {
        $this->developmentMode = $developmentMode;
        $this->logger = $logger;
        $this->container = $container;
        $this->httpFactory = $httpFactory;
    }

    //TODO
    //To complete your HTTP exception family, you might also consider adding:
    // BadRequestException (400)
    // MethodNotAllowedException (405)
    // ConflictException (409)
    // InternalServerErrorException (500)


    /**
     * Handle an exception and return a PSR-7 response
     *
     * @param Throwable $e
     * @param ServerRequestInterface|null $request
     * @return ResponseInterface
     */
    public function handleException(Throwable $e, ?ServerRequestInterface $request = null): ResponseInterface
    {
        // Important!!!// SEE NOTES BELLOW for list of all Exception (base class)

        // Log the exception regardless of type
        $this->logger?->error(get_class($e) . ": " . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ]);

        // Determine status code based on exception type
        $statusCode = 500; // Default
        $additionalContext = [];

        // Handle specific exception types
        if ($e instanceof UnauthenticatedException) {
            $statusCode = 401;
            $additionalContext = [
                'attempted_resource' => $e->getAttemptedResource(),
                'auth_method' => $e->getAuthMethod(),
                'reason_code' => $e->getReasonCode()
            ];
        } elseif ($e instanceof ForbiddenException) {
            $statusCode = 403;
            $additionalContext = [
                'user_id' => $e->getUserId(),
                'required_permission' => $e->getRequiredPermission(),
                'user_roles' => $e->getUserRoles() ? implode(', ', $e->getUserRoles()) : null
            ];
        } elseif ($e instanceof RecordNotFoundException) {
            $statusCode = 404;
            $additionalContext = [
                'entity_type' => $e->getEntityType(),
                'entity_id' => $e->getEntityId()
            ];
        } elseif ($e instanceof PageNotFoundException) {
            $statusCode = 404;
            $additionalContext = [
                'requestedRoute' => $e->getRequestedRoute(),
            ];
        } elseif ($e instanceof BadRequestException) {
            $statusCode = 400;
        } elseif ($e instanceof InvalidArgumentException) {
            ## InvalidArgumentException is not HTTP exception
            ## it is a logic exception
            $statusCode = 400;
        } else {
            // For unspecified exceptions, use the exception code if it's a valid HTTP status
            $statusCode = ($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
        }

        // If httpFactory is not available, create a simple response
        if (!$this->httpFactory) {
            // Create a basic response (without PSR-7)
            header('HTTP/1.1 ' . $statusCode);
            echo $this->developmentMode ?
                $this->generateDevelopmentErrorPage($e, $request) :
                $this->generateProductionErrorPage($statusCode);
            exit;
        }

        // Create a PSR-7 response
        $response = $this->httpFactory->createResponse($statusCode);

        // Get session from request if available
        $sessionManager = null;
        if ($request) {
            $sessionManager = $request->getAttribute('session');
        }

        // If no session in request, create a fallback one
        if (!$sessionManager) {
            $sessionManager = new \Core\Session\SessionManager([
                'name' => 'mvc3_session',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        // Try to use the ErrorsController if available
        if ($this->container && $this->container->has('Core\Errors\ErrorsController')) {
            try {
                $errorController = $this->container->get('Core\Errors\ErrorsController');

                // Use the response object directly instead of output buffering
                $errorResponse = $errorController->showError(
                    $statusCode,
                    $e->getMessage(),
                    [
                        'exception' => $e,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'additionalContext' => $additionalContext
                    ]
                );

                // Add Content-Type header if needed
                if (!$errorResponse->hasHeader('Content-Type')) {
                    $errorResponse = $errorResponse->withHeader('Content-Type', 'text/html');
                }

                return $errorResponse;
            } catch (\Throwable $innerException) {
                // If ErrorsController fails, fall back to simple error page
                $this->logger?->error("Error in ErrorsController: " . $innerException->getMessage());
            }
        }

        // Fallback to basic error pages
        if ($this->developmentMode) {
            $content = $this->generateDevelopmentErrorPage($e, $request);
        } else {
            $content = $this->generateProductionErrorPage($statusCode);
        }

        $response->getBody()->write($content);
        return $response;
    }


    private function getExtraMessage(Exception $e): string
    {
        if ($this->developmentMode) {
            // Detailed information for developers
            return "File: {$e->getFile()} Line:{$e->getLine()}\n" .
                "Stack Trace:\n" . $e->getTraceAsString();
        } else {
            // Limited info for production
            return "Error ID: " . uniqid();
        }
    }

    /**
     * Convert PHP errors to exceptions
     *
     * @param int $level Error level
     * @param string $message Error message
     * @param string $file Filename the error was raised in
     * @param int $line Line number in the file
     * @return bool
     * @throws \ErrorException
     */
    public function handleError(int $level, string $message, string $file, int $line): bool
    {
        if (error_reporting() & $level) {
            // Convert the error to an exception
            throw new \ErrorException($message, 0, $level, $file, $line);
        }

        // Don't execute PHP's internal error handler
        return true;
    }


    /**
     * Generate a detailed error page for development
     *
     * @param Throwable $exception
     * @param ServerRequestInterface|null $request
     * @return string
     */
    private function generateDevelopmentErrorPage(Throwable $exception, ?ServerRequestInterface $request): string
    {
        $content = '<h1>Error: ' . htmlspecialchars($exception->getMessage()) . '</h1>';
        $content .= '<p>Uncaught exception: ' . get_class($exception) . '</p>';
        $content .= '<p>Code: ' . $exception->getCode() . '</p>';
        $content .= '<p>File: ' . $exception->getFile() . ' (line ' . $exception->getLine() . ')</p>';
        $content .= '<h2>Stack Trace</h2>';
        $content .= '<pre>' . htmlspecialchars($exception->getTraceAsString()) . '</pre>';

        if ($request) {
            $content .= '<h2>Request Details</h2>';
            $content .= '<p>Method: ' . htmlspecialchars($request->getMethod()) . '</p>';
            $content .= '<p>URI: ' . htmlspecialchars((string)$request->getUri()) . '</p>';

            $content .= '<h3>Query Parameters</h3>';
            $content .= '<pre>' . htmlspecialchars(print_r($request->getQueryParams(), true)) . '</pre>';

            $content .= '<h3>Request Body</h3>';
            $content .= '<pre>' . htmlspecialchars(print_r($request->getParsedBody(), true)) . '</pre>';
        }

        return $content;
    }

    /**
     * Generate a user-friendly error page for production
     *
     * @param int $statusCode
     * @return string
     */
    private function generateProductionErrorPage(int $statusCode): string
    {
        $messages = [
            404 => 'The page you requested could not be found.',
            500 => 'An error occurred while processing your request.',
        ];

        $message = $messages[$statusCode] ?? 'An error occurred.';

        return '<h1>Error</h1><p>' . $message . '</p>';
    }
}

////////////////////////////////////////////
//  * Exception (base class)
// ├── LogicException
// │   ├── BadFunctionCallException // TODO
// │   ├── DomainException          // TODO
// │   ├── InvalidArgumentException // Done
// │   ├── LengthException          // TODO
// │   └── OutOfRangeException      // TODO
// └── RuntimeException
//     ├── OutOfBoundsException     // TODO
//     ├── OverflowException        // TODO
//     ├── RangeException           // TODO
//     ├── UnderflowException       // TODO
//     └── UnexpectedValueException // TODO
//
// HttpExceptionInterface (interface)               // TODO
// └── HttpException (custom base class)            // Done
//     ├── HttpBadRequestException (400)            // Done
//     │   └── ValidationException (400 with field errors)
//     ├── HttpUnauthorizedException (401)          // sd
//     ├── HttpForbiddenException (403)             // Done
//     ├── HttpNotFoundException (404)              // Done
//     │   └── ResourceNotFoundException (404 for specific resources)
//     ├── HttpMethodNotAllowedException (405)        // TODO
//     ├── HttpNotAcceptableException (406)
//     ├── HttpRequestTimeoutException (408)
//     ├── HttpConflictException (409)                // TODO
//     ├── HttpGoneException (410)
//     ├── HttpUnsupportedMediaTypeException (415)
//     ├── HttpUnprocessableEntityException (422)     // TODO
//     ├── HttpTooManyRequestsException (429)
//     └── HttpServerErrorException (500+)
//         ├── HttpInternalServerErrorException (500) // TODO
//         ├── HttpNotImplementedException (501)
//         ├── HttpBadGatewayException (502)
//         ├── HttpServiceUnavailableException (503)
//         └── HttpGatewayTimeoutException (504)
////////////////////////////////////////////

## 455 309
