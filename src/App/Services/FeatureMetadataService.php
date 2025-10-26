<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Url;

/**
 * Holds metadata for a feature, such as URLs, owner keys, and redirect targets.
 *
 * @package App\Services
 */
class FeatureMetadataService
{
    /**
     * Enum for base route (Url enum).
     *
     * @var \App\Enums\Url
     */
    public readonly Url $baseUrlEnum;

    /**
     * Enum for edit route (nullable).
     *
     * @var \App\Enums\Url|null
     */
    public readonly ?Url $editUrlEnum;
    /**
     * @var string
     */
    public readonly string $ownerForeignKey;

    /**
     * @var string|null
     */
    public readonly ?string $redirectAfterSave;

    /**
     * @var string|null
     */
    public readonly ?string $redirectAfterAdd;

    /**
     * @var string
     */
    public readonly string $pageName;

    /**
     * @var string
     */
    public readonly string $entityName;

    /**
     * @param Url $baseUrlEnum
     * @param Url|null $editUrlEnum
     * @param string $ownerForeignKey
     * @param string|null $redirectAfterSave
     * @param string|null $redirectAfterAdd
     * @param string $pageName
     * @param string $entityName
     */
    public function __construct(
        Url $baseUrlEnum,
        ?Url $editUrlEnum,
        string $ownerForeignKey,
        ?string $redirectAfterSave,
        ?string $redirectAfterAdd,
        string $pageName,
        string $entityName
    ) {
        $this->baseUrlEnum = $baseUrlEnum;
        $this->editUrlEnum = $editUrlEnum;
        $this->ownerForeignKey = $ownerForeignKey;
        $this->redirectAfterSave = $redirectAfterSave;
        $this->redirectAfterAdd = $redirectAfterAdd;
        $this->pageName   = $pageName;
        $this->entityName = $entityName;
    }
}
