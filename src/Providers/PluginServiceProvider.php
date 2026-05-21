<?php

namespace WpPluginCore\Providers;

use RuntimeException;

abstract class PluginServiceProvider extends ServiceProvider
{
    /**
     * Filename used as the "dev mode" flag. When present in the plugin
     * root, assets are loaded from the Vite dev server instead of dist.
     */
    protected const DEV_FLAG = '.vite-dev';

    public function boot(): void
    {
        $this->addAction('wp_enqueue_scripts', fn () => $this->enqueueAssets('wp_head'));
        $this->addAction('admin_enqueue_scripts', fn () => $this->enqueueAssets('admin_head'));
        $this->registerModuleTypeFilter();
    }

    /**
     * Absolute path to the plugin's main file (the one with the plugin header).
     */
    abstract protected function pluginMainFile(): string;

    /**
     * Script handle used for wp_enqueue_script.
     */
    abstract protected function scriptHandle(): string;

    /**
     * Style handle used for wp_enqueue_style.
     */
    abstract protected function styleHandle(): string;

    /**
     * Vite dev server URL (e.g. http://localhost:5173).
     */
    abstract protected function devServerUrl(): string;

    /**
     * Vite entrypoint relative to the project root (e.g. "assets/js/main.ts").
     */
    abstract protected function entryPoint(): string;

    /**
     * Directory (relative to the plugin root) containing the Vite build output.
     */
    abstract protected function distDirectory(): string;

    /**
     * Whether to also enqueue this plugin's assets on admin pages.
     * Override and return false to disable admin enqueue entirely.
     */
    protected function enqueueOnAdmin(): bool
    {
        return true;
    }

    /**
     * Whether to enqueue this plugin's assets on the public frontend.
     * Override and return false to disable frontend enqueue entirely.
     */
    protected function enqueueOnFrontend(): bool
    {
        return true;
    }

    private function enqueueAssets(string $headHook): void
    {
        $isAdmin = $headHook === 'admin_head';

        if ($isAdmin && !$this->enqueueOnAdmin()) {
            return;
        }
        if (!$isAdmin && !$this->enqueueOnFrontend()) {
            return;
        }

        $pluginFile = $this->pluginMainFile();
        $pluginDir  = plugin_dir_path($pluginFile);
        $pluginUri  = plugin_dir_url($pluginFile);

        $isDev = file_exists($pluginDir . self::DEV_FLAG);

        if ($isDev) {
            $this->enqueueDevAssets($headHook);
        } else {
            $this->enqueueProductionAssets($pluginDir, $pluginUri);
        }
    }

    private function enqueueDevAssets(string $headHook): void
    {
        $dev = rtrim($this->devServerUrl(), '/');

        wp_enqueue_script('vite-client', $dev . '/@vite/client', [], null, true);

        wp_enqueue_script(
            $this->scriptHandle(),
            $dev . '/' . ltrim($this->entryPoint(), '/'),
            ['vite-client'],
            null,
            true
        );
    }

    private function enqueueProductionAssets(string $pluginDir, string $pluginUri): void
    {
        $distDir       = trim($this->distDirectory(), '/');
        $manifestPath  = $pluginDir . $distDir . '/.vite/manifest.json';

        if (!file_exists($manifestPath)) {
            $this->reportBuildError("Vite manifest not found at: {$manifestPath}");
            return;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $entryKey = $this->entryPoint();
        $entry    = $manifest[$entryKey] ?? null;

        if (!$entry) {
            $this->reportBuildError("Entry '{$entryKey}' not found in Vite manifest.");
            return;
        }

        $distUri = trailingslashit($pluginUri) . $distDir . '/';

        foreach (($entry['css'] ?? []) as $index => $cssFile) {
            wp_enqueue_style(
                $index === 0 ? $this->styleHandle() : $this->styleHandle() . '-' . $index,
                $distUri . $cssFile,
                [],
                null
            );
        }

        wp_enqueue_script(
            $this->scriptHandle(),
            $distUri . $entry['file'],
            [],
            null,
            true
        );
    }

    private function registerModuleTypeFilter(): void
    {
        $this->addFilter('script_loader_tag', function ($tag, $handle, $src) {
            if (in_array($handle, ['vite-client', $this->scriptHandle()], true)) {
                return '<script type="module" src="' . esc_url($src) . '"></script>';
            }
            return $tag;
        }, 10, 3);
    }

    private function reportBuildError(string $message): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            throw new RuntimeException($message);
        }
        error_log('[wp-plugin-core] ' . $message);
    }
}
