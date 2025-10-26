<?php

declare(strict_types=1);

namespace Core\Scrapfile\Bootstrap;

use Core\Context\CurrentContext;
use Core\Services\ThemeServiceInterface;

/**
 * Bootstrap-specific scrap info renderer service
 *
 * Renders debug/info tables for $scrapInfo using Bootstrap markup
 */
class BootstrapScrapInfoRendererService
{
    public function __construct(
        private ThemeServiceInterface $themeService
    ) {
    }

    /**
     * Render the scrap info table
     *
     * @param array $scrapInfo
     * @return string
     */
    public function renderScrapInfo(array $scrapInfo, CurrentContext $scrap): string
    {
        $tableClass = $this->themeService->getElementClass('table') ?: 'table table-bordered table-striped mb-0';
        $cardClass = $this->themeService->getElementClass('card') ?: 'card mb-4';
        $cardHeaderClass = $this->themeService->getElementClass('card.header') ?: 'card-header bg-primary text-white';
        $cardBodyClass = $this->themeService->getElementClass('card.body') ?: 'card-body';


                        $namespace = $scrap->getNamespaceName();
                        $temp = $namespace !== null ? substr($namespace, 13) : '';
                        $controller = htmlspecialchars($scrapInfo['controller'] ?? '');
                        $action     = htmlspecialchars($scrap->getActionName() ?? '');
                        $routeId    = htmlspecialchars($scrap->getRouteId() ?? '');
                        $routeParms = $scrapInfo['routeParams'];
                        $pageName   = htmlspecialchars($scrapInfo['page_key'] ?? '');
        $rows = '';
        $rows .= <<<HTML
            <tr>
                <th scope="row">NameSpace</th>
                <td>{$temp}</td>
            </tr>
        HTML;
        $rows .= <<<HTML
            <tr>
                <th scope="row">Controller</th>
                <td>{$controller}</td>
            </tr>
        HTML;
        $rows .= <<<HTML
            <tr>
                <th scope="row">action</th>
                <td>{$action}</td>
            </tr>
        HTML;
        $rows .= <<<HTML
            <tr>
                <th scope="row">routeId</th>
                <td>{$routeId}</td>
            </tr>
        HTML;

        $routeParmsHtml = '';
        if ($routeParms !== null && is_array($routeParms)) {
            $routeParmsHtml = htmlspecialchars(print_r($routeParms, true));
        } else {
            $routeParmsHtml = htmlspecialchars((string)$routeParms);
        }

        $rows .= <<<HTML
            <tr>
                <th scope="row">route Parms</th>
                <td><pre>{$routeParmsHtml}</pre></td>
            </tr>
        HTML;

        $rows .= <<<HTML
            <tr>
                <th scope="row">pageName</th>
                <td>{$pageName}</td>
            </tr>
        HTML;

        foreach ($scrapInfo as $key => $value) {
            $displayValue = is_array($value) ? '<pre>' . htmlspecialchars(print_r($value, true)) . '</pre>' : htmlspecialchars((string)$value);
            $rows .= <<<HTML
                <tr>
                    <th scope="row">{$key}</th>
                    <td>{$displayValue}</td>
                </tr>
            HTML;
        }

        return <<<HTML
        <div class="scrap-container {$cardClass} table-responsive">
            <div class="scrap-head {$cardHeaderClass}">
                Debug Info
            </div>
            <div class="scrap-body {$cardBodyClass}">
                <table class="scrap-table {$tableClass}">
                    <tbody>
                        {$rows}
                    </tbody>
                </table>
            </div>
        </div>
        HTML;
    }
}
