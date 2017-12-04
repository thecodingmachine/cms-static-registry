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

    public function __construct(PageRegistry $pageRegistry, BlockRegistry $blockRegistry)
    {
        $this->pageRegistry = $pageRegistry;
        $this->blockRegistry = $blockRegistry;
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
            throw new CMSException("Error while using getCmsPagesByTag. The third parameter (direction) must be either 'asc' or 'desc'.");
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
}
