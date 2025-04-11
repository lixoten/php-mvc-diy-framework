<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Email\EmailNotificationService;
use Psr\Http\Message\UriInterface;

class RegistrationService
{
    private UserService $userService;
    private EmailNotificationService $emailNotificationService;

    public function __construct(
        UserService $userService,
        EmailNotificationService $emailNotificationService
    ) {
        $this->userService = $userService;
        $this->emailNotificationService = $emailNotificationService;
    }

    /**
     * Register a new user
     *
     * @param array $data User registration data
     * @param UriInterface|null $requestUri Current request URI for building email URLs
     * @return array ['success' => bool, 'user' => User|null, 'errors' => array]
     */
    public function registerUser(array $data, ?UriInterface $requestUri = null): array
    {
        try {
            // Create the user via UserService
            $user = $this->userService->createUser($data);

            // Send verification email
            $token = $user->getActivationToken();
            $this->emailNotificationService->sendVerificationEmail($user, $token, $requestUri);

            return [
                'success' => true,
                'user' => $user,
                'errors' => []
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'user' => null,
                'errors' => ['_form' => 'An error occurred while creating your account: ' . $e->getMessage()]
            ];
        }
    }
}
