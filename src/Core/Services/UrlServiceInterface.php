<?php

declare(strict_types=1);

namespace Core\Services;

/**
 * URL Service Interface
 */
interface UrlServiceInterface
{
    /**
     * Get a URL by its name
     */
    public function url(string $name): string;

    /**
     * Get a view template path by its name
     */
    public function view(string $name): string;

    /**
     * Get a label by URL name
     */
    public function label(string $name): string;

    /**
     * Register a URL
     */
    public function register(string $name, string $url, ?string $viewPath = null, ?string $label = null): void;

    /**
     * Register multiple URLs at once
     */
    public function registerGroup(string $prefix, array $urls): void;
}
