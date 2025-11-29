<?php

namespace WpPluginCore\Providers;

abstract class ServiceProvider
{
    abstract public function boot(): void;


    /**
     * Adds action
     *
     * @param  string   $hook
     * @param  callable $callback
     * @param  integer  $priority
     * @param  integer  $accepted_args
     * @return void
     */
    protected function addAction(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        add_action($hook, $callback, $priority, $accepted_args);
    }

    /**
     * Adds filter
     *
     * @param  string   $hook
     * @param  callable $callback
     * @param  integer  $priority
     * @param  integer  $accepted_args
     * @return void
     */
    protected function addFilter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        add_filter($hook, $callback, $priority, $accepted_args);
    }

}
