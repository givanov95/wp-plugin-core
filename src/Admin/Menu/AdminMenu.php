<?php

namespace WpPluginCore\Admin\Menu;

class AdminMenu
{
    private const KIND_TOP   = 'top';
    private const KIND_SUB   = 'sub';

    /**
     * @param callable $pageRenderCallback
     */
    private function __construct(
        private readonly string $kind,
        private readonly string $pageTitle,
        private readonly string $menuTitle,
        private readonly string $capability,
        private readonly string $menuSlug,
        private $pageRenderCallback,
        private readonly ?string $iconUrl = null,
        private readonly ?int $position = null,
        private readonly ?string $parentSlug = null,
    ) {
    }

    /**
     * Create a top-level admin menu.
     */
    public static function topLevel(
        string $pageTitle,
        string $menuTitle,
        string $capability,
        string $menuSlug,
        callable $pageRenderCallback,
        ?string $iconUrl = null,
        ?int $position = null
    ): self {
        return new self(
            kind: self::KIND_TOP,
            pageTitle: $pageTitle,
            menuTitle: $menuTitle,
            capability: $capability,
            menuSlug: $menuSlug,
            pageRenderCallback: $pageRenderCallback,
            iconUrl: $iconUrl,
            position: $position,
        );
    }

    /**
     * Create a submenu under a parent slug.
     */
    public static function submenu(
        string $parentSlug,
        string $pageTitle,
        string $menuTitle,
        string $capability,
        string $menuSlug,
        callable $pageRenderCallback,
        ?int $position = null
    ): self {
        return new self(
            kind: self::KIND_SUB,
            pageTitle: $pageTitle,
            menuTitle: $menuTitle,
            capability: $capability,
            menuSlug: $menuSlug,
            pageRenderCallback: $pageRenderCallback,
            position: $position,
            parentSlug: $parentSlug,
        );
    }

    public function register(): void
    {
        if ($this->kind === self::KIND_SUB) {
            add_submenu_page(
                $this->parentSlug,
                $this->pageTitle,
                $this->menuTitle,
                $this->capability,
                $this->menuSlug,
                $this->pageRenderCallback,
                $this->position
            );
            return;
        }

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

    public function getPageTitle(): string  { return $this->pageTitle; }
    public function getMenuTitle(): string  { return $this->menuTitle; }
    public function getCapability(): string { return $this->capability; }
    public function getMenuSlug(): string   { return $this->menuSlug; }
    public function getIconUrl(): ?string   { return $this->iconUrl; }
    public function getPosition(): ?int     { return $this->position; }
    public function getParentSlug(): ?string { return $this->parentSlug; }
    public function isSubmenu(): bool       { return $this->kind === self::KIND_SUB; }
}
