<?php

namespace WpPluginCore\Components;

abstract class Component
{
    /**
     * Renders the HTML for the component
     *
     * @return string
     */
    abstract public function render(): string;

    /**
     * Escaping html special chars
     *
     * @param  [type] $value
     * @return string
     */
    protected function escape($value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
