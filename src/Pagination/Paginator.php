<?php

namespace WpPluginCore\Pagination;

class Paginator
{
    public readonly array $items;
    public readonly int $total;
    public readonly int $perPage;
    public readonly int $currentPage;
    public readonly int $lastPage;
    public readonly string $baseUrl;
    public readonly string $pageQueryVar;

    public function __construct(
        array $items,
        int $total,
        int $perPage,
        int $currentPage,
        string $baseUrl,
        string $pageQueryVar = 'page',
    ) {
        $perPage = max(1, $perPage);

        $this->items        = $items;
        $this->total        = max(0, $total);
        $this->perPage      = $perPage;
        $this->currentPage  = max(1, $currentPage);
        $this->lastPage     = max(1, (int) ceil($this->total / $perPage));
        $this->baseUrl      = $baseUrl;
        $this->pageQueryVar = $pageQueryVar;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    /**
     * Build a URL for the given page. Returns a raw URL (not escaped); callers
     * must escape with esc_url() before using in HTML attributes.
     */
    public function pageUrl(int $page): string
    {
        return add_query_arg($this->pageQueryVar, max(1, $page), $this->baseUrl);
    }
}
