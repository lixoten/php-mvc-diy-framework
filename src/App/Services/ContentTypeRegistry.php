<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Interfaces\ContentTypeRegistryInterface;
use Core\Interfaces\ConfigInterface; // Use your existing Config service interface
use InvalidArgumentException;

// Dynamic-me 2
/**
 * Manages configurations for different generic content types loaded from config.
 */
class ContentTypeRegistry implements ContentTypeRegistryInterface
{
    /**
     * @var array<string, array> Holds the loaded content type configurations.
     */
    private array $contentTypes = [];

    /**
     * Constructor. Loads content type configurations.
     *
     * @param ConfigInterface $configService The application configuration service.
     */
    public function __construct(ConfigInterface $configService)
    {
        // Load from a dedicated 'content_types' key in your main config,
        // or load a separate config file (e.g., config/content_types.php)
        $this->contentTypes = $configService->get('content_types', []);

        // Optional: Add validation here to ensure each type has required keys
        // (e.g., 'repository', 'entity', 'label', 'fields')
    }

    /**
     * {@inheritdoc}
     */
    public function hasContentType(string $slug): bool
    {
        return isset($this->contentTypes[$slug]);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(string $slug): array
    {
        if (!$this->hasContentType($slug)) {
            throw new InvalidArgumentException("Content type '$slug' is not registered.");
        }
        return $this->contentTypes[$slug];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllContentTypes(): array
    {
        return $this->contentTypes;
    }
}