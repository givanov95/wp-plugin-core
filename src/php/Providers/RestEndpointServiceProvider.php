<?php

namespace WpPluginCore\Providers;

use WP_Error;

abstract class RestEndpointServiceProvider extends ServiceProvider
{
    protected array $restEndpoints = [];

    abstract protected function registerEndpoints(): void;

    public function boot(): void
    {
        $this->registerEndpoints();
        $this->registerRestEndpoints();


        RestEndpointsManager::addEndpoints($this->getNormalizedRestEndpoints());
    }

    public function getNormalizedRestEndpoints(): array
    {
        $normalizedEndpoints = [];
        foreach ($this->restEndpoints as $rest) {
            $normalizedRoute = ltrim($rest['route'], '/');
            $fullRoute = $rest['namespace'] . '/' . $normalizedRoute;
            $fullRoute = ltrim($fullRoute, '/');
            $endpointKey = $fullRoute . '|' . $rest['method'];

            $normalizedEndpoints[$endpointKey] = [
                'route' => $fullRoute,
                'nonce' => wp_create_nonce('wp_rest'),
                'method' => $rest['method'],
            ];
        }

        return $normalizedEndpoints;
    }

    protected function addRestEndpoint(
        string $namespace,
        string $route,
        callable $callback,
        string $method = 'POST',
        bool $public = true
    ): self {
        $this->restEndpoints[] = [
            'namespace' => $namespace,
            'route'     => $route,
            'callback'  => $callback,
            'method'    => strtoupper($method),
            'public'    => $public,
        ];

        return $this;
    }

    private function registerRestEndpoints(): void
    {
        add_action('rest_api_init', function () {
            foreach ($this->restEndpoints as $restEndpoint) {
                $normalizedRoute = ltrim($restEndpoint['route'], '/');

                register_rest_route(
                    $restEndpoint['namespace'],
                    '/' . $normalizedRoute,
                    [
                        'methods'  => $restEndpoint['method'],
                        'callback' => function (\WP_REST_Request $request) use ($restEndpoint) {
                            $nonce = $request->get_header('X-WP-Nonce');

                            if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
                                return new WP_Error(
                                    'rest_invalid_nonce',
                                    'Invalid nonce',
                                    ['status' => 403]
                                );
                            }

                            return call_user_func($restEndpoint['callback'], $request);
                        },
                        'permission_callback' => $restEndpoint['public'] ?
                            '__return_true' :
                            function () {
                                return current_user_can('read');
                            },
                    ]
                );
            }
        });
    }
}
