<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Interfaces\PageRegistryInterface;
use Core\Interfaces\ConfigInterface;

// Dynamic-me
/**
 * Manages generic page definitions loaded primarily from configuration.
 */
class PageRegistry implements PageRegistryInterface
{
    /**
     * @var array<string, array> Holds the loaded page configurations.
     */
    private array $pages = [];

    /**
     * Constructor. Loads page data from the configuration service.
     *
     * @param ConfigInterface $configService The application configuration service.
     */
    public function __construct(ConfigInterface $configService)
    {
        $this->loadPagesFromConfig($configService);
        // In the future, you could add loading from DB here as well
        // $this->loadPagesFromDatabase(...);
    }

    /**
     * Loads page definitions from the 'pages' key in the application config.
     */
    private function loadPagesFromConfig(ConfigInterface $configService): void
    {
        //$this->pages = $configService->get('pages', []);
        $this->pages = $configService->get('app.pages', []);
        // You could add validation here to ensure each page has required keys like 'title'
    }

    /**
     * {@inheritdoc}
     */
    public function getAllPages(): array
    {
        return $this->pages;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage(string $slug): ?array
    {
        return $this->pages[$slug] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPage(string $slug): bool
    {
        return isset($this->pages[$slug]);
    }
}
