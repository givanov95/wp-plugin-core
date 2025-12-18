<?php

namespace WpPluginCore\Providers;

use WpPluginCore\Admin\Menu\AdminMenu;

abstract class PluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootAssets();
        $this->addModuleTypeToScripts();

        if ($this instanceof \WpPluginCore\Admin\Interfaces\ShouldHaveAdminMenu) {
            $adminMenu = $this->getAdminMenu();
            $this->registerAdminMenu($adminMenu);
        }
    }

    /**
     * Handle the main plugin file path
     */
    abstract protected function pluginMainFile(): string;

    /**
     * handle scripts
     */
    abstract protected function scriptHandle(): string;

    /**
     * handle styles
     */
    abstract protected function styleHandle(): string;

    /**
     * URL dev server (example: http://localhost:5173)
     */
    abstract protected function devServerUrl(): string;

    /**
     * Vite entrypoint, example: assets/js/main.ts
     */
    abstract protected function entryPoint(): string;

    /**
     * Dist dir
     */
    abstract protected function distDirectory(): string;

    /**
     * Boot the assets
     *
     * @return void
     */
    protected function bootAssets(): void
    {
        $this->addAction('wp_enqueue_scripts', function () {

            $plugin_file = $this->pluginMainFile();
            $plugin_dir  = plugin_dir_path($plugin_file);
            $plugin_uri  = plugin_dir_url($plugin_file);

            $is_dev = file_exists($plugin_dir . '/.vite-dev');

            if ($is_dev) {
                $this->enqueueDevAssets();
            } else {
                $this->enqueueProductionAssets($plugin_dir, $plugin_uri);
            }
        });
    }

    /**
     * Enqueue the dev assets to a script
     *
     * @return void
     */
    private function enqueueDevAssets(): void
    {

        $dev = rtrim($this->devServerUrl(), '/');

        wp_enqueue_script('vite-client', $dev . '/@vite/client', [], null, true);




        wp_enqueue_script(
            $this->scriptHandle(),
            $dev . '/' . $this->entryPoint(),
            ['vite-client'],
            null,
            true
        );

        $this->addAction('wp_head', function () use ($dev) {
            echo '<link rel="stylesheet" href="' . esc_url($dev . '/assets/css/main.css') . '" />';
        });
    }

    /**
     * Enqueue the production assets
     *
     * @param  string $plugin_dir
     * @param  string $plugin_uri
     * @return void
     */
    private function enqueueProductionAssets(string $plugin_dir, string $plugin_uri): void
    {
        $manifest_path = $plugin_dir . '/' . $this->distDirectory() . '/.vite/manifest.json';

        if (!file_exists($manifest_path)) {
            error_log("Manifest file not found: " . $manifest_path);
            return;
        }

        $manifest = json_decode(file_get_contents($manifest_path), true);

        $entry = $manifest[$this->entryPoint()] ?? null;

        if (!$entry) {
            error_log("Entry not found in manifest: " . $this->entryPoint());
            return;
        }

        if (!empty($entry['css'])) {
            foreach ($entry['css'] as $css_file) {
                wp_enqueue_style(
                    $this->styleHandle(),
                    $plugin_uri . '/' . $this->distDirectory() . '/' . $css_file,
                    [],
                    null
                );
            }
        }

        wp_enqueue_script(
            $this->scriptHandle(),
            $plugin_uri . '/' . $this->distDirectory() . '/' . $entry['file'],
            [],
            null,
            true
        );
    }

    /**
     * Adds modules to the scripts
     *
     * @return void
     */
    private function addModuleTypeToScripts(): void
    {
        $this->addFilter('script_loader_tag', function ($tag, $handle, $src) {
            if (in_array($handle, ['vite-client', $this->scriptHandle()], true)) {
                return '<script type="module" src="' . esc_url($src) . '"></script>';
            }
            return $tag;
        }, 10, 3);
    }

    private function registerAdminMenu(AdminMenu $adminMenu): void
    {

        $this->addAction('admin_menu', function () use ($adminMenu) {
            $adminMenu->register(function () {
                echo '<div class="wrap"><h1>' . esc_html(get_admin_page_title()) . '</h1></div>';
            });
        });
    }
}
