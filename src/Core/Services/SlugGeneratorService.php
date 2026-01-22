<?php

declare(strict_types=1);

namespace Core\Services;

use Psr\Log\LoggerInterface;

// filepath: src/Core/Services/SlugGeneratorService.php
class SlugGeneratorService implements SlugGeneratorServiceInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Generates a basic URL-friendly slug from the given text.
     * Does not guarantee uniqueness.
     *
     * @param string $text The input text (e.g., a title).
     * @return string The generated slug.
     */
    public function generateSlug(string $text): string
    {
        $text = strtolower($text); // Convert to lowercase
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text); // Remove non-alphanumeric chars except spaces/hyphens
        $text = preg_replace('/[\s-]+/', '-', $text); // Replace spaces and multiple hyphens with a single hyphen
        $text = trim($text, '-'); // Trim hyphens from start/end
        return $text;
    }

    /**
     * Generates a unique URL-friendly slug from a base text.
     *
     * @param string $baseText The base text to generate the slug from (already potentially slugified).
     * @param callable $isSlugUniqueCallback A callback function that takes a slug string
     *                                       and returns true if the slug is unique, false otherwise.
     * @return string A unique slug.
     */
    public function generateUniqueSlug(string $baseText, callable $isSlugUniqueCallback): string
    {
        $slug = $this->generateSlug($baseText); // Ensure the baseText itself is slugified
        $counter = 1;
        $uniqueSlug = $slug;

        // Keep trying slugs with appended numbers until a unique one is found
        while (!$isSlugUniqueCallback($uniqueSlug)) {
            $counter++;
            $uniqueSlug = $slug . '-' . $counter;
            // Prevent excessively long slugs if uniqueness cannot be found
            if (strlen($uniqueSlug) > 255) { // Assuming a max slug length, adjust as per your DB schema
                $this->logger->warning('Could not generate unique slug within reasonable length, resorting to truncation.', ['baseSlug' => $slug, 'counter' => $counter]);
                $uniqueSlug = substr($uniqueSlug, 0, 250); // Truncate to fit and try one last time
                if (!$isSlugUniqueCallback($uniqueSlug)) {
                    $this->logger->error('Failed to generate a truly unique slug even after truncation.', ['finalSlugAttempt' => $uniqueSlug]);
                    break; // Break to prevent infinite loop
                }
            }
        }

        return $uniqueSlug;
    }
}