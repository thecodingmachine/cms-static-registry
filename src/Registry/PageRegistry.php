<?php


namespace TheCodingMachine\CMS\StaticRegistry\Registry;


use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Finder\Finder;
use TheCodingMachine\CMS\Block\BlockInterface;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Page;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Page404;
use TheCodingMachine\CMS\StaticRegistry\Menu\MenuItem;
use TheCodingMachine\CMS\StaticRegistry\Menu\MenuRegistry;

/**
 * The page registry can fetch Page objects from the "pages" directory or from the container.
 */
class PageRegistry
{
    /**
     * @var string
     */
    private $pageDirectory;
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * An array of pages indexed by URL.
     *
     * @var Page[][]
     */
    private $pages;

    /**
     * @var MenuItem
     */
    private $rootMenuItem;

    public function __construct(string $pageDirectory, CacheInterface $cache)
    {
        $this->pageDirectory = rtrim($pageDirectory, '/\\').'/';
        $this->cache = $cache;
    }

    public function getPage(string $url, string $domain): Page
    {
        $pages = $this->getImportedPagesFromCache();

        if (isset($pages[$domain][$url])) {
            return $pages[$domain][$url];
        }
        if (isset($pages['<any>'][$url])) {
            return $pages['<any>'][$url];
        }

        throw PageNotFoundException::couldNotFindPage($url, $domain);
    }

    /**
     * @param string $tag
     * @param string|null $domain
     * @return Page[]
     */
    public function findPagesByTag(string $tag, ?string $domain): array
    {
        $pages = $this->getImportedPagesFromCache();

        if ($domain === null) {
            $pagesArrByTag = [];
            foreach ($pages as $domainKey => $pagesPerDomain) {
                $pagesArrByTag[] = $this->findPagesByTagAndDomain($pages, $tag, $domainKey);
            }
            return array_merge(...$pagesArrByTag);
        } else {
            $pagesForDomain = $this->findPagesByTagAndDomain($pages, $tag, $domain);
            $pagesForAnyDomain = $this->findPagesByTagAndDomain($pages, $tag, '<any>');
            return array_merge($pagesForDomain, $pagesForAnyDomain);
        }
    }

    /**
     * @param Page[][] $pages
     * @param string $tag
     * @param string $domain
     * @return Page[]
     */
    private function findPagesByTagAndDomain(array $pages, string $tag, string $domain): array
    {
        $taggedPages = [];
        if (isset($pages[$domain])) {
            foreach ($pages[$domain] as $page) {
                if (in_array($tag, $page->getTags(), true)) {
                    $taggedPages[] = $page;
                }
            }
        }
        return $taggedPages;
    }

    /**
     * @return Page[][]
     */
    private function getImportedPagesFromCache(): array
    {
        $key = 'pages';
        $pages = $this->cache->get($key);
        if ($pages === null) {
            $pages = $this->getImportedPages();
            $this->cache->set($key, $pages);
        }
        return $pages;
    }

    /**
     * @return Page[][]
     * @throws DuplicatePageException
     */
    private function getImportedPages(): array
    {
        if ($this->pages === null) {
            $this->pages = [];
            $fileList = new Finder();

            $fileList->files()->in($this->pageDirectory)->name('/\.html$|\.md/')->sortByName();

            foreach ($fileList as $file) {
                $importedPage = Page::fromFile($file->getRealPath());

                if (isset($this->pages[$importedPage->getWebsite() ?? '<any>'][$importedPage->getUrl()])) {
                    throw new DuplicatePageException(sprintf('There are 2 pages claiming the URL %s %s', $importedPage->getUrl(), $importedPage->getWebsite() ? ' of website '.$importedPage->getWebsite() : ''));
                }
                $this->pages[$importedPage->getWebsite() ?? '<any>'][$importedPage->getUrl()] = $importedPage;
            }
        }
        return $this->pages;
    }

    public function getRootMenuItem(): MenuItem
    {
        $key = 'rootMenuItem';
        $rootMenuItem = $this->cache->get($key);
        if ($rootMenuItem === null) {
            $rootMenuItem = $this->getRootMenuItemWithoutCache();
            $this->cache->set($key, $rootMenuItem);
        }
        return $rootMenuItem;
    }

    private function getRootMenuItemWithoutCache(): MenuItem
    {
        if ($this->rootMenuItem === null) {
            $menuRegistry = new MenuRegistry();
            $pages = $this->getImportedPages();
            foreach ($pages as $pagesFromWebsite) {
                foreach ($pagesFromWebsite as $page) {
                    if ($page->getMenu()) {
                        $menuRegistry->registerMenuItem(
                            $page->getMenu(),
                            $page->getUrl(),
                            $page->getMenuOrder(),
                            $page->getMenuCssClass()
                        );
                    }
                }
            }
            $this->rootMenuItem = $menuRegistry->getRootMenu();
        }
        return $this->rootMenuItem;
    }
}
