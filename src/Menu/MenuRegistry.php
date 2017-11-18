<?php


namespace TheCodingMachine\CMS\StaticRegistry\Menu;


class MenuRegistry
{
    /**
     * @var MenuItem
     */
    private $rootMenu;

    public function __construct()
    {
        $this->rootMenu = new MenuItem('root');
    }

    /**
     * @return MenuItem
     */
    public function getRootMenu(): MenuItem
    {
        return $this->rootMenu;
    }

    /**
     * @param string[] $items The path from the root menu to the branch we are interested in.
     * @param null|string $url
     * @param float $priority
     * @param string|null $cssClass
     */
    public function registerMenuItem(array $items, ?string $url, float $priority = 0.0, ?string $cssClass = null): void
    {
        $this->registerSubMenuItem($this->rootMenu, $items, $url, $priority, $cssClass);
    }

    /**
     * @param string[] $items The path from the root menu to the branch we are interested in.
     * @param null|string $url
     * @param float $priority
     * @param string|null $cssClass
     */
    public function registerSubMenuItem(MenuItem $menuItem, array $items, ?string $url, float $priority = 0.0, ?string $cssClass): void
    {
        $label = array_shift($items);

        if (\count($items) > 0) {
            $childMenuItem = $menuItem->findChild($label);
            if ($childMenuItem === null) {
                $childMenuItem = new MenuItem($label);
                $menuItem->addMenuItem($childMenuItem, 0);
            }
            $this->registerSubMenuItem($childMenuItem, $items, $url, $priority, $cssClass);
            return;
        }

        $childMenuItem = new MenuItem($label, $url);
        $childMenuItem->setCssClass($cssClass);
        $menuItem->addMenuItem($childMenuItem, $priority);
    }
}
