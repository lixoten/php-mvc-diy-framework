<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * Service responsible for resolving and providing absolute paths to key directories
 * and files within the application structure.
 *
 * This centralizes all path definitions, making the application more robust
 * to structural changes and reducing hardcoded paths across various components.
 */
class PathResolverService
{
    private string $projectRoot;

    /**
     * @param string $projectRoot The absolute path to the project's root directory.
     */
    public function __construct(string $projectRoot)
    {
        $this->projectRoot = rtrim($projectRoot, '/') . '/';
    }

    /**
     * Get the absolute path to the 'src' directory.
     *
     * @return string
     */
    public function getSrcPath(): string
    {
        return $this->projectRoot . 'src/';
    }

    /**
     * Get the absolute path to the 'public_html' directory.
     *
     * @return string
     */
    public function getPublicHtmlPath(): string
    {
        return $this->projectRoot . 'public_html/';
    }

    /**
     * Get the absolute path to the base 'App' directory.
     *
     * @return string
     */
    public function getAppPath(): string
    {
        return $this->getSrcPath() . 'App/';
    }

    /**
     * Get the absolute path to the 'App/Entities' directory.
     *
     * @return string
     */
    public function getAppEntitiesPath(): string
    {
        return $this->getAppPath() . 'Entities/';
    }

    /**
     * Get the absolute path to the 'App/Repository' directory.
     *
     * @return string
     */
    public function getAppRepositoryPath(): string
    {
        return $this->getAppPath() . 'Repository/';
    }


//////////////////////////////////////////
//////////////////////////////////////////


    public function getAppFeatureRepositoryPath(string $entityName): string
    {
        return $this->getAppFeaturePath($entityName);
    }

    public function getAppFeatureEntityPath(string $entityName): string
    {
        return $this->getAppFeaturePath($entityName);
    }

//////////////////////////////////////////
//////////////////////////////////////////


    /**
     * Get the absolute path to the base 'Generated' staging directory.
     *
     * @return string
     */
    public function getGeneratedBasePath(): string
    {
        return $this->getSrcPath() . 'Generated/';
    }

    /**
     * Get the absolute path to an entity's specific 'Generated' staging directory.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getGeneratedEntityPath(string $entityName): string
    {
        return $this->getGeneratedBasePath() . $entityName . '/';
    }

    /**
     * Get the absolute path to the archive directory for generated entity files.
     *
     * @param string $entityName The name of the entity.
     * @return string The full path to the generated entity archive directory.
     */
    public function getGeneratedEntityArchivePath(string $entityName): string
    {
        return $this->getGeneratedEntityPath($entityName) . "Archive/";
    }



    /**
     * Get the absolute path to the 'Database/Migrations' directory.
     *
     * @return string
     */
    public function getDatabaseMigrationsPath(): string
    {
        return $this->getSrcPath() . 'Database/Migrations/';
    }

    /**
     * Get the absolute path to the 'Database/Seeders' directory.
     *
     * @return string
     */
    public function getDatabaseSeedersPath(): string
    {
        return $this->getSrcPath() . 'Database/Seeders/';
    }

    // --- Feature-Specific Config Paths ---

    /**
     * Get the absolute path to an entity's feature base directory.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getAppFeaturePath(string $entityName): string
    {
        return $this->getAppPath() . 'Features/' . $entityName . '/';
    }

    /**
     * Get the absolute path to an entity's feature Config directory.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getAppFeatureConfigPath(string $entityName): string
    {
        return $this->getAppFeaturePath($entityName) . 'Config/';
    }

    /**
     * Get the absolute path to an entity's feature schema config file.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getAppFeatureConfigSchemaFilePath(string $entityName): string
    {
        return $this->getAppFeatureConfigPath($entityName) . strtolower($entityName) . '_schema' . '.php';
    }

    /**
     * Get the absolute path to an entity's feature field render config file.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getAppFeatureConfigFieldRenderFilePath2(string $entityName): string
    {
        return $this->getAppFeatureConfigPath($entityName) . strtolower($entityName) . "_fields" . '.php';
    }

    /**
     * Get the absolute path to an entity's feature field render config file.
     *
     * param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getAppFeatureConfigFieldRenderFilePath(string $type, string $pageName): string
    {
        $w = explode('_', $pageName);
        $entityName = $w[0];
        $action     = $w[1];

        $file = '';
        if ($type === 'local') {
            $file .= "{$entityName}_{$action}";
        } elseif ($type === 'entity') {
            $file .= $entityName;
        } elseif ($type === 'base') {
            $file .= 'base';
        } else {
            $file .= $entityName;
        }
        $rrr = $this->getAppFeatureConfigPath($entityName) .  $file . '_fields' . '.php';
        return $rrr;
    }




    /**
     *
     * Get the absolute path to an entity's feature view render edit config file.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getAppFeatureConfigViewRenderEditFilePath(string $entityName): string
    {
        return $this->getAppFeatureConfigPath($entityName) . 'view_' . strtolower($entityName) . '_edit.php';
    }

    /**
     * Get the absolute path to an entity's feature view render list config file.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getAppFeatureConfigViewRenderListFilePath(string $entityName): string
    {
        return $this->getAppFeatureConfigPath($entityName) . 'view_' . strtolower($entityName) . '_list.php';
    }

    // --- Global Config Paths (for fallback) ---

    /**
     * Get the absolute path to the global 'Config/schema' directory.
     *
     * @return string
     */
    public function getGlobalConfigSchemaPath(): string
    {
        return $this->getSrcPath() . 'Config/schema/';
    }

    /**
     * Get the absolute path to a global schema config file.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getGlobalConfigSchemaFilePath(string $entityName): string
    {
        return $this->getGlobalConfigSchemaPath() . strtolower($entityName) . '.php';
    }

    /**
     * Get the absolute path to the global 'Config/list_fields' directory.
     *
     * @return string
     */
    public function getGlobalConfigListFieldsPath(): string
    {
        return $this->getSrcPath() . 'Config/list_fields/';
    }

    /**
     * Get the absolute path to a global list fields config file.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getGlobalConfigListFieldsFilePath(string $entityName): string
    {
        return $this->getGlobalConfigListFieldsPath() . strtolower($entityName) . '.php';
    }

    /**
     * Get the absolute path to the global 'Config/view_options' directory.
     *
     * @return string
     */
    public function getGlobalConfigViewOptionsPath(): string
    {
        return $this->getSrcPath() . 'Config/view_options/';
    }

    /**
     * Get the absolute path to a global view options edit config file.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getGlobalConfigViewOptionsEditFilePath(string $entityName): string
    {
        return $this->getGlobalConfigViewOptionsPath() . strtolower($entityName) . '_edit.php';
    }

    /**
     * Get the absolute path to a global view options list config file.
     *
     * @param string $entityName The name of the entity (e.g., 'Testy').
     * @return string
     */
    public function getGlobalConfigViewOptionsListFilePath(string $entityName): string
    {
        return $this->getGlobalConfigViewOptionsPath() . strtolower($entityName) . '_list.php';
    }
}
