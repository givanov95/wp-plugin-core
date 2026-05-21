<?php

namespace WpPluginCore\Pages;

abstract class Page
{
    /**
     * Page title (used in the admin <h1> / browser title).
     */
    abstract public static function title(): string;

    /**
     * Full page HTML.
     */
    abstract public function render(): string;

    /**
     * Wrap content in the standard `.wrap` admin container.
     */
    protected static function wrap(string $content): string
    {
        return '<div class="wrap">' . $content . '</div>';
    }

    protected static function escape(?string $value = null): string
    {
        return esc_html($value ?? '');
    }

    protected static function escapeAttr(?string $value = null): string
    {
        return esc_attr($value ?? '');
    }
}
