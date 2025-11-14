<?php

declare(strict_types=1);

// namespace App\Services;
// namespace App\Services;
namespace App\Features\User;

// use App\Entities\User;
use App\Features\User\User;
// use App\Repository\UserRepositoryInterface;
use App\Features\User\UserRepositoryInterface;
use Core\Security\TokenServiceInterface;
// use Core\Services\DataTransformerService;
use App\Enums\UserStatus;

class UserService
{
    private UserRepositoryInterface $userRepository;
    private TokenServiceInterface $tokenService;
    // private DataTransformerService $dataTransformer;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenServiceInterface $tokenService,
        // DataTransformerService $dataTransformer
    ) {
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
        // $this->dataTransformer = $dataTransformer;
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPasswordHash(password_hash($data['password'], PASSWORD_DEFAULT));
        $user->setRoles(['user']);
        $user->setStatus(UserStatus::PENDING);

        // Generate activation token
        $tokenData = $this->tokenService->generateWithExpiry(24 * 3600); // 24 hours
        $user->setActivationToken($tokenData['token']);
        $user->setActivationTokenExpiry($tokenData['expires_at']);

        // Save user to repository
        return $this->userRepository->create($user);
    }

    /** used
     * Fetch a user by ID
     */
    public function getAllUsersWithFields($listFields, $sortField, $sortDirection, $limit, $offset): array
    {
        $rrr = $this->userRepository->findAllWithFields(
            $listFields,
                [$sortField => $sortDirection],
                $limit,
                $offset
            );
        return $rrr;
    }

    /** used
     * Fetch a user by ID
     */
    public function countAllUsers(): int
    {
        $rrr = $this->userRepository->countAll();
        return $rrr;
    }



    /**
     * Fetch a user by ID
     */
    public function getUserByIdWithFields(int $userId, array $fields): ?array
    {
        return $this->userRepository->findByIdWithFields($userId, $fields);
    }

    /**
     * Fetch a user by ID
     */
    public function getUserById(int $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }

    /**
     * Fetch a user by username
     */
    public function getUserByUsername(string $username): ?User
    {
        return $this->userRepository->findByUsername($username);
    }

    /**
     * Fetch a user by email
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Fetch a user by activation token
     */
    public function getUserByActivationToken(string $token): ?User
    {
        return $this->userRepository->findByActivationToken($token);
    }

    /**
     * Fetch a user by reset token
     */
    public function getUserByResetToken(string $token): ?User
    {
        return $this->userRepository->findByResetToken($token);
    }

    /**
     * Update an existing user
     */
    public function updateUser(User $user): bool
    {
        return $this->userRepository->update($user);
    }

    /**
     * Update an existing user
     */
    public function updateUserWithFields(int $userId, array $data): bool
    {
        return $this->userRepository->updateFields($userId, $data);
        // return $this->userRepository->update($user);
    }


   /**
     * Update an existing user
     */
    public function insertFields(array $data): int
    {
        return $this->userRepository->insertFields($data);
        // return $this->userRepository->update($user);
    }

    /**
     * Delete a user by ID
     */
    public function deleteUser(int $userId): bool
    {
        return $this->userRepository->delete($userId);
    }



    // /**
    //  * Transform user data for database storage
    //  * Normalizes form submission or API input for database persistence
    //  *
    //  * @param array<string, mixed> $data Raw input data
    //  * @param string $pageName Page context (e.g., 'user_edit')
    //  * @return array<string, mixed> Storage-ready data
    //  */
    // public function transformForStorage(array $data, string $pageName = 'user_edit'): array
    // {
    //     return $this->dataTransformer->toStorage($data, $pageName, 'user');
    // }

    // /**
    //  * Transform user data for display
    //  * Converts database/storage format to display-ready format for views, forms, lists
    //  *
    //  * @param array<string, mixed> $userData Raw user data from storage
    //  * @param string $pageName Page context (e.g., 'user_edit', 'user_list', 'user_detail')
    //  * @return array<string, mixed> Display-ready data
    //  */
    // public function transformForDisplay(array $userData, string $pageName = 'user_edit'): array
    // {
    //     return $this->dataTransformer->toDisplay($userData, $pageName, 'user');
    // }
}
