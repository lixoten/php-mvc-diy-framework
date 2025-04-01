<?php

declare(strict_types=1);

namespace App\Features\Admin;

use App\Enums\FlashMessageType;
use App\Repository\LoginAttemptsRepositoryInterface;
use Core\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// TODO, this is a Draft id-1234
// This admin interface will give you complete control over the brute force protection system!
class LoginAttemptsController extends Controller
{
    private LoginAttemptsRepositoryInterface $loginAttemptsRepository;

    public function __construct(LoginAttemptsRepositoryInterface $loginAttemptsRepository)
    {
        $this->loginAttemptsRepository = $loginAttemptsRepository;
    }

    /**
     * List all login attempts with filtering options
     */
    public function indexAction(ServerRequestInterface $request): ResponseInterface
    {
        // Get query parameters for filtering
        $params = $request->getQueryParams();
        $username = $params['username'] ?? null;
        $ip = $params['ip'] ?? null;
        $page = (int)($params['page'] ?? 1);
        $limit = 25;

        // Get attempts based on filters
        $attempts = $this->loginAttemptsRepository->findAll(
            $username,
            $ip,
            $limit,
            ($page - 1) * $limit
        );

        // Count for pagination
        $totalCount = $this->loginAttemptsRepository->countAll($username, $ip);

        return $this->render('Admin/LoginAttempts/index', [
            'attempts' => $attempts,
            'username' => $username,
            'ip' => $ip,
            'page' => $page,
            'totalPages' => ceil($totalCount / $limit)
        ]);
    }

    /**
     * View attempts for a specific user
     */
    public function userAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $username = $args['username'] ?? '';
        $attempts = $this->loginAttemptsRepository->findAttemptsByUser($username);

        return $this->render('Admin/LoginAttempts/user', [
            'attempts' => $attempts,
            'username' => $username
        ]);
    }

    /**
     * Clear attempts for a specific user
     */
    public function clearAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $username = $args['username'] ?? '';

        if ($username) {
            $this->loginAttemptsRepository->clearForUser($username);
            $this->flash->add("Login attempts cleared for $username", FlashMessageType::Success);
        }

        return $this->redirect('/admin/login-attempts');
    }

    /**
     * Clear all expired attempts
     */
    public function cleanupAction(): ResponseInterface
    {
        $deleted = $this->loginAttemptsRepository->deleteExpired(time() - 86400); // 24 hours
        $this->flash->add("Cleaned up $deleted expired login attempts", FlashMessageType::Success);

        return $this->redirect('/admin/login-attempts');
    }
}
