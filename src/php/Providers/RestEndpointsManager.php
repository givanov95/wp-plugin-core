<?php

namespace WpPluginCore\Providers;

final class RestEndpointsManager
{
    private static array $endpoints = [];

    public static function addEndpoints(array $endpoints): void
    {
        foreach ($endpoints as $key => $endpoint) {
            self::$endpoints[$key] = $endpoint;
        }
    }

    public static function getEndpoints(): array
    {
        return self::$endpoints;
    }

    public static function clearEndpoints(): void
    {
        self::$endpoints = [];
    }

    public static function localizeEndpoints(string $localizeScriptHandleName, string $localizeScriptObjectName): void
    {
        if (empty(self::$endpoints)) {
            return;
        }

        wp_localize_script(
            $localizeScriptHandleName,
            $localizeScriptObjectName,
            [
                'rest_url'       => get_rest_url(),
                'rest_endpoints' => self::$endpoints,
            ]
        );
    }
}
