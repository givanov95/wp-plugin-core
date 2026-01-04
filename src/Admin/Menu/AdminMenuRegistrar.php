<?php

namespace WpPluginCore\Admin\Menu;

use WpPluginCore\Admin\Interfaces\ShouldHaveAdminMenu;

class AdminMenuRegistrar
{
    public static function register(array $providers): void
    {
        \add_action('admin_menu', function () use ($providers) {
            foreach ($providers as $provider) {
                $providerClass = new $provider();
                if ($providerClass instanceof ShouldHaveAdminMenu) {
                    $providerClass->getAdminMenu()->register();
                }
            }
        });
    }
}
