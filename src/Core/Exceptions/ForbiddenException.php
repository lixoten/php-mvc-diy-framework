<?php

declare(strict_types=1);

namespace Core\Exceptions;

use App\Helpers\DebugRt as Debug;

class ForbiddenException extends HttpException
{
    private ?string $userId;
    private ?string $requiredPermission;
    private ?array $userRoles;

    public function __construct(
        string $message = null,
        int $code = 403,
        string $userId = null,
        string $requiredPermission = null,
        array $userRoles = null,
        \Throwable $previous = null
    ) {
        $this->userId = $userId;
        $this->requiredPermission = $requiredPermission;
        $this->userRoles = $userRoles;

        // Use default message if none provided
        if ($message === null) {
            $message = "Access forbidden";
        }

        // Add user and permission details if available
        if ($userId !== null && $requiredPermission !== null) {
            $message .= ": User #{$userId} lacks the required '{$requiredPermission}' permission";
        } elseif ($requiredPermission !== null) { // Just add permission info if available
            $message .= ": Missing required permission '{$requiredPermission}'";
        } elseif ($userId !== null) { // Just add user info if available
            $message .= ": User #{$userId} does not have access to this resource";
        }

        parent::__construct($message, $code, $previous);
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getRequiredPermission(): ?string
    {
        return $this->requiredPermission;
    }

    public function getUserRoles(): ?array
    {
        return $this->userRoles;
    }
}
