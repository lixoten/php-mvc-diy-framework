<?php

declare(strict_types=1);

/**
 * @var array $data
 * @var array $scrapInfo
 * @var \Core\Context\CurrentContext $scrap
 * @var \Core\Scrap\BootstrapScrapInfoRendererService $scrapInfoRenderer
 */

// Get the renderer from the container
global $container;
$scrapInfoRenderer = $container->get(\Core\Scrapfile\Bootstrap\BootstrapScrapInfoRendererService::class);

// Render the scrap info table
echo $scrapInfoRenderer->renderScrapInfo($scrapInfo, $scrap);

// $rr = $this->scrap();
// $rr = $scrap();
// $rr = 33;
