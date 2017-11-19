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

        try {
            $page = $this->pageRegistry->getPage($url, $domain);
        } catch (PageNotFoundException $e) {
            return null;
        }

        $block = new Block($this->themeRegistry->getThemeDescriptor($page->getTheme()), [
            'content' => [ $page->getContent() ],
            'title' => $page->getTitle(),
            'url' => $page->getUrl(),
            'menu' => $this->pageRegistry->getRootMenuItem()
        ]);

        return $block;
    }
}
