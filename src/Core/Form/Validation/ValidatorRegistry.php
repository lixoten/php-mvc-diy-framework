<?php

declare(strict_types=1);

namespace Core\Form\Validation;

use App\Helpers\DebugRt as Debug;

/**
 * Registry for validators
 */
class ValidatorRegistry
{
    /**
     * @var ValidatorInterface[]
     */
    private array $validators = [];

    /**
     * Constructor
     *
     * @param ValidatorInterface[] $defaultValidators Array of default validators
     */
    public function __construct(array $defaultValidators = [])
    {
        foreach ($defaultValidators as $validator) {
            $this->register($validator);
        }
    }


    /**
     * Register a validator
     *
     * @param ValidatorInterface $validator
     * @return self
     */
    public function register(ValidatorInterface $validator): self
    {
        $this->validators[$validator->getName()] = $validator;
        return $this;
    }

    /**
     * Get a validator by name
     *
     * @param string $name
     * @return ValidatorInterface
     * @throws \InvalidArgumentException If validator not found
     */
    public function get(string $name): ValidatorInterface
    {
        if (!isset($this->validators[$name])) {
            throw new \InvalidArgumentException("Validator '$name' not found...");
        }

        return $this->validators[$name];
    }

    /**
     * Check if a validator exists
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->validators[$name]);
    }

    /**
     * Validate a value using a specific validator
     *
     * @param mixed $value Value to validate
     * @param string $validator Validator name
     * @param array $options Validation options
     * @return string|null Error message if validation fails, null if valid
     */
    public function validate($value, string $validator, array $options = []): ?string
    {
        // Merge the full options array with the specific validator options
        $validatorOptions = array_merge(
            $options,
            $options['validators'][$validator] ?? []
        );


        return $this->get($validator)->validate($value, $validatorOptions);
    }
}
