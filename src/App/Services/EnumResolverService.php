<?php

declare(strict_types=1);

namespace App\Services;

// DangerDanger Not used proff of concept needed
// TODO decide if we need this class
class EnumResolverService
{
    /**
     * Finds and returns the enum class for a given entity name.
     */
    public function getFieldEnumForEntity(string $entityName): ?string
    {
        // Example: Convert 'post' to 'PostField'
        $className = ucfirst($entityName) . 'Field';
        $fullClassName = 'App\\Enums\\' . $className;

        if (enum_exists($fullClassName)) {
            return $fullClassName;
        }

        return null;
    }
}


// DangerDanger Not used proff of concept needed
// TODO decide if we need this class, if so place in seperate file
use App\Services\EnumResolverService;

class LabelProviderService
{
    private EnumResolverService $enumResolver;

    public function __construct(EnumResolverService $enumResolver)
    {
        $this->enumResolver = $enumResolver;
    }

    public function getLabel(string $id): string
    {
        // Example ID: 'post.title'
        [$entityName, $fieldName] = explode('.', $id);

        $enumClass = $this->enumResolver->getFieldEnumForEntity($entityName);

        if ($enumClass) {
            // Find the enum case with the matching value
            foreach ($enumClass::cases() as $case) {
                if ($case->value === $fieldName) {
                    // Return the label from the enum's metadata
                    return $case->getMetadata()['label'];
                }
            }
        }

        // Fall back to your existing logic if no enum is found
        return $this->getLabelFromTranslationFile($id);
    }
}
