<?php

namespace WpPluginCore\Admin\Menu;

class AdminMenu
{
    private string $pageTitle;
    private string $menuTitle;
    private string $capability;
    private string $menuSlug;
    private ?string $iconUrl;
    private ?int $position;

    public function __construct(
        string $pageTitle,
        string $menuTitle,
        string $capability,
        string $menuSlug,
        ?string $iconUrl = null,
        ?int $position = null
    ) {
        $this->pageTitle = $pageTitle;
        $this->menuTitle = $menuTitle;
        $this->capability = $capability;
        $this->menuSlug = $menuSlug;
        $this->iconUrl = $iconUrl;
        $this->position = $position;
    }


    /**
     * Register the menu in WordPress
     *
     * @param callable $callback Function to render the page
     */
    public function register(callable $callback): void
    {
        add_menu_page(
            $this->pageTitle,
            $this->menuTitle,
            $this->capability,
            $this->menuSlug,
            $callback,
            $this->iconUrl,
            $this->position
        );
    }

    /**
     * Convert to WordPress-friendly array
     */
    public function toArray(): array
    {
        return [
            'page_title' => $this->pageTitle,
            'menu_title' => $this->menuTitle,
            'capability' => $this->capability,
            'menu_slug'  => $this->menuSlug,
            'icon_url'   => $this->iconUrl,
            'position'   => $this->position,
        ];
    }


    public function getPageTitle(): string
    {
        return $this->pageTitle;
    }

    public function getMenuTitle(): string
    {
        return $this->menuTitle;
    }

    public function getCapability(): string
    {
        return $this->capability;
    }

    public function getMenuSlug(): string
    {
        return $this->menuSlug;
    }

    public function getIconUrl(): ?string
    {
        return $this->iconUrl;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }
}
