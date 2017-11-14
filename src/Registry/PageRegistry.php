<?php


namespace TheCodingMachine\CMS\StaticRegistry\Registry;


use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Finder\Finder;
use TheCodingMachine\CMS\Block\BlockInterface;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Page;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Page404;

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

            $fileList->files()->in($this->pageDirectory)->sortByName();

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
}
