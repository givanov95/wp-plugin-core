# wp-plugin-core

A small PHP toolkit for building WordPress plugins. Provides:

- **Service providers** — `PluginServiceProvider` (with Vite dev/prod asset
  enqueueing) and `RestEndpointServiceProvider` (with built-in nonce + capability
  checks).
- **Admin menus** — `AdminMenu` with top-level and submenu factories, registered
  in batch by `AdminMenuRegistrar`.
- **Controllers** — input validation and sanitization via `ValidationRule`
  enums, plus `success()` / `error()` JSON response helpers.
- **Database** — a thin wrapper around `$wpdb` with safe identifier escaping
  and `paginate()`.
- **Pagination** — a `Paginator` value object and a configurable
  `PaginationLinks` component.

Requires PHP 8.3+.

## Installation

```bash
composer require givanov95/wp-plugin-core
```

## Quickstart

```php
// my-plugin.php
require_once __DIR__ . '/vendor/autoload.php';

use MyPlugin\Providers\AssetsProvider;
use MyPlugin\Providers\ApiProvider;
use WpPluginCore\Providers\RestEndpointsManager;

(new AssetsProvider())->boot();
(new ApiProvider())->boot();

add_action('wp_enqueue_scripts', function () {
    RestEndpointsManager::localizeEndpoints('my-plugin', 'MyPluginData');
}, 20);
```

### Assets provider

```php
use WpPluginCore\Providers\PluginServiceProvider;

class AssetsProvider extends PluginServiceProvider
{
    protected function pluginMainFile(): string { return MY_PLUGIN_FILE; }
    protected function scriptHandle(): string   { return 'my-plugin'; }
    protected function styleHandle(): string    { return 'my-plugin'; }
    protected function devServerUrl(): string   { return 'http://localhost:5173'; }
    protected function entryPoint(): string     { return 'assets/js/main.ts'; }
    protected function distDirectory(): string  { return 'dist'; }
}
```

Place a file named `.vite-dev` in the plugin root to load from the dev server;
otherwise assets are read from the Vite `manifest.json`.

### REST endpoints

```php
use WpPluginCore\Providers\RestEndpointServiceProvider;

class ApiProvider extends RestEndpointServiceProvider
{
    protected function registerEndpoints(): void
    {
        $this->addRestEndpoint(
            namespace: 'my-plugin/v1',
            route:     '/items',
            callback:  [new ItemsController(), 'index'],
            method:    'GET',
            public:    false,
            capability: 'edit_posts',
        );
    }
}
```

Endpoints are auto-localized to JavaScript via `RestEndpointsManager`, so the
companion `@givanov95/wp-plugin-core-frontend` package can call them by route
without manually wiring nonces.

### Admin menus

```php
use WpPluginCore\Admin\Interfaces\ShouldHaveAdminMenu;
use WpPluginCore\Admin\Menu\AdminMenu;
use WpPluginCore\Admin\Menu\AdminMenuRegistrar;

class SettingsMenuProvider implements ShouldHaveAdminMenu
{
    public function getAdminMenu(): AdminMenu
    {
        return AdminMenu::topLevel(
            pageTitle: 'My Plugin',
            menuTitle: 'My Plugin',
            capability: 'manage_options',
            menuSlug: 'my-plugin',
            pageRenderCallback: fn () => (new SettingsPage())->render(),
        );
    }
}

AdminMenuRegistrar::register([
    SettingsMenuProvider::class,
    LogsSubmenuProvider::class, // returns AdminMenu::submenu(...)
]);
```

### Validation

```php
use WpPluginCore\Controllers\Controller;
use WpPluginCore\Enums\ValidationRule;

class ItemsController extends Controller
{
    public function store(\WP_REST_Request $request): void
    {
        $data = $this->validate($request->get_json_params(), [
            'email' => ['required' => true,  'rule' => ValidationRule::EMAIL],
            'age'   => ['required' => false, 'rule' => ValidationRule::INT],
        ]);

        $this->success($data);
    }
}
```

### Database

```php
use WpPluginCore\Database\Database;

$db = new Database('my_items', allowedColumns: ['id', 'email', 'created_at']);

$id    = $db->insert(['email' => 'a@b.c']);
$item  = $db->find($id);
$page  = $db->paginate(['status' => 'active'], page: 1, perPage: 20,
    orderBy: ['created_at' => 'DESC']);
```

The `allowedColumns` argument is optional. When provided, any column referenced
in `where()` / `orderBy` must be in the list. Otherwise, identifiers are
validated against `[A-Za-z_][A-Za-z0-9_]*` and backticked.

## License

MIT — see [LICENSE](LICENSE).
