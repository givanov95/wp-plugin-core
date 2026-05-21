<?php

namespace WpPluginCore\Admin\Menu;

use WpPluginCore\Admin\Interfaces\ShouldHaveAdminMenu;

class AdminMenuRegistrar
{
    /**
     * Register admin menus for the given providers.
     *
     * Accepts either class names (instantiated with no args) or already
     * constructed objects. Top-level menus register first, submenus after,
     * so submenus can attach to parents created in the same batch.
     *
     * @param array<class-string|object> $providers
     */
    public static function register(array $providers): void
    {
        add_action('admin_menu', function () use ($providers) {
            $instances = array_map(
                static fn ($p) => is_object($p) ? $p : new $p(),
                $providers
            );

            $instances = array_filter(
                $instances,
                static fn ($p) => $p instanceof ShouldHaveAdminMenu
            );

            $topLevel  = [];
            $submenus  = [];

            foreach ($instances as $provider) {
                $menu = $provider->getAdminMenu();
                if ($menu->isSubmenu()) {
                    $submenus[] = $menu;
                } else {
                    $topLevel[] = $menu;
                }
            }

            foreach ($topLevel as $menu) {
                $menu->register();
            }
            foreach ($submenus as $menu) {
                $menu->register();
            }
        });
    }
}
