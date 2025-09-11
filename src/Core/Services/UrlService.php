<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * URL Service Implementation
 */
class UrlService implements UrlServiceInterface
{
    /**
     * URL registry
     */
    private array $urls = [];

    /**
     * View template registry
     */
    private array $views = [];

    /**
     * Labels registry
     */
    private array $labels = [];

    /**
     * Get a URL by its name
     */
    public function url(string $name): string
    {
        if (!isset($this->urls[$name])) {
            throw new \InvalidArgumentException("URL '{$name}' is not registered.");
        }

        return $this->urls[$name];
    }

    /**
     * Get a view template path by its name
     */
    public function view(string $name): string
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        // If no explicit view mapping exists, try to derive from URL (remove leading slash)
        if (isset($this->urls[$name])) {
            return ltrim($this->urls[$name], '/');
        }

        throw new \InvalidArgumentException("View '{$name}' is not registered.");
    }

    /**
     * Get a label by URL name
     */
    public function label(string $name): string
    {
        if (isset($this->labels[$name])) {
            return $this->labels[$name];
        }

        // If no label exists, return a humanized version of the last part of the name
        $parts = explode('.', $name);
        return ucfirst(str_replace('_', ' ', end($parts)));
    }

    /**
     * Register a URL
     */
    public function register(string $name, string $url, ?string $viewPath = null, ?string $label = null): void
    {
        // Auto-add leading slash if not present
        if (!empty($url) && $url[0] !== '/') {
            $url = '/' . $url;
        }

        $this->urls[$name] = $url;

        if ($viewPath !== null) {
            $this->views[$name] = $viewPath;
        }

        if ($label !== null) {
            $this->labels[$name] = $label;
        }
    }

    /**
     * Register multiple URLs at once
     */
    public function registerGroup(string $prefix, array $urls): void
    {
        foreach ($urls as $name => $url) {
            $fullName = $prefix . '.' . $name;

            // Handle different array formats
            if (is_array($url)) {
                if (isset($url[0])) {
                    // Sequential array: [url, label]
                    $urlPath = $url[0];
                    $label = $url[1] ?? null;
                    $viewPath = isset($url[2]) ? $url[2] : null;

                    $this->register($fullName, $urlPath, $viewPath, $label);
                } else {
                    // Associative array: ['url' => x, 'view' => y, 'label' => z]
                    $this->register(
                        $fullName,
                        $url['url'],
                        $url['view'] ?? null,
                        $url['label'] ?? null
                    );
                }
            } else {
                // If URL is a string, it's just the URL
                $this->register($fullName, $url);
            }
        }
    }
}
