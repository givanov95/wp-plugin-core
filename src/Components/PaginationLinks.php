<?php

namespace WpPluginCore\Components;

use WpPluginCore\Pagination\Paginator;

/**
 * Renders pagination links for a Paginator.
 *
 * Class names and the info-text format are fully configurable via the
 * constructor. Defaults use Tailwind utility classes and English strings
 * (translatable via the supplied text domain).
 */
class PaginationLinks extends Component
{
    /**
     * @param string $textDomain Text domain used for translating the info string.
     * @param array{
     *   container?:    string,
     *   info?:         string,
     *   linksWrapper?: string,
     *   link?:         string,
     *   linkActive?:   string,
     *   linkDisabled?: string,
     *   ellipsis?:     string,
     * } $classes
     * @param array{
     *   prev?:     string,
     *   next?:     string,
     *   ellipsis?: string,
     *   info?:     string  Printf-format with 3 %d: from, to, total
     * } $labels
     */
    public function __construct(
        private readonly Paginator $paginator,
        private readonly string $textDomain = 'wp-plugin-core',
        private readonly array $classes = [],
        private readonly array $labels = [],
    ) {
    }

    public function render(): string
    {
        if (!$this->paginator->hasPages()) {
            return '';
        }

        $p = $this->paginator;
        $c = $this->classes();
        $l = $this->labels();

        $from  = (($p->currentPage - 1) * $p->perPage) + 1;
        $to    = min($p->currentPage * $p->perPage, $p->total);

        $html  = '<div class="' . esc_attr($c['container']) . '">';
        $html .= '<div class="' . esc_attr($c['info']) . '">';
        $html .= esc_html(sprintf($l['info'], $from, $to, $p->total));
        $html .= '</div>';
        $html .= '<div class="' . esc_attr($c['linksWrapper']) . '">';

        $html .= $this->link($p->currentPage - 1, $l['prev'], $p->currentPage === 1);

        foreach ($this->pages() as $page) {
            if ($page === '...') {
                $html .= '<span class="' . esc_attr($c['ellipsis']) . '">'
                    . esc_html($l['ellipsis']) . '</span>';
                continue;
            }

            $html .= $this->link($page, (string) $page, false, $page === $p->currentPage);
        }

        $html .= $this->link($p->currentPage + 1, $l['next'], $p->currentPage === $p->lastPage);

        return $html . '</div></div>';
    }

    private function link(int $page, string $label, bool $disabled = false, bool $active = false): string
    {
        $c = $this->classes();

        if ($disabled) {
            return '<span class="' . esc_attr($c['linkDisabled']) . '">' . esc_html($label) . '</span>';
        }

        $classes = $active
            ? trim($c['link'] . ' ' . $c['linkActive'])
            : $c['link'];

        return sprintf(
            '<a href="%s" class="%s">%s</a>',
            esc_url($this->paginator->pageUrl($page)),
            esc_attr($classes),
            esc_html($label)
        );
    }

    /**
     * @return array<int, int|string>
     */
    private function pages(): array
    {
        $p = $this->paginator;
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

    private function classes(): array
    {
        return $this->classes + [
            'container'    => 'flex items-center justify-between pt-4',
            'info'         => 'text-sm text-slate-600',
            'linksWrapper' => 'flex gap-1',
            'link'         => 'px-3 py-1 border rounded text-sm bg-white text-slate-700 hover:bg-slate-100',
            'linkActive'   => 'bg-slate-900 text-white border-slate-900',
            'linkDisabled' => 'px-3 py-1 text-slate-400 border rounded',
            'ellipsis'     => 'px-2 text-slate-500',
        ];
    }

    private function labels(): array
    {
        $defaults = [
            'prev'     => __('Previous', $this->textDomain),
            'next'     => __('Next', $this->textDomain),
            'ellipsis' => '…',
            /* translators: 1: first item index, 2: last item index, 3: total items */
            'info'     => __('Showing %1$d–%2$d of %3$d', $this->textDomain),
        ];

        return $this->labels + $defaults;
    }
}
