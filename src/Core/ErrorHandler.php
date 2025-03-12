<?php

declare(strict_types=1);

namespace Core;

use App\Features\Errors\ErrorsController;
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

class ErrorHandler
{
    private bool $developmentMode;
    private Logger $logger;
    private ?ContainerInterface $container;

    public function __construct(
        bool $developmentMode = false,
        Logger $logger = null,
        ?ContainerInterface $container = null  // Make nullable
    ) {
        $this->developmentMode = $developmentMode;
        $this->logger = $logger;  // No fallback creation
        $this->container = $container;
    }

    //TODO
    //To complete your HTTP exception family, you might also consider adding:
    // BadRequestException (400)
    // MethodNotAllowedException (405)
    // ConflictException (409)
    // InternalServerErrorException (500)

    public function handleExceptionBase(
        Throwable $e,
        int $statusCode,
        array $additionalContext = []
        // string $redirectPath,
        // string $flashId
    ): never {
        // Log the exception
        $context = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ];

        // Merge with any additional context from specialized handlers
        $context = array_merge($context, $additionalContext);

        if ($e instanceof HttpException) {
            $prefix = "HTTP({$statusCode})";
        } elseif ($e instanceof \PDOException) {
            $prefix = "DATABASE";
        } else {
            $prefix = get_class($e);
        }

        $this->logger->error($prefix . ": " . $e->getMessage(), $context);

        //$extraMessage = $this->getExtraMessage($e);

        // $this->flashObj->addMessage(
        //     $e->getMessage(),
        //     sticky: true,
        //     extraMessage: $extraMessage,
        //     flashId: $flashId
        // );
        // $this->returnPageManagerObj->setReturnToPage($redirectPath);


