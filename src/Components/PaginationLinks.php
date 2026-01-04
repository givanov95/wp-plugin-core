<?php

namespace WpPluginCore\Components;

use WpPluginCore\Pagination\Paginator;

class PaginationLinks extends Component
{
    public function __construct(
        private Paginator $paginator
    ) {

    }


    public function render(): string
    {
        if (!$this->paginator->hasPages()) {
            return '';
        }

        $html = '<div class="flex items-center justify-between pt-4">';

        // info
        $html .= sprintf(
            '<div class="text-sm text-slate-600">
                Показани %d–%d от %d
            </div>',
            (($this->paginator->currentPage - 1) * $this->paginator->perPage) + 1,
            min($this->paginator->currentPage * $this->paginator->perPage, $this->paginator->total),
            $this->paginator->total
        );

        // links
        $html .= '<div class="flex gap-1">';

        // prev
        $html .= self::link(
            $this->paginator,
            $this->paginator->currentPage - 1,
            '‹',
            $this->paginator->currentPage === 1
        );

        foreach (self::pages($this->paginator) as $page) {
            if ($page === '...') {
                $html .= '<span class="px-2 text-slate-500">…</span>';
                continue;
            }

            $html .= self::link(
                $this->paginator,
                $page,
                (string)$page,
                false,
                $page === $this->paginator->currentPage
            );
        }

        // next
        $html .= self::link(
            $this->paginator,
            $this->paginator->currentPage + 1,
            '›',
            $this->paginator->currentPage === $this->paginator->lastPage
        );

        return $html . '</div></div>';
    }

    private static function link(
        Paginator $p,
        int $page,
        string $label,
        bool $disabled = false,
        bool $active = false
    ): string {
        if ($disabled) {
            return '<span class="px-3 py-1 text-slate-400 border rounded">' . $label . '</span>';
        }

        return sprintf(
            '<a href="%s"
                class="px-3 py-1 border rounded text-sm %s">
                %s
            </a>',
            esc_url($p->pageUrl($page)),
            $active
                ? 'bg-slate-900 text-white border-slate-900'
                : 'bg-white text-slate-700 hover:bg-slate-100',
            esc_html($label)
        );
    }

    private static function pages(Paginator $p): array
    {
        $pages = [];

        $start = max(1, $p->currentPage - 2);
        $end   = min($p->lastPage, $p->currentPage + 2);

        if ($start > 1) {
            $pages[] = 1;
            if ($start > 2) {
                $pages[] = '...';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        if ($end < $p->lastPage) {
            if ($end < $p->lastPage - 1) {
                $pages[] = '...';
            }
            $pages[] = $p->lastPage;
        }

        return $pages;
    }
}
