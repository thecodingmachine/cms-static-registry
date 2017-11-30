<?php


namespace TheCodingMachine\CMS\StaticRegistry\Registry;


use Psr\Http\Message\ServerRequestInterface;
use TheCodingMachine\CMS\Block\Block;
use TheCodingMachine\CMS\Block\BlockInterface;
use TheCodingMachine\CMS\Page\PageRegistryInterface;
use TheCodingMachine\CMS\StaticRegistry\Twig\CmsPageExtension;
use TheCodingMachine\CMS\StaticRegistry\Twig\Context;
use TheCodingMachine\CMS\Theme\TwigThemeDescriptor;

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

        $themeDescriptor = $this->themeRegistry->getThemeDescriptor($page->getTheme());

        if ($page->getTemplate() !== null) {
            $context = $page->getContext();
            $context['content'][] = $page->getContent();
            $contentBlock = new Block(new TwigThemeDescriptor($page->getTemplate(), [
                'theme' => $themeDescriptor->getPath()
            ]), $context);
        } else {
            $contentBlock = $page->getContent();
        }

        $pageBlock = new Block($themeDescriptor, [
            'content' => [ $contentBlock ],
            'title' => $page->getTitle(),
            'url' => $page->getUrl(),
            'menu' => $this->pageRegistry->getRootMenuItem()
        ]);

        return $pageBlock;
    }
}
