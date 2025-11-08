<?php

declare(strict_types=1);

namespace Core\Providers;

use Core\Interfaces\ConfigInterface;
use Core\Services\UrlServiceInterface;

// fixme we might not need this class at all
class UrlServiceProvider
{
    public function register(
        UrlServiceInterface $urlService,
        ConfigInterface $configService
    ) {
        // Load URLs from configuration
        $environment = $_ENV['APP_ENV'] ?? 'development';
        $urlConfig = $configService->get('urls', []);
        foreach ($urlConfig as $groupName => $urls) {
            $urlService->registerGroup($groupName, $urls);
        }
    }
}
