<?php

namespace WpPluginCore\Pages;

abstract class Page
{
    /**
     * Page title (admin page title, h1 и т.н.)
     */
    abstract public static function title(): string;

    /**
     * Render full page HTML
     */
    abstract public function render(): string;

    /**
     * Optional wrapper for admin pages
     */
    protected static function wrap(string $content): string
    {
        return '<div class="wrap">' . $content . '</div>';
    }

    protected static function escape(?string $value = null): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
