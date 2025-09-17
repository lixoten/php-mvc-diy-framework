<?php

declare(strict_types=1);

namespace Core;

use App\Enums\AttentionType;
use App\Enums\FlashMessageType;
use Core\Errors\ErrorsController;
use App\Helpers\FlashMessages;
use App\Helpers\Redirector;
use App\Helpers\ReturnPageManager;
use App\Helpers\DebugRt;
use App\Services\ActivationTokenGenerationException;
use Core\Exceptions\BadRequestException;
use Core\Exceptions\ConnectionException;
use Core\Exceptions\DatabaseException;
use Core\Exceptions\ForbiddenException;
use Core\Exceptions\HttpException;
use Core\Exceptions\PageNotFoundException;
use Core\Exceptions\QueryException;
use Core\Exceptions\RecordNotFoundException;
use Core\Exceptions\ServerErrorException;
use Core\Exceptions\UnauthenticatedException;
use Core\Exceptions\ValidatorNotFoundException;
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
        // DebugRt::j('1', '', $developmentMode);
        $this->logger = $logger;
        $this->container = $container;
        $this->httpFactory = $httpFactory;
    }

    // TODO
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

        // Determine status code based on exception type
        $statusCode = 500; // Default
        $additionalContext = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ];

        // Handle specific exception types
        if ($e instanceof UnauthenticatedException) {
            $statusCode = 401;
            $additionalContext['attempted_resource'] = $e->getAttemptedResource();
            $additionalContext['auth_method'] = $e->getAuthMethod();
            $additionalContext['reason_code'] = $e->getReasonCode();

            // // Add flash message
            //$this->flash->add('Please log in to continue', FlashMessageType::Warning);

            // THIS IS THE REDIRECTION PART
            // return $this->httpFactory->createRedirectResponse('/login');
            $rrr = $this->httpFactory->createRedirectResponse('/login');
            return $rrr;
        } elseif ($e instanceof ForbiddenException) {
            $statusCode = 403;
            $additionalContext['user_id'] = $e->getUserId();
            $additionalContext['required_permission'] = $e->getRequiredPermission();
            $additionalContext['user_roles'] = $e->getUserRoles() ? implode(', ', $e->getUserRoles()) : null;
        } elseif ($e instanceof RecordNotFoundException) {
            $statusCode = 404;
            $additionalContext['entity_type'] = $e->getEntityType();
            $additionalContext['entity_id'] = $e->getEntityId();
        } elseif ($e instanceof PageNotFoundException) {
            $statusCode = 404;
            $additionalContext['requestedRoute'] = $e->getRequestedRoute();
        } elseif ($e instanceof BadRequestException) {
            ## Notes-: 400 Bad Request: Client error, requires the client to fix their
            ## request (e.g., incorrect data, missing parameters).
            $statusCode = 400;
        } elseif ($e instanceof ValidatorNotFoundException) {
            ## // TODO
            $statusCode = 400;
        } elseif ($e instanceof InvalidArgumentException) {
            ## InvalidArgumentException is not HTTP exception
            ## it is a logic exception
            $statusCode = 400;
        } elseif ($e instanceof DatabaseException) {
            ## Notes-: 500 Internal Server Error: Server error, requires the server-side developers or
            ## administrators to fix the problem
            $statusCode = 500;

            // Add database-specific context for logging and debugging
            $additionalContext['sql_state'] = $e->getSqlState();

            // Add query information if available
            if ($e->getQuery()) {
                $additionalContext['query'] = $e->getQuery();
            }

            // Add driver and configuration info for connection exceptions
            if ($e instanceof ConnectionException) {
                $additionalContext['driver'] = $e->getDriver();
                $additionalContext['config'] = $e->getConfig();
            }

            // Add bindings for query exceptions
            if ($e instanceof QueryException) {
                $additionalContext['bindings'] = $e->getBindings();
            }

            // Log with enhanced details
            //$this->logger?->error("Database error: " . $e->getMessage(), $additionalContext);

            // Create user-friendly message for production
            if (!$this->developmentMode) {
                // Replace the original exception message with something user-friendly
                $e = new ServerErrorException(
                    "We encountered a database error. Our team has been notified.",
                    500,
                    $e // Keep original exception as the "previous" one
                );
            }
        } elseif ($e instanceof \Core\Exceptions\ConfigurationException) {
            $statusCode = 500;
            $additionalContext['config_file'] = $e->getConfigFile();
            $additionalContext['entity_type'] = $e->getEntityType();
            $additionalContext['suggestion'] = $e->getSuggestion();

            // In development mode, add the suggestion to the message
            if ($this->developmentMode) {
                $e = new \Core\Exceptions\ConfigurationException(
                    $e->getMessage() . "\n\nSuggestion: " . $e->getSuggestion() .
                    "\n\nCheck configuration in: " . $e->getConfigFile(),
                    $e->getConfigFile(),
                    $e->getEntityType(),
                    $e->getSuggestion(),
                    $e->getCode(),
                    $e->getPrevious()
                );
            }
        } else {
            // DebugRt::p($e);
            // DebugRt::j('1', '', $e);
            // For unspecified exceptions, use the exception code if it's a valid HTTP status
            $statusCode = ($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;
        }

        $logLevel = 'error'; // Default
        if ($statusCode < 400) {
            $logLevel = 'info';
        } elseif ($statusCode < 500) {
            $logLevel = 'warning';
        }

        // Single comprehensive log with all context
        $this->logger?->$logLevel(get_class($e) . ": " . $e->getMessage(), $additionalContext);

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
        $this->logger?->$logLevel(get_class($e) . ": " . $e->getMessage(), $additionalContext);

        // If no session in request, create a fallback one
        if (!$sessionManager) {
            $this->logger?->notice(AttentionType::FALLBACK->errorMessage('for $sessionManager'));
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
                $debugHelp = $this->generateDevelopmentErrorPage($e, $request);

                $errorResponse = $errorController->showError(
                    $statusCode,
                    $e->getMessage(),
                    [
                        'exception' => $e,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'additionalContext' => $additionalContext,
                        'debugHelp' => $debugHelp
                    ]
                );

                // Add Content-Type header if needed
                if (!$errorResponse->hasHeader('Content-Type')) {
                    $errorResponse = $errorResponse->withHeader('Content-Type', 'text/html');
                }
                //DebugRt::j('a1', '', $errorResponse);
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

    ######/TODO  Furture to make Errorhandler AJAX-aware
    ######/ ...existing code...
    ######* @return ResponseInterface
    ######*/
    ######public function handleException(Throwable $e, ?ServerRequestInterface $request = null): ResponseInterface
    ######
    ######   // --- START: NEW LOGIC ---
    ######
    ######   // 1. Check if the request is an AJAX request by looking for the header our JavaScript sends.
    ######   $isAjax = $request && $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    ######
    ######   // --- END: NEW LOGIC ---
    ######
    ######
    ######
    ######   // ... all your existing code to determine status code and log the error ...
    ######   // ... this part doesn't change at all ...
    ######   $statusCode = 500; // Default
    ######   // ...
    ######   $this->logger?->$logLevel(get_class($e) . ": " . $e->getMessage(), $additionalContext);
    ######
    ######
    ######   // --- START: NEW LOGIC ---
    ######
    ######   // 2. If it IS an AJAX request, build a JSON response and exit early.
    ######   if ($isAjax) {
    ######       // Determine the error message based on development mode.
    ######       $errorMessage = $this->developmentMode ? $e->getMessage() : 'An error occurred.';
    ######
    ######       $payload = [
    ######           'success' => false,
    ######           'message' => $errorMessage,
    ######       ];
    ######
    ######       // If we are in development, add extra debug info to the JSON payload.
    ######       if ($this->developmentMode) {
    ######           $payload['debug'] = [
    ######               'exception' => get_class($e),
    ######               'file' => $e->getFile(),
    ######               'line' => $e->getLine(),
    ######           ];
    ######       }
    ######
    ######       // Use the existing HttpFactory to create a JSON response.
    ######       // This is the same factory you use elsewhere.
    ######       if ($this->httpFactory) {
    ######           return $this->httpFactory->createJsonResponse($payload)->withStatus($statusCode);
    ######       }
    ######
    ######       // Fallback if the factory isn't available (this is just for extreme cases).
    ######       header('Content-Type: application/json');
    ######       http_response_code($statusCode);
    ######       echo json_encode($payload);
    ######       exit;
    ######   }
    ######
    ######   // --- END: NEW LOGIC ---
    ######
    ######
    ######   // 3. If it's NOT an AJAX request, the code continues exactly as it did before,
    ######   //    generating an HTML page. No changes are needed here.
    ######   if (!$this->httpFactory) {
    ######       // Create a basic response (without PSR-7)
    ######   // ...existing code...



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
        //$content = '<h1>Error: ' . htmlspecialchars($exception->getMessage()) . '</h1>';
        //$content .= '<p>Uncaught exception: ' . get_class($exception) . '</p>';
        $content = '';

        // Special handling for configuration exceptions
        if ($exception instanceof \Core\Exceptions\ConfigurationException) {
            $content .= '<div style="background-color: #fff3cd; padding: 15px; ';
            $content .= 'border: 1px solid #ffeeba; margin: 10px 0;">';
            $content .= '<h3>Configuration Issue</h3>';
            $content .= '<p><strong>Config File:</strong> ' . htmlspecialchars($exception->getConfigFile()
            ?? 'Unknown') . '</p>';
            $content .= '<p><strong>Entity Type:</strong> ' . htmlspecialchars($exception->getEntityType()
            ?? 'Unknown') . '</p>';
            if ($exception->getSuggestion()) {
                $content .= '<h4>How to Fix:</h4>';
                $content .= '<p>' . htmlspecialchars($exception->getSuggestion()) . '</p>';
            }
            $content .= '</div>';
        }


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
