<?php

namespace WpPluginCore\Pagination;

class Paginator
{
    public array $items;
    public int $total;
    public int $perPage;
    public int $currentPage;
    public int $lastPage;
    public string $baseUrl;

    public function __construct(
        array $items,
        int $total,
        int $perPage,
        int $currentPage,
        string $baseUrl
    ) {
        $this->items = $items;
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = max(1, $currentPage);
        $this->lastPage = (int)ceil($total / $perPage);
        $this->baseUrl = $baseUrl;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function hasPages(): bool
    {
        return $this->lastPage > 1;
    }

    public function pageUrl(int $page): string
    {
        return esc_url(add_query_arg('page', $page, $this->baseUrl));
    }
}
