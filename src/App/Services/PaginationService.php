<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Url;

class PaginationService
{
    /**
     * Get pagination data for rendering
     *
     * @param Url $baseUrl The base URL enum (e.g., Url::CORE_TESTY_LIST)
     * @param int $currentPage The current page number
     * @param int $totalPages The total number of pages
     * @param array<string, mixed> $urlParams Additional query parameters to include in the pagination links.
     * @return array<string, mixed> Structured pagination data.
     */
    public function getPaginationData(
        Url $baseUrl,
        int $currentPage,
        int $totalPages,
        array $urlParams = []
    ): array {
        if ($totalPages <= 1) {
            return [
                'pages' => [],
                'showPagination' => false,
                'baseUrlEnum' => $baseUrl,
                'urlParams' => $urlParams
            ];
        }

        $pages = [];

        // Generate page links
        for ($i = 1; $i <= $totalPages; $i++) {
            $pages[] = [
                'number' => $i,
                'href' => $baseUrl->paginationUrl($i, $urlParams),
                'text' => (string)$i,
                'active' => ($i === $currentPage),
                'disabled' => false
            ];
        }

        // Previous link
        $prevLink = null;
        if ($currentPage > 1) {
            $prevLink = [
                'url' => $baseUrl,
                'href' => $baseUrl->paginationUrl($currentPage - 1, $urlParams),
                'text' => 'Previous',
                'active' => false,
                'disabled' => false
            ];
        }

        // Next link
        $nextLink = null;
        if ($currentPage < $totalPages) {
            $nextLink = [
                'url' => $baseUrl,
                'href' => $baseUrl->paginationUrl($currentPage + 1, $urlParams),
                'text' => 'Next',
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
            'hasNext' => $currentPage < $totalPages,
            'baseUrlEnum' => $baseUrl, // REF: Provide the Url enum object at top level
            'urlParams' => $urlParams, // REF: Provide the original URL parameters at top level
        ];
    }

    /**
     * Get pagination with window (only show certain pages around current)
     *
     * @param Url $baseUrl The base URL enum (e.g., Url::CORE_TESTY_LIST)
     * @param int $currentPage The current page number
     * @param int $totalPages The total number of pages
     * @param int $window The number of pages to show around the current page.
     * @param array<string, mixed> $urlParams Additional query parameters to include in the pagination links.
     * @return array<string, mixed> Structured pagination data with windowing.
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

         // Explicitly provide first and last page data if they are outside the window
        $data['showFirstPage'] = $start > 1;
        if ($data['showFirstPage']) {
            $data['firstPageLink'] = [
                'href' => $baseUrl->paginationUrl(1, $urlParams),
                'text' => '1',
                'number' => 1,
                'active' => false,
                'disabled' => false
            ];
        }

        $data['showLastPage'] = $end < $totalPages;
        if ($data['showLastPage']) {
            $data['lastPageLink'] = [
                'href' => $baseUrl->paginationUrl($totalPages, $urlParams),
                'text' => (string)$totalPages,
                'number' => $totalPages,
                'active' => false,
                'disabled' => false
            ];
        }

        return $data;
    }
}
