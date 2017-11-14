<?php


namespace TheCodingMachine\CMS\StaticRegistry\Registry;


use Psr\Http\Message\ServerRequestInterface;
use TheCodingMachine\CMS\Block\Block;
use TheCodingMachine\CMS\Block\BlockInterface;
use TheCodingMachine\CMS\Page\PageRegistryInterface;

class StaticRegistry implements PageRegistryInterface
{
    /**
     * @var PageRegistry
     */
    private $pageRegistry;
    /**
     * @var ThemeRegistry
     */
    private $themeRegistry;

    public function __construct(PageRegistry $pageRegistry, ThemeRegistry $themeRegistry)
    {
        $this->pageRegistry = $pageRegistry;
        $this->themeRegistry = $themeRegistry;
    }

    public function getPage(ServerRequestInterface $request): ?BlockInterface
    {
        $uri = $request->getUri();
        $domain = $uri->getHost();
        $url = $uri->getPath();

        $page = $this->pageRegistry->getPage($url, $domain);

        $block = new Block($this->themeRegistry->getThemeDescriptor($page->getTheme()), [
            'content' => [ $page->getContent() ]
        ]);

        return $block;
    }
}
