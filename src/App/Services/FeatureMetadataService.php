<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Url;

/**
 * A service that holds feature-specific metadata loaded from configuration.
 *
 * This class acts as a structured data object, providing access to essential
 * metadata like URL enums and ownership keys required by controllers.
 */
class FeatureMetadataService
{
    /**
     * @param Url $baseUrlEnum The base URL enum for the feature.
     * @param Url $editUrlEnum The edit URL enum for the feature.
     * @param string $ownerForeignKey The database column name for the owner's foreign key.
     */
    public function __construct(
        public readonly Url $baseUrlEnum,
        public readonly Url $editUrlEnum,
        public readonly string $ownerForeignKey,
        public readonly string $redirectAfterSave,
        public readonly string $redirectAfterAdd,
        // private readonly Url $baseUrlEnum,
        // private readonly Url $editUrlEnum,
        // private readonly string $ownerForeignKey
    ) {
    }

    // /**
    //  * Gets the base URL enum for the feature.
    //  *
    //  * @return Url
    //  */
    // public function getBaseUrlEnum(): Url
    // {
    //     return $this->baseUrlEnum;
    // }

    // /**
    //  * Gets the edit URL enum for the feature.
    //  *
    //  * @return Url
    //  */
    // public function getEditUrlEnum(): Url
    // {
    //     return $this->editUrlEnum;
    // }

    // /**
    //  * Gets the owner's foreign key column name.
    //  *
    //  * @return string
    //  */
    // public function getOwnerForeignKey(): string
    // {
    //     return $this->ownerForeignKey;
    // }
}