        // Forward to error controller (without redirect)
        ///////////////$errorController = $this->container->get(ErrorsController::class);
        if ($this->container) {
            // Use container if available
            $errorController = $this->container->get(ErrorsController::class);
        } else {
            // Manual fallback if container is null
            $sessionManager = new \Core\Session\SessionManager([
                'name' => 'mvc3_session',
                'secure' => false,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            $flashService = new \App\Services\FlashMessageService($sessionManager);
            $view = new \Core\View(); // Create a View instance
            $errorController = new ErrorsController([], $flashService, $view);
        }


        $errorController->showError(
            $statusCode,
            $e->getMessage(),
            [
            'exception' => $e,
            'file' => $e->getFile(),
            'line' => $e->getLine()
            ]
        );

        exit;


        // DIRECT OUTPUT - NO REDIRECTS!
        http_response_code($statusCode);
        echo '<!DOCTYPE html>';
        echo '<html><head><title>' . $statusCode . ' Error</title>';
        echo '<style>body{font-family:sans-serif;max-width:800px;margin:0 auto;padding:20px}</style>';
        echo '</head><body>';
        echo '<h1>' . $statusCode . ' Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>File: ' . htmlspecialchars($e->getFile()) . ' Line: ' . $e->getLine() . '</p>';
        echo '<p><a href="/">Back to Home</a></p>';
        echo '</body></html>';
        exit;
    }

    public function handleException(Throwable $e): void
    {
        // Dispatch to specialized handlers based on exception type
        if ($e instanceof UnauthenticatedException) {
            $this->handleUnauthenticatedException($e);
            return;
        } elseif ($e instanceof ForbiddenException) {
            $this->handleForbiddenException($e);
            return;
        } elseif ($e instanceof RecordNotFoundException) {
            $this->handleRecordNotFoundException($e);
            return;
        } elseif ($e instanceof PageNotFoundException) {
            $this->handlePageNotFoundException($e);
            return;
        } elseif ($e instanceof BadRequestException) {
            $this->handleBadRequestException($e);
            return;
        // } elseif ($e instanceof DatabaseException) {  // TODO
            // $this->handleDatabaseException($e);
            // return;
        } elseif ($e instanceof InvalidArgumentException) {
            $this->handleInvalidArgumentException($e);
            return;
        }

        // Fall back to generic handling for unrecognized exceptions
        $statusCode = $e->getCode() >= 100 && $e->getCode() < 600 ? $e->getCode() : 500;

        $this->handleExceptionBase(
            $e,
            $statusCode,
            // '/errors/server-error',
            // "02052025-500"
        );
    }

    public function handleUnauthenticatedException(UnauthenticatedException $e): void
    {
        $statusCode = 401;

        $this->handleExceptionBase(
            $e,
            $statusCode,
            // Enhanced logging with authentication details
            [
                'attempted_resource' => $e->getAttemptedResource(),
                'auth_method' => $e->getAuthMethod(),
                'reason_code' => $e->getReasonCode()
            ]
        );
    }


    public function handleForbiddenException(ForbiddenException $e): void
    {
        $statusCode = 403;

        // // Enhanced logging with permission details
        // $this->logger->error("Access forbidden: " . $e->getMessage(), [
        //     'user_id' => $e->getUserId(),
        //     'required_permission' => $e->getRequiredPermission(),
        //     'user_roles' => $e->getUserRoles() ? implode(', ', $e->getUserRoles()) : null
        // ]);

        $this->handleExceptionBase(
            $e,
            $statusCode,
            // Enhanced logging with authentication details
            [
                'user_id' => $e->getUserId(),
                'required_permission' => $e->getRequiredPermission(),
                'user_roles' => $e->getUserRoles() ? implode(', ', $e->getUserRoles()) : null
            ]
        );
    }

    public function handleRecordNotFoundException(RecordNotFoundException $e): void
    {
        $statusCode = 404;

        // // Log with entity information
        // $this->logger->error("Not found: " . $e->getMessage(), [
        //     'entity_type' => $e->getEntityType(),
        //     'entity_id' => $e->getEntityId()
        // ]);

        $this->handleExceptionBase(
            $e,
            $statusCode,
            // Enhanced logging with RecordNotFound details
            [
                'entity_type' => $e->getEntityType(),
                'entity_id' => $e->getEntityId()
            ]
            // '/errors/not-found',
            // "02052025-404"
        );
    }

    public function handlePageNotFoundException(PageNotFoundException $e): void
    {
        $statusCode = 404;

        // // Log with entity information
        // $this->logger->error("Not found: " . $e->getMessage(), [
        //     'entity_type' => $e->getEntityType(),
        //     'entity_id' => $e->getEntityId()
        // ]);

        $this->handleExceptionBase(
            $e,
            $statusCode,
            // Enhanced logging with PageNotFound details
            [
                'requestedRoute' => $e->getRequestedRoute(),
            ]
        );
    }

    public function handleBadRequestException(BadRequestException $e): void
    {
        $statusCode = 400;

        // // Log with entity information
        // $this->logger->error("Not found: " . $e->getMessage(), [
        //     'entity_type' => $e->getEntityType(),
        //     'entity_id' => $e->getEntityId()
        // ]);
        $this->handleExceptionBase(
            $e,
            $statusCode,
        );
    }


    // public function handleDatabaseException(DatabaseException $e): void  // TODO
    // {
    //     // Check if method exists or use default status code
    //     $statusCode = method_exists($e, 'getHttpStatusCode') ? $e->getHttpStatusCode() : 500;

    //     $this->handleExceptionBase(
    //         $e,
    //         $statusCode,
    //         // '/errors/service-unavailable',
    //         // "02052025-503"
    //     );
    // }

    public function handleInvalidArgumentException(InvalidArgumentException $e): void
    {
        // Get status code from exception or default based on exception code
        $statusCode = 400; // InvalidArgumentException always maps to 400

        $redirectPath = match ($e->getCode()) {
            22, 99 => '/errors/invalid-argument',
            404 => '/errors/not-found',
            400 => '/errors/bad-request',
            default => '/errors/server-error'
        };

        // Use common base handler for consistency
        $this->handleExceptionBase(
            $e,
            $statusCode,
            // $redirectPath,
            // "02052025-" . ($e->getCode() ?: 500)
        );
    }

    // public function handleActivationTokenGenerationException(ActivationTokenGenerationException $e): void
    // {
    //     // Check if method exists or use default status code
    //     $statusCode = method_exists($e, 'getHttpStatusCode') ? $e->getHttpStatusCode() : 500;

    //     $this->handleExceptionBase(
    //         $e,
    //         $statusCode,
    //         // '/errors/service-unavailable',
    //         // "02052025-503"
    //     );
    // }

    // In your exception handler methods, add this:
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
}
