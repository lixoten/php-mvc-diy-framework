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
     * @var string
     */
    public readonly string $pageKey;

    /**
     * @var string
     */
    public readonly string $entityName;

    /**
     * @var string
     */
    public readonly string $ownerForeignKey;

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
     * Enum for list route (nullable).
     *
     * @var \App\Enums\Url|null
     */
    public readonly ?Url $listUrlEnum;

    /**
     * Enum for create route (nullable).
     *
     * @var \App\Enums\Url|null
     */
    public readonly ?Url $createUrlEnum;

    /**
     * Enum for view route (nullable).
     *
     * @var \App\Enums\Url|null
     */
    public readonly ?Url $viewUrlEnum;

    /**
     * Enum for delete route (nullable).
     *
     * @var \App\Enums\Url|null
     */
    public readonly ?Url $deleteUrlEnum; // Add this

    /**
     * Enum for delete confirmation route (nullable).
     *
     * @var \App\Enums\Url|null
     */
    public readonly ?Url $deleteConfirmUrlEnum; // Add this





    /**
     * @var string|null
     */
    public readonly ?string $redirectAfterSave;

    /**
     * @var string|null
     */
    public readonly ?string $redirectAfterAdd;



    /**
     * @param string $pageKey
     * @param string $entityName
     * @param string $ownerForeignKey
     * @param Url $baseUrlEnum
     * @param Url|null $editUrlEnum
     * @param Url|null $listUrlEnum
     * @param Url|null $createUrlEnum
     * @param Url|null $viewUrlEnum
     * @param Url|null $deleteUrlEnum
     * @param Url|null $deleteConfirmUrlEnum
     * @param string|null $redirectAfterSave
     * @param string|null $redirectAfterAdd
     */
    public function __construct(
        string $pageKey,
        string $entityName,
        string $ownerForeignKey,
        Url $baseUrlEnum,
        ?Url $editUrlEnum,
        ?Url $listUrlEnum,
        ?Url $createUrlEnum = null,
        ?Url $viewUrlEnum = null,
        ?Url $deleteUrlEnum = null, // Make nullable with default
        ?Url $deleteConfirmUrlEnum = null, // Make nullable with default
        ?string $redirectAfterSave = null,
        ?string $redirectAfterAdd = null,
    ) {
        $this->pageKey   = $pageKey;
        $this->entityName = $entityName;
        $this->ownerForeignKey = $ownerForeignKey;
        $this->baseUrlEnum = $baseUrlEnum;
        $this->editUrlEnum = $editUrlEnum;
        $this->listUrlEnum = $listUrlEnum;
        $this->createUrlEnum = $createUrlEnum;
        $this->viewUrlEnum = $viewUrlEnum;
        $this->deleteUrlEnum = $deleteUrlEnum;
        $this->deleteConfirmUrlEnum = $deleteConfirmUrlEnum;
        $this->redirectAfterSave = $redirectAfterSave;
        $this->redirectAfterAdd = $redirectAfterAdd;

    }
}
