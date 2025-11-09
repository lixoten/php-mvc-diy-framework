<?php

declare(strict_types=1);

namespace Core\I18n;

class LabelProvider
{
    protected array $labels;

    public function __construct(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * Get a label by key, e.g. 'posts.title'
     */
    public function get(string $key): string
    {
        $parts = explode('.', $key);
        $currentValue = $this->labels;
        $foundSpecific = true; // Flag to track if the full specific key was found

        foreach ($parts as $part) {
            if (isset($currentValue[$part])) {
                $currentValue = $currentValue[$part];
            } else {
                $foundSpecific = false; // A part was not found, so the specific key isn't fully resolved
                break; // Stop traversing
            }
        }

        // If the full specific key was found and it's a string, return it
        if ($foundSpecific && is_string($currentValue)) {
            return $currentValue;
        }

        // If the specific key was not fully found, or the resolved value wasn't a string,
        // try to find the last part of the key in the 'common' labels.
        $lastPart = end($parts); // Get the last segment of the original key
        if (isset($this->labels['common'][$lastPart])) {
            return $this->labels['common'][$lastPart];
        }

        // If no specific or common label found, return the key itself
        return $key;
    }
}
