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
     * @var \SplPriorityQueue|MenuItem[]
     */
    private $children;

    /**
     * The CSS class for the menu, if any.
     *
     * @var string
     */
    private $cssClass;

    /**
     * Whether the menu is extended or not.
     * This should not have an effect if the menu has no child.
     *
     * @var bool
     */
    private $isExtended;

    /**
     * @param string $label The text for the menu item
     * @param string|null $url The link for the menu (relative to the root url), unless it starts with / or http:// or https:// or # or ?.
     */
    public function __construct(string $label, string $url=null) {
        $this->label = $label;
        $this->url = $url;
        $this->children = new \SplPriorityQueue();
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
     * Note: a SplPriorityQueue can be iterated only once so we clone the whole queue and turn it into an array
     *
     * @return MenuItem[]
     */
    public function getChildren(): array {
        return iterator_to_array(clone $this->children);
    }

    /**
     * Adds a menu item as a child of this menu item.
     *
     * @param MenuItem $menuItem
     */
    public function addMenuItem(MenuItem $menuItem, float $priority): void {
        $this->children->insert($menuItem, $priority);
    }


    public function isActive(string $url): bool
    {
        return $url === $this->url;
    }

    /**
     * Returns true if the menu should be in extended state (if one of the children is in active state).
     * @return bool
     */
    public function isExtended(string $url): bool {
        foreach ($this->children as $child) {
            if ($child->isActive($url) || $child->isExtended($url)) {
                return true;
            }
        }

        return false;
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
     * @param string|null $cssClass
     */
    public function setCssClass(?string $cssClass): void {
        $this->cssClass = $cssClass;
    }
}
