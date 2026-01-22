<?php

declare(strict_types=1);

namespace Core\Services;

// filepath: src/Core/Services/SlugGeneratorServiceInterface.php
interface SlugGeneratorServiceInterface
{
    /**
     * Generates a basic URL-friendly slug from the given text.
     * Does not guarantee uniqueness.
     *
     * @param string $text The input text (e.g., a title).
     * @return string The generated slug.
     */
    public function generateSlug(string $text): string;

    /**
     * Generates a unique URL-friendly slug from a base text.
     * Implementations should handle checking for uniqueness (e.g., by appending numbers).
     *
     * @param string $baseText The base text to generate the slug from.
     * @param callable $isSlugUniqueCallback A callback function that takes a slug string
     *                                       and returns true if the slug is unique, false otherwise.
     * @return string A unique slug.
     */
    public function generateUniqueSlug(string $baseText, callable $isSlugUniqueCallback): string;
}
