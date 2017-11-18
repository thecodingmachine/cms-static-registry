<?php
namespace TheCodingMachine\CMS\StaticRegistry\Menu;

/**
 * This class represent a menu item.
 */
class MenuItem {

    /**
     * The text for the menu item
     *
     * @var string
     */
    private $label;

    /**
     * The link for the menu (relative to the root url), unless it starts with / or http:// or https:// or # or ?.
     *
     * @var string|null
     */
    private $url;

    /**
     * The children menu item of this menu (if any).
     *
     * @var MenuItem[]
     */
    private $children;

    /**
     * The CSS class for the menu, if any.
     *
     * @var string
     */
    private $cssClass;

    /**
     * Whether the menu is in an active state or not.
     *
     * @var bool
     */
    private $isActive;

    /**
     * Whether the menu is extended or not.
     * This should not have an effect if the menu has no child.
     *
     * @var bool
     */
    private $isExtended;

    /**
     * Level of priority used to order the menu items.
     *
     * @var float
     */
    private $priority = 0.0;

    /**
     * @var bool
     */
    private $activateBasedOnUrl;

    /**
     * @var bool
     */
    private $sorted = false;

    /**
     * @param string $label The text for the menu item
     * @param string|null $url The link for the menu (relative to the root url), unless it starts with / or http:// or https:// or # or ?.
     * @param MenuItem[] $children
     */
    public function __construct(string $label, string $url=null, array $children=[]) {
        $this->label = $label;
        $this->url = $url;
        $this->children = $children;
    }

    /**
     * Returns the label for the menu item.
     * @return string
     */
    public function getLabel(): string {
        return $this->label;
    }

    /**
     * Returns the URL for this menu (or null if this menu is not a link).
     * @return string|null
     */
    public function getUrl(): ?string {
        return $this->url;
    }

    public function findChild(string $label): ?MenuItem
    {
        foreach ($this->children as $child) {
            if ($child->getLabel() === $label) {
                return $child;
            }
        }
        return null;
    }

    /**
     * Returns a list of children elements for the menu (if there are some).
     *
     * @return MenuItem[]
     */
    public function getChildren(): array {
        if ($this->sorted === false && $this->children) {
            // First, let's make 2 arrays: the array of children with a priority, and the array without.
            $childrenWithPriorities = array();
            $childrenWithoutPriorities = array();
            foreach ($this->children as $child) {
                /* @var $child MenuItem */
                $priority = $child->getPriority();
                if ($priority === null) {
                    $childrenWithoutPriorities[] = $child;
                } else {
                    $childrenWithPriorities[] = $child;
                }
            }

            usort($childrenWithPriorities, [$this, 'compareMenuItems']);
            $this->children = array_merge($childrenWithPriorities, $childrenWithoutPriorities);
            $this->sorted = true;
        }
        return $this->children;
    }

    public function compareMenuItems(MenuItem $item1, MenuItem $item2): int {
        return $item1->getPriority() <=> $item2->getPriority();
    }

    /**
     * The children menu item of this menu (if any).
     *
     * @param MenuItem[] $children
     * @return MenuItem
     */
    public function setChildren(array $children): self {
        $this->sorted = false;
        $this->children = $children;
        return $this;
    }

    /**
     * Adds a menu item as a child of this menu item.
     *
     * @param MenuItem $menuItem
     * @return MenuItem
     */
    public function addMenuItem(MenuItem $menuItem): self {
        $this->sorted = false;
        $this->children[] = $menuItem;
        return $this;
    }

    /**
     * Returns true if the menu is in active state (if we are on the page for this menu).
     * @return bool
     */
    public function isActive(string $rootUrl) {
        if ($this->isActive) {
            return true;
        }

        if($this->activateBasedOnUrl && $this->url !== null) {
            $urlParts = parse_url($_SERVER['REQUEST_URI']);
            $menuUrlParts = parse_url($this->getLink($rootUrl));

            if (isset($menuUrlParts['path'])) {
                $menuUrl = $menuUrlParts['path'];
            } else {
                $menuUrl = '/';
            }

            if (isset($urlParts['path'])) {
                $requestUrl = $urlParts['path'];
            } else {
                $requestUrl = '/';
            }

            if($requestUrl === $menuUrl) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the active state of the menu.
     *
     * @param bool $isActive
     * @return MenuItem
     */
    public function setIsActive(bool $isActive): self {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * Enables the menu item (activates it).
     *
     */
    public function enable(): self {
        $this->isActive = true;
        return $this;
    }

    /**
     * Returns true if the menu should be in extended state (if we can see the children directly).
     * @return bool
     */
    public function isExtended(): bool {
        // TODO: is extended if one of the sub menus is active!
        return $this->isExtended;
    }

    /**
     * Whether the menu is extended or not.
     * This should not have an effect if the menu has no child.
     *
     * @param bool $isExtended
     * @return MenuItem
     */
    public function setIsExtended(bool $isExtended = true): self {
        $this->isExtended = $isExtended;
        return $this;
    }

    /**
     * Returns an optional CSS class to apply to the menu item.
     * @return string|null
     */
    public function getCssClass(): ?string {
        return $this->cssClass;
    }

    /**
     * An optional CSS class to apply to the menu item.
     * Use of this property depends on the menu implementation.
     *
     * @param string $cssClass
     */
    public function setCssClass($cssClass) {
        $this->cssClass = $cssClass;
        return $this;
    }

    /**
     * Level of priority used to order the menu items.
     *
     * @param float $priority
     */
    public function setPriority(float $priority) {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Returns the level of priority. It is used to order the menu items.
     * @return float
     */
    public function getPriority(): float {
        return $this->priority;
    }

    /**
     * Returns the absolute URL, with parameters if required.
     * @return string
     */
    public function getLink(string $rootUrl) {
        if ($this->url === null) {
            return null;
        }

        if (strpos($this->url, "/") === 0
            || strpos($this->url, "javascript:") === 0
            || strpos($this->url, "http://") === 0
            || strpos($this->url, "https://") === 0
            || strpos($this->url, "?") === 0
            || strpos($this->url, "#") === 0) {
            return $this->url;
        }

        return $rootUrl.$this->url;
    }

    /**
     * If the URL of the current page matches the URL of the link, the link will be considered as "active".
     *
     * @param bool $activateBasedOnUrl
     * @return MenuItem
     */
    public function setActivateBasedOnUrl(bool $activateBasedOnUrl = true): self {
        $this->activateBasedOnUrl = $activateBasedOnUrl;
        return $this;
    }
}
