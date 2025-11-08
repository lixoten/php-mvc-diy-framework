<?php

declare(strict_types=1);

namespace Core\Console\Generators;

use Core\Exceptions\SchemaDefinitionException;

/**
 * Generates all files for a feature (entity, repository, migration, seeder, controller, form type, list type).
 */
class FeatureGenerator
{
    protected EntityGenerator $entityGenerator;
    protected RepositoryGenerator $repositoryGenerator;
    protected MigrationGenerator $migrationGenerator;
    protected SeederGenerator $seederGenerator;
    // Optionally add ControllerGenerator, FormTypeGenerator, ListTypeGenerator, etc.

    /**
     * @param EntityGenerator $entityGenerator
     * @param RepositoryGenerator $repositoryGenerator
     * @param MigrationGenerator $migrationGenerator
     * @param SeederGenerator $seederGenerator
     */
    public function __construct(
        EntityGenerator $entityGenerator,
        RepositoryGenerator $repositoryGenerator,
        MigrationGenerator $migrationGenerator,
        SeederGenerator $seederGenerator
        // Add more generators as needed
    ) {
        $this->entityGenerator = $entityGenerator;
        $this->repositoryGenerator = $repositoryGenerator;
        $this->migrationGenerator = $migrationGenerator;
        $this->seederGenerator = $seederGenerator;
        // Add more generators as needed
    }

    /**
     * Generate all files for a feature based on the schema.
     *
     * @param array<string, mixed> $schema
     * @return array<string> List of generated file paths
     * @throws SchemaDefinitionException
     */
    public function generate(array $schema): array
    {
        $files = [];

        // Generate Entity
        $files[] = $this->entityGenerator->generate($schema);

        // Generate Repository (returns ['interface' => ..., 'implementation' => ...])
        $repoFiles = $this->repositoryGenerator->generate($schema);
        if (is_array($repoFiles)) {
            $files = array_merge($files, array_values($repoFiles));
        } else {
            $files[] = $repoFiles;
        }

        // Generate Migration
        $files[] = $this->migrationGenerator->generate($schema);

        // Generate Seeder
        $files[] = $this->seederGenerator->generate($schema);

        // Optionally: Generate Controller, FormType, ListType, etc.
        // $files[] = $this->controllerGenerator->generate($schema);
        // $files[] = $this->formTypeGenerator->generate($schema);
        // $files[] = $this->listTypeGenerator->generate($schema);

        return $files;
    }
}
