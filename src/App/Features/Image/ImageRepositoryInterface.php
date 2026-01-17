<?php

declare(strict_types=1);

namespace App\Features\Image;

use Core\Repository\BaseRepositoryInterface;

// use App\Entities\Image;

/**
 * Generated File - Date: 2025-10-30 20:01
 * interface for Image.
 */
interface ImageRepositoryInterface extends BaseRepositoryInterface
{
        //public function create(object $image): object;
    /**
     * Finds an Image entity by its unique filename (hash).
     *
     * @param string $filename The unique filename (hash) of the image.
     * @return Image|null The Image entity or null if not found.
     */
    public function findByFilename(string $filename): ?Image;

}
