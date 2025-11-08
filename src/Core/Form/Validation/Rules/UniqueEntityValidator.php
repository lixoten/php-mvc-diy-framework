<?php

declare(strict_types=1);

namespace Core\Form\Validation\Rules;


// use App\Repository\UserRepositoryInterface;
use App\Features\User\UserRepositoryInterface;

/**
 * Validator for checking uniqueness of user attributes like email or username
 */
class UniqueEntityValidator extends AbstractValidator
{
    private UserRepositoryInterface $userRepository;
    private string $fieldType;
    private string $defaultMessage;

    /**
     * Constructor
     *
     * @param UserRepositoryInterface $userRepository Repository for user checks
     * @param string $fieldType Type of field to check ('username' or 'email')
     * @param string $defaultMessage Default error message
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        string $fieldType,
        string $defaultMessage
    ) {
        $this->userRepository = $userRepository;
        $this->fieldType = $fieldType;
        $this->defaultMessage = $defaultMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, array $options = []): ?string
    {
        if ($this->shouldSkipValidation($value)) {
            return null;
        }

        $excludeUserId = $options['exclude_user_id'] ?? null;
        $exists = false;

        // Check if value exists based on field type
        if ($this->fieldType === 'username') {
            $exists = $this->userRepository->usernameExists($value, $excludeUserId);
        } elseif ($this->fieldType === 'email') {
            $exists = $this->userRepository->emailExists($value, $excludeUserId);
        }

        if ($exists) {
            return $this->getErrorMessage($options, $this->defaultMessage);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'unique_' . $this->fieldType;
    }

        protected function getDefaultOptions(): array
    {
        DebugRt::j('1', '', 'boom');
        return [
            'required'  => null,
            'minlength'         => null,
            'maxlength'         => null,
            'pattern'           => null,

            'forbidden_words'    => ['1234', 'password'],
            'require_digit'     => false,
            'require_uppercase' => false,
            'require_lowercase' => false,
            'require_special'   => false,


            'required_message' => null,
            'minlength_message'         => null,
            'maxlength_message'         => null,
            'pattern_message'           => null,

            'forbidden_words_message'   => null,
            'require_digit_message'     => null,
            'require_uppercase_message' => null,
            'require_lowercase_message' => null,
            'invalid_message'           => null,
        ];
    }
}
