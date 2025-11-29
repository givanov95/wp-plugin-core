<?php

namespace WpPluginCore\Providers;

final class PluginServiceProvider extends ServiceProvider
{
    public const PLUGIN_SCRIPT_NAME = 'ywi-subscriptions-main-js';
    public const PLUGIN_STYLE_NAME = 'ywi-subscriptions-main-style';

    public function boot(): void
    {
        $this->bootAssets();
        $this->addModuleTypeToScripts();
    }

    protected function bootAssets(): void
    {
        $this->addAction('wp_enqueue_scripts', function () {


            $plugin_root = dirname(dirname(__DIR__)); // /src/Providers → /src → /plugin-root


            $plugin_dir = plugin_dir_path($plugin_root . '/youworthitbox-subscriptions.php');


            $plugin_uri = plugin_dir_url($plugin_root . '/youworthitbox-subscriptions.php');

            error_log("PLUGIN DIR: " . $plugin_dir);


            $is_dev = file_exists($plugin_dir . '/.vite-dev');

            if ($is_dev) {
                $this->enqueueDevAssets();
            } else {
                $this->enqueueProductionAssets($plugin_dir, $plugin_uri);
            }
        });
    }

    private function enqueueDevAssets(): void
    {
        wp_enqueue_script('vite-client', 'http://localhost:5173/@vite/client', [], null, true);

        wp_enqueue_script(
            self::PLUGIN_SCRIPT_NAME,
            'http://localhost:5173/assets/js/main.ts',
            ['vite-client'],
            null,
            true
        );


        $this->addAction('wp_head', function () {
            echo '<link rel="stylesheet" href="http://localhost:5173/assets/css/main.css" />';
        });
    }

    private function enqueueProductionAssets(string $plugin_dir, string $plugin_uri): void
    {
        $manifest_path = $plugin_dir . '/dist/.vite/manifest.json';
        if (!file_exists($manifest_path)) {
            error_log("Manifest file not found: " . $manifest_path);
            return;
        }

        $manifest = json_decode(file_get_contents($manifest_path), true);

        $js_entry = $manifest['assets/js/main.ts'] ?? null;

        if ($js_entry) {
            if (!empty($js_entry['css'])) {
                foreach ($js_entry['css'] as $css_file) {
                    wp_enqueue_style(
                        self::PLUGIN_STYLE_NAME,
                        $plugin_uri . '/dist/' . $css_file,
                        [],
                        null
                    );
                }
            }

            wp_enqueue_script(
                self::PLUGIN_SCRIPT_NAME,
                $plugin_uri . '/dist/' . $js_entry['file'],
                [],
                null,
                true
            );
        }
    }

    private function addModuleTypeToScripts(): void
    {
        $this->addFilter('script_loader_tag', function ($tag, $handle, $src) {
            if (in_array($handle, ['vite-client', self::PLUGIN_SCRIPT_NAME], true)) {
                return '<script type="module" src="' . esc_url($src) . '"></script>';
            }
            return $tag;
        }, 10, 3);
    }





}
