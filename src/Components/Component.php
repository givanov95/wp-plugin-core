<?php

namespace WpPluginCore\Components;

abstract class Component
{
    /**
     * Render the component HTML.
     */
    abstract public function render(): string;

    protected function escape(?string $value): string
    {
        return esc_html($value ?? '');
    }

    protected function escapeAttr(?string $value): string
    {
        return esc_attr($value ?? '');
    }
}
