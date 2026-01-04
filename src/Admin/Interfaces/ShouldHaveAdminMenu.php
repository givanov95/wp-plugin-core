<?php

namespace WpPluginCore\Admin\Interfaces;

use WpPluginCore\Admin\Menu\AdminMenu;

interface ShouldHaveAdminMenu
{
    /**
     * Get the admin menu title
     */
    public function getAdminMenu(): AdminMenu;

}
