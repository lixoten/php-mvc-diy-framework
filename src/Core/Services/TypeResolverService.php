<?php

declare(strict_types=1);

namespace Core\Services;

use Core\Form\FormTypeInterface;
use Core\List\ListTypeInterface;
use Core\Interfaces\ConfigInterface;
use InvalidArgumentException;

/**
 * Service for dynamically resolving FormType and ListType instances by feature name.
 * Uses config-driven mappings and lazy factory instantiation for scalability.
 * Follows SOLID: Open-Closed (extend via config), Single Responsibility (resolution only).
 */
class TypeResolverService
{
    /** @var array<string, FormTypeInterface> Cache for resolved form types. */
    private array $formTypeCache = [];

    /** @var array<string, ListTypeInterface> Cache for resolved list types. */
    private array $listTypeCache = [];

    private ConfigInterface $config;

    /** @var callable Factory closure for lazy instantiation. */
    private $factory;

    /**
     * @param ConfigInterface $config Config service for loading mappings.
     * @param callable $factory Closure to instantiate types (e.g., with dependencies).
     */
    public function __construct(ConfigInterface $config, callable $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * Resolves a FormType by feature name, instantiating lazily via factory.
     *
     * @param string $featureName The feature name (e.g., 'testy').
     * @return FormTypeInterface The resolved form type instance.
     * @throws InvalidArgumentException If the feature is not mapped or instantiation fails.
     */
    public function resolveFormType(string $featureName): FormTypeInterface
    {
        if (isset($this->formTypeCache[$featureName])) {
            return $this->formTypeCache[$featureName];
        }

        $mappings = $this->config->get('type_mappings.forms');
        if (!isset($mappings[$featureName])) {
            throw new InvalidArgumentException("FormType not found for feature: {$featureName}");
        }

        $className = $mappings[$featureName];
        $instance = ($this->factory)($className);
        if (!$instance instanceof FormTypeInterface) {
            throw new InvalidArgumentException("Resolved class {$className} does not implement FormTypeInterface");
        }

        $this->formTypeCache[$featureName] = $instance;
        return $instance;
    }

    /**
     * Resolves a ListType by feature name, instantiating lazily via factory.
     *
     * @param string $featureName The feature name (e.g., 'testy').
     * @return ListTypeInterface The resolved list type instance.
     * @throws InvalidArgumentException If the feature is not mapped or instantiation fails.
     */
    public function resolveListType(string $featureName): ListTypeInterface
    {
        if (isset($this->listTypeCache[$featureName])) {
            return $this->listTypeCache[$featureName];
        }

        $mappings = $this->config->get('type_mappings.lists', []);
        if (!isset($mappings[$featureName])) {
            throw new InvalidArgumentException("ListType not found for feature: {$featureName}");
        }

        $className = $mappings[$featureName];
        $instance = ($this->factory)($className);
        if (!$instance instanceof ListTypeInterface) {
            throw new InvalidArgumentException("Resolved class {$className} does not implement ListTypeInterface");
        }

        $this->listTypeCache[$featureName] = $instance;
        return $instance;
    }
}
