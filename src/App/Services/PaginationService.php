<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Url;

class PaginationService
{
    /**
     * Get pagination data for rendering
     */
    public function getPaginationData(
        Url $baseUrl,
        int $currentPage,
        int $totalPages,
        array $urlParams = []
    ): array {
        if ($totalPages <= 1) {
            return ['pages' => [], 'showPagination' => false];
        }

        $pages = [];

        // Generate page links
        for ($i = 1; $i <= $totalPages; $i++) {
            $pageParams = array_merge($urlParams, ['page' => $i]);

            $pages[] = [
                'number' => $i,
                'url' => $baseUrl,
                'href' => $baseUrl->url($pageParams),
                'text' => (string)$i,
                'params' => $pageParams,
                'active' => ($i === $currentPage),
                'disabled' => false
            ];
        }

        // Previous link
        $prevLink = null;
        if ($currentPage > 1) {
            $prevParams = array_merge($urlParams, ['page' => $currentPage - 1]);
            $prevLink = [
                'url' => $baseUrl,
                'href' => $baseUrl->url($prevParams),
                'text' => 'Previous',
                'params' => $prevParams,
                'active' => false,
                'disabled' => false
            ];
        }

        // Next link
        $nextLink = null;
        if ($currentPage < $totalPages) {
            $nextParams = array_merge($urlParams, ['page' => $currentPage + 1]);
            $nextLink = [
                'url' => $baseUrl,
                'href' => $baseUrl->url($nextParams),
                'text' => 'Next',
                'params' => $nextParams,
                'active' => false,
                'disabled' => false
            ];
        }

        return [
            'pages' => $pages,
            'previous' => $prevLink,
            'next' => $nextLink,
            'current' => $currentPage,
            'total' => $totalPages,
            'showPagination' => true,
            'hasPages' => $totalPages > 1,
            'hasPrevious' => $currentPage > 1,
            'hasNext' => $currentPage < $totalPages
        ];
    }

    /**
     * Get pagination with window (only show certain pages around current)
     */
    public function getPaginationDataWithWindow(
        Url $baseUrl,
        int $currentPage,
        int $totalPages,
        int $window = 2,
        array $urlParams = []
    ): array {
        $data = $this->getPaginationData($baseUrl, $currentPage, $totalPages, $urlParams);

        if (!$data['showPagination']) {
            return $data;
        }

        // Calculate window range
        $start = max(1, $currentPage - $window);
        $end = min($totalPages, $currentPage + $window);

        // Filter pages to only show window
        $windowPages = array_filter($data['pages'], function ($page) use ($start, $end) {
            return $page['number'] >= $start && $page['number'] <= $end;
        });

        $data['pages'] = array_values($windowPages);
        $data['window'] = $window;
        $data['windowStart'] = $start;
        $data['windowEnd'] = $end;
        $data['showFirstPage'] = $start > 1;
        $data['showLastPage'] = $end < $totalPages;

        return $data;
    }
}
