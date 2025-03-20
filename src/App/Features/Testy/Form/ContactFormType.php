<?php

namespace App\Features\Testy\Form;

use Core\Form\FormTypeInterface;
use Core\Form\FieldRegistryInterface;
use App\Helpers\DebugRt as Debug;

/**
 * Simple contact form type without dependencies
 */
class ContactFormType implements FormTypeInterface
{
    private FieldRegistryInterface $fieldRegistry;
    private array $config;

    public function __construct(FieldRegistryInterface $fieldRegistry, array $config = [])
    {
        $this->fieldRegistry = $fieldRegistry;
        $this->config = $config;
    }

    /**
     * Set the form configuration
     *
     * @param array $config The form configuration
     * @return self
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get the current form configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }





    public function getFields(): array
    {
        $fields = [];

        // Use defaults ONLY if config is completely empty
        if (empty($this->config) || empty($this->config['fields'])) {
            $this->config = ['fields' => [
                'name' => [],
                'email' => [],
                'subject' => [],
                'message' => []
            ]];
        }

        // Process fields from config
        if (!empty($this->config['fields'])) {
            foreach ($this->config['fields'] as $fieldName => $overrides) {
                // Get field definition from registry
                $field = $this->fieldRegistry->get($fieldName);

                if (!$field) {
                    throw new \InvalidArgumentException("Field '$fieldName' not found in registry");
                }

                // Apply overrides with deep merge
                foreach ($overrides as $key => $value) {
                    if (is_array($value) && isset($field[$key]) && is_array($field[$key])) {
                        $field[$key] = array_merge($field[$key], $value);
                    } else {
                        $field[$key] = $value;
                    }
                }

                $fields[$fieldName] = $field;
            }
        }

        return $fields;
    }
}
