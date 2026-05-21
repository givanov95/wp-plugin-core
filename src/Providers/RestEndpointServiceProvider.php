<?php

namespace WpPluginCore\Providers;

use WP_Error;
use WP_REST_Request;

abstract class RestEndpointServiceProvider extends ServiceProvider
{
    /**
     * @var array<int, array{namespace:string,route:string,callback:callable,method:string,public:bool,capability:?string}>
     */
    protected array $restEndpoints = [];

    abstract protected function registerEndpoints(): void;

    public function boot(): void
    {
        $this->registerEndpoints();
        $this->addAction('rest_api_init', fn () => $this->registerRestRoutes());

        RestEndpointsManager::addEndpoints($this->getNormalizedRestEndpoints());
    }

    /**
     * Register a single REST endpoint definition.
     *
     * @param string      $namespace  REST namespace (e.g. "my-plugin/v1")
     * @param string      $route      Route pattern (e.g. "/items")
     * @param callable    $callback   Endpoint handler
     * @param string      $method     HTTP method (GET, POST, PUT, PATCH, DELETE)
     * @param bool        $public     If true, no auth/capability check is performed
     * @param string|null $capability WP capability required when $public is false; defaults to 'read'
     */
    protected function addRestEndpoint(
        string $namespace,
        string $route,
        callable $callback,
        string $method = 'POST',
        bool $public = true,
        ?string $capability = null
    ): self {
        $this->restEndpoints[] = [
            'namespace'  => $namespace,
            'route'      => $route,
            'callback'   => $callback,
            'method'     => strtoupper($method),
            'public'     => $public,
            'capability' => $capability,
        ];

        return $this;
    }

    public function getNormalizedRestEndpoints(): array
    {
        $normalized = [];

        foreach ($this->restEndpoints as $rest) {
            $route     = ltrim($rest['route'], '/');
            $fullRoute = ltrim($rest['namespace'] . '/' . $route, '/');
            $key       = $fullRoute . '|' . $rest['method'];

            $normalized[$key] = [
                'route'  => $fullRoute,
                'nonce'  => wp_create_nonce('wp_rest'),
                'method' => $rest['method'],
            ];
        }

        return $normalized;
    }

    private function registerRestRoutes(): void
    {
        foreach ($this->restEndpoints as $endpoint) {
            register_rest_route(
                $endpoint['namespace'],
                '/' . ltrim($endpoint['route'], '/'),
                [
                    'methods'             => $endpoint['method'],
                    'callback'            => $endpoint['callback'],
                    'permission_callback' => $this->makePermissionCallback($endpoint),
                ]
            );
        }
    }

    /**
     * Build a permission_callback that verifies the WP REST nonce
     * and (optionally) a capability for non-public endpoints.
     *
     * @param array{public:bool,capability:?string} $endpoint
     */
    private function makePermissionCallback(array $endpoint): callable
    {
        return function (WP_REST_Request $request) use ($endpoint) {
            $nonce = $request->get_header('X-WP-Nonce');

            if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
                return new WP_Error(
                    'rest_invalid_nonce',
                    __('Invalid or missing REST nonce.', 'wp-plugin-core'),
                    ['status' => 403]
                );
            }

            if ($endpoint['public']) {
                return true;
            }

            $capability = $endpoint['capability'] ?? 'read';

            if (!current_user_can($capability)) {
                return new WP_Error(
                    'rest_forbidden',
                    __('You do not have permission to access this endpoint.', 'wp-plugin-core'),
                    ['status' => 403]
                );
            }

            return true;
        };
    }
}
