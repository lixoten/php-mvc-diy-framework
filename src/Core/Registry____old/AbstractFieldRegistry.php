<?php

declare(strict_types=1);

namespace Core\Registry;

use Core\I18n\I18nTranslator;
use Core\Interfaces\ConfigInterface;

/**
 * Abstract field registry with inheritance support
 */
abstract class AbstractFieldRegistry implements FieldRegistryInterface
{
    /**
     * @var array<string, array>
     */
    //protected array $fields = [];
    public string $entityType = '';

    //protected I18nTranslator $translator;
    //protected ?FieldRegistryInterface $baseRegistry = null;
    protected ConfigInterface $configService;

    /**
     * Constructor
     */
    // public function __construct(I18nTranslator $translator, ?FieldRegistryInterface $baseRegistry = null)
    public function __construct(
        ConfigInterface $configService,
        //?FieldRegistryInterface $baseRegistry = null
    ) {
        $this->configService = $configService;
        //$this->baseRegistry = $baseRegistry;
        // $this->translator = $translator;
    }


    // public function get(string $fieldName): ?array
    // {
    //     $methodName = 'get' . str_replace('_', '', ucwords($fieldName, '_'));

    //     if (method_exists($this, $methodName)) {
    //         return $this->$methodName();
    //     }

    //     // Check for common fields if no specific method exists
    //     $commonField = $this->getCommonField($fieldName);
    //     if ($commonField) {
    //         return $commonField;
    //     }

    //     // Optionally, you could throw an exception or log a warning here
    //     // throw new InvalidArgumentException("Field definition not found for: " . $fieldName);
    //     return null; // Or return null if not found
    // }
    /**
     * Get field definition by name.
     * Checks for a specific getter method first, then common fields.
     */
    public function get(string $fieldName): ?array
    {
        // 1. Check for field in $fields array
        // This First pass looks at Local-Layer
        // if (isset($this->fields[$fieldName])) {
        //     return $this->fields[$fieldName];
        // }

        // $local = 'local_posts';
        $local = 'local_login';
        //$config = $this->configService->get('list_fields/' . $local); // loads "list_fields/posts.php"
        $localField = $this->configService->get('list_fields/' . $local . "." . $fieldName); // loads "list_fields/posts.php"
        if (isset($localField)) {
            return $localField;
        }

        // 2. Check config for override
        // This Second pass looks at Table-Layer via Config
        // $this->entityType = "posts";
        $entity = $this->entityType; // e.g., 'posts'
        $repoField = $this->configService->get('list_fields/' . $entity . "." . $fieldName); // loads "list_fields/posts.php"
        // $config = $this->configService->get('list_fields_' . $entity); // loads posts.php
        // $config = $this->configService->get('list_fields_' . $entity . ".test2"); // loads posts.php
        // $config = $this->configService->get('list_fields/' . $entity . ".test2"); // loads posts.php
        // $rrrrrrrrrrrrr = $this->configService->getConfigValue('security', 'rate_limits.endpoints.login', []);
        // $serviceConfig = $this->configService->get('security.rate_limits.endpoints.login.limit', []);
        // $serviceConfig = $this->configService->get('security.rate_limits', []);


        // if (isset($config['entities'][$entity]['columns'][$fieldName])) {
        //     return $config['entities'][$entity]['columns'][$fieldName];
        // }
        if (isset($repoField)) {
            return $repoField;
        }


        // 3. Optionally, fallback to baseRegistry
        // if ($this->baseRegistry) {
        //     $config2 = $this->configService->get('list_fields/base'); // loads "list_fields/posts.php"
        //     // $field = $this->baseRegistry->get($fieldName);
        //     // if ($field) {
        //         // return $field;
        //     // }
        //     if (isset($config2[$fieldName])) {
        //         return $config2[$fieldName];
        //     }
        // }
        $baseField = $this->configService->get('list_fields/base' . "." . $fieldName); // loads "list_fields/posts.php"
        if (isset($baseField)) {
            return $baseField;
        }

        // // 3. Fallback to base config (instead of BaseFieldRegistry class)
        // $baseConfig = $this->configService->get('list_fields_base');
        // if (isset($baseConfig[$fieldName])) {
        //     return $baseConfig[$fieldName];
        // }

        // // 2. Fallback to getCommonField
        // // Check for common fields if no specific method exists
        // $commonField = $this->getCommonField($fieldName);
        // if ($commonField) {
        //     return $commonField;
        // }

        // // 3. Optionally, fallback to baseRegistry
        // if ($this->baseRegistry) {
        //     return $this->baseRegistry->get($fieldName);
        // }

        // Optionally, you could throw an exception or log a warning here
        // throw new InvalidArgumentException("Field definition not found for: " . $fieldName);
        return null; // Or return null if not found
    }


    // Specific getter methods (like getTitle, getUsername) should now be
    // primarily in the concrete registry (GenericFieldRegistry) or loaded from config.
}
