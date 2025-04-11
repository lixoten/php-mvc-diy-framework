<?php

declare(strict_types=1);

namespace App\Features\Auth;

use App\Enums\FlashMessageType;
use App\Enums\UserStatus;
use App\Helpers\DebugRt;
use App\Repository\UserRepositoryInterface;
use App\Services\Email\EmailNotificationService;
use App\Services\Interfaces\FlashMessageServiceInterface;
use Core\Controller;
use Core\Http\HttpFactory;
use Core\View;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Email verification controller
 */
class EmailVerificationController extends Controller
{
    private UserRepositoryInterface $userRepository;
    private EmailNotificationService $emailNotificationService;
    private LoggerInterface $logger;

    /**
     * Constructor with dependencies
     */
    public function __construct(
        array $route_params,
        FlashMessageServiceInterface $flash,
        View $view,
        HttpFactory $httpFactory,
        ContainerInterface $container,
        UserRepositoryInterface $userRepository,
        EmailNotificationService $emailNotificationService,
        LoggerInterface $logger
    ) {
        parent::__construct($route_params, $flash, $view, $httpFactory, $container);
        $this->userRepository = $userRepository;
        $this->emailNotificationService = $emailNotificationService;
        $this->logger = $logger;
    }

    /**
     * Show verification pending page
     */
    public function pendingAction(): ResponseInterface
    {
        return $this->view(AuthConst::VIEW_AUTH_VERIFICATION_PENDING, [
            'title' => 'Email Verification Pending'
        ]);
    }

    // TODO - We nned phpunitTests
    /**
     * Verify email with token
     */
    public function verifyAction(ServerRequestInterface $request): ResponseInterface
    {
        // Debug::p(111);
        $token = $request->getQueryParams()['token'] ?? '';

        if (empty($token)) {
            $this->flash->add('Invalid verification token.', FlashMessageType::Error);
            return $this->redirect('/login');
        }

        // Find user with this token
        $user = $this->userRepository->findByActivationToken($token);

        if (!$user) {
            $this->flash->add('Invalid verification token or user not found.', FlashMessageType::Error);
            $response = $this->view(AuthConst::VIEW_AUTH_VERIFICATION_ERROR, [
                'title' => 'Verification Failed',
                'error' => 'Invalid token'
            ]);
            return $response->withStatus(422); // Unprocessable Entity
        }

        // CHECK IF TOKEN IS EXPIRED
        if ($user->isActivationTokenExpired()) {
            $this->flash->add('Your verification link has expired. Please request a new one.', FlashMessageType::Error);
            return $this->view(AuthConst::VIEW_AUTH_VERIFICATION_ERROR, [
                'title' => 'Verification Failed',
                'error' => 'Expired token'
            ]);
        }

        // CHECK IF USER IS ALREADY VERIFIED
        if ($user->getStatus() === UserStatus::ACTIVE) {
            $this->flash->add('Your account is already verified.', FlashMessageType::Info);
            return $this->redirect('/login');
        }

        // Activate the user
        $user->setStatus(UserStatus::ACTIVE);
        $user->setActivationToken(null); // Clear the token
        $this->userRepository->update($user);

        $this->logger->info('Verification successful', [
            'user_id' => $user->getUserId(),
            'username' => $user->getUsername(),
            'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
        ]);
        //DebugRt::p(111);
        // Set success message
        $this->flash->add('Your email has been verified. You can now log in.', FlashMessageType::Success);

        return $this->view(AuthConst::VIEW_AUTH_VERIFICATION_SUCCESS, [
            'title' => 'Email Verified',
            'username' => $user->getUsername()
        ]);
    }

    /**
     * Resend verification email
     */
    public function resendAction(ServerRequestInterface $request): ResponseInterface
    {
        // Get CSRF token
        $csrfToken = $request->getAttribute('csrf')->generate();

        // Check if this is a form submission
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $email = $data['email'] ?? '';

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->flash->add('Please enter a valid email address.', FlashMessageType::Error);

                // Create response with 422 status for failed validation
                $response = $this->view(AuthConst::VIEW_AUTH_VERIFICATION_RESEND, [
                    'title' => 'Resend Verification Email',
                    'email' => $email,
                    'csrf_token' => $csrfToken
                ]);

                return $response->withStatus(422);
            }

            // Find user by email
            $user = $this->userRepository->findByEmail($email);

            // If no user found or user already active, don't reveal this for security
            if (!$user || $user->getStatus() !== UserStatus::PENDING) {
                // Always show success to prevent email enumeration
                $this->flash->add(
                    'If your email exists in our system and requires verification, ' .
                    'a new verification link has been sent.',
                    FlashMessageType::Success
                );
                return $this->redirect('/login');
            }

            // // Generate new token
            // With this single line that uses the proper User entity method:
            $token = $user->generateActivationToken(24); // Generate token with 24 hour expiry

            // Save updated user
            $this->userRepository->update($user);

            // Get email service
            $emailService = $this->container->get('App\Services\Interfaces\EmailServiceInterface');

            // // Send verification email
            // $verificationUrl = (string)$request->getUri()->withPath('/verify-email')->withQuery("token=$token");
            // $emailData = [
            //     'username' => $user->getUsername(),
            //     'verificationUrl' => $verificationUrl,
            //     'expiryHours' => 24,
            //     'siteName' => 'MVCLixo'
            // ];

            // $emailService->sendTemplate(
            //     // $user->getEmail(), // TODO - Need to put it back, just for testing
            //     'lixoten@gmail.com', // TODO // Important!!!
            //     'Verify Your Email Address',
            //     'Auth/verification_email',
            //     $emailData
            // );

            // Send verification email
            $this->emailNotificationService->sendVerificationEmail($user, $token, $request->getUri());

            $this->flash->add(
                'A new verification email has been sent. Please check your inbox.',
                FlashMessageType::Success
            );
            return $this->redirect('/verify-email/pending');
        }

        // Show the resend form
        return $this->view(AuthConst::VIEW_AUTH_VERIFICATION_RESEND, [
            'title' => 'Resend Verification Email',
            'csrf_token' => $csrfToken
        ]);
    }
}
