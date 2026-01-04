<?php

namespace WpPluginCore\Admin\Menu;

use function add_menu_page;

class AdminMenu
{
    /**
     * @var string
     */
    private string $pageTitle;

    /**
     * @var string
     */
    private string $menuTitle;

    /**
     * @var string
     */
    private string $capability;

    /**
     * @var string
     */
    private string $menuSlug;

    /**
     * @var callable
     */
    private $pageRenderCallback;

    /**
     * @var string|null
     */
    private ?string $iconUrl;

    /**
     *
     * @var integer|null
     */
    private ?int $position;

    public function __construct(
        string $pageTitle,
        string $menuTitle,
        string $capability,
        string $menuSlug,
        callable $pageRenderCallback,
        ?string $iconUrl = null,
        ?int $position = null
    ) {
        $this->pageTitle = $pageTitle;
        $this->menuTitle = $menuTitle;
        $this->capability = $capability;
        $this->menuSlug = $menuSlug;
        $this->pageRenderCallback = $pageRenderCallback;
        $this->iconUrl = $iconUrl;
        $this->position = $position;


    }


    /**
     * Register the menu in WordPress
     *
     * @param callable $callback Function to render the page
     */
    public function register(): void
    {
        add_menu_page(
            $this->pageTitle,
            $this->menuTitle,
            $this->capability,
            $this->menuSlug,
            $this->pageRenderCallback,
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
