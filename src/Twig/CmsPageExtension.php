<?php


namespace TheCodingMachine\CMS\StaticRegistry\Twig;

use Barclays\Dao\BaseBlockDao;
use Barclays\Dao\StaticBlockDao;
use Barclays\Model\BaseBlock;
use Barclays\Model\PageVersion;
use Barclays\Services\SerializationContext;
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
     * @return Page[]
     */
    public function getCmsPagesByTag(string $tag, ?string $domain = null, string $orderBy = 'date', string $direction = 'desc', int $limit = null, int $page = null): array
    {
        if (!in_array($direction, ['asc', 'desc'])) {
            return ["Error while using getCmsPagesByTag. The third parameter (direction) must be either 'asc' or 'desc'."];
        }

        $pages = $this->pageRegistry->findPagesByTag($tag, $domain);

        /*if ($limit !== null || $page !== null) {
            $blocks = $blocks->take(($page !== null) ? $page*$limit : null, $limit);
            $count = $blocks->totalCount();
        } else {
            $count = $blocks->count();
        }*/


        return $pages;
    }
}
