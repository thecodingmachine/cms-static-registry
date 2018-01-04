<?php


namespace TheCodingMachine\CMS\StaticRegistry\Twig;

use Barclays\Dao\BaseBlockDao;
use Barclays\Dao\StaticBlockDao;
use Barclays\Model\BaseBlock;
use Barclays\Model\PageVersion;
use Barclays\Services\SerializationContext;
use TheCodingMachine\CMS\CMSException;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Block;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Page;
use TheCodingMachine\CMS\StaticRegistry\Registry\BlockRegistry;
use TheCodingMachine\CMS\StaticRegistry\Registry\PageRegistry;
use TheCodingMachine\TDBM\UncheckedOrderBy;

class CmsPageExtension extends \Twig_Extension
{
    /**
     * @var PageRegistry
     */
    private $pageRegistry;
    /**
     * @var BlockRegistry
     */
    private $blockRegistry;
    /**
     * @var string
     */
    private $rootUrl;

    public function __construct(PageRegistry $pageRegistry, BlockRegistry $blockRegistry, string $rootUrl)
    {
        $this->pageRegistry = $pageRegistry;
        $this->blockRegistry = $blockRegistry;
        $this->rootUrl = '/'.trim($rootUrl, '/').'/';
        if ($this->rootUrl === '//') {
            $this->rootUrl = '/';
        }
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return \Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function('cmsBlocksById', [$this, 'getCmsBlocksById']),
            new \Twig_Function('cmsPagesByTag', [$this, 'getCmsPagesByTag']),
            new \Twig_Function('cmsBlocksByTag', [$this, 'getCmsBlocksByTag']),
            new \Twig_Function('cmsPageByUrl', [$this, 'getCmsPageByUrl']),
            new \Twig_Function('url', [$this, 'getUrl']),
        ];
    }

    /**
     * @return Block[]
     */
    public function getCmsBlocksById(string $blockId): array
    {
        $blocks = $this->blockRegistry->getBlocks($blockId);

        return $blocks;
    }

    /**
     * @param string $tag
     * @param null|string $domain
     * @param string $orderBy One of the properties of the context. For instance, if you set this to 'date', it means you should add a "date" key to your context it you want to sort upon it.
     * @param string $direction
     * @param int|null $limit
     * @param int|null $offset
     * @return Page[]
     * @throws CMSException
     */
    public function getCmsPagesByTag(string $tag, ?string $domain = null, ?string $orderBy = null, string $direction = 'desc', int $limit = null, int $offset = null): array
    {
        if (!\in_array($direction, ['asc', 'desc'], true)) {
            throw new CMSException("Error while using getCmsPagesByTag. The fourth parameter (direction) must be either 'asc' or 'desc'.");
        }

        $pages = $this->pageRegistry->findPagesByTag($tag, $domain);

        if ($orderBy !== null) {
            usort($pages, function(Page $page1, Page $page2) use ($orderBy, $direction) {
                if ($direction === 'asc') {
                    return ($page1->getContext()[$orderBy] ?? null) <=> ($page2->getContext()[$orderBy] ?? null);
                } else {
                    return ($page2->getContext()[$orderBy] ?? null) <=> ($page1->getContext()[$orderBy] ?? null);
                }
            });
        }

        if ($limit !== null || $offset !== null) {
            $pages = \array_slice($pages, $offset, $limit);
        }

        return $pages;
    }

    /**
     * @param string $tag
     * @param string $orderBy One of the properties of the context. For instance, if you set this to 'date', it means you should add a "date" key to your context it you want to sort upon it.
     * @param string $direction
     * @param int|null $limit
     * @param int|null $offset
     * @return Block[]
     * @throws CMSException
     */
    public function getCmsBlocksByTag(string $tag, ?string $orderBy = null, string $direction = 'desc', int $limit = null, int $offset = null): array
    {
        if (!\in_array($direction, ['asc', 'desc'], true)) {
            throw new CMSException("Error while using getCmsBlocksByTag. The third parameter (direction) must be either 'asc' or 'desc'.");
        }

        $blocks = $this->blockRegistry->findBlocksByTag($tag);

        if ($orderBy !== null) {
            usort($blocks, function(Block $block1, Block $block2) use ($orderBy, $direction) {
                if ($direction === 'asc') {
                    return ($block1->getContext()[$orderBy] ?? null) <=> ($block2->getContext()[$orderBy] ?? null);
                } else {
                    return ($block2->getContext()[$orderBy] ?? null) <=> ($block1->getContext()[$orderBy] ?? null);
                }
            });
        }

        if ($limit !== null || $offset !== null) {
            $blocks = \array_slice($blocks, $offset, $limit);
        }

        return $blocks;
    }

    /**
     * @return Page
     */
    public function getCmsPageByUrl(string $url, string $domain = null): Page
    {
        return $this->pageRegistry->getPage($url, $domain ?: '<any>');
    }

    /**
     * Prepends the URL with the "base" url.
     *
     * @param string|null $url
     * @return string|null
     */
    public function getUrl(string $url = null): ?string
    {
        if ($url === null) {
            return null;
        }
        $isAbsolute = parse_url($url, PHP_URL_SCHEME) !== null;
        if ($isAbsolute) {
            return $url;
        } else {
            return $this->rootUrl.ltrim($url, '/');
        }
    }
}
