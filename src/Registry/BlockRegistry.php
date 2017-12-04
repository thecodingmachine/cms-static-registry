<?php


namespace TheCodingMachine\CMS\StaticRegistry\Registry;


use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Finder\Finder;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Block;

/**
 * The block registry can fetch Block objects from the "blocks" directory or from the container.
 */
class BlockRegistry
{
    /**
     * @var string
     */
    private $blockDirectory;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * An array of array of blocks indexed by id (several blocks can share the same ID)
     *
     * @var Block[][]
     */
    private $blocks;

    public function __construct(string $blockDirectory, ContainerInterface $container, CacheInterface $cache)
    {
        $this->blockDirectory = rtrim($blockDirectory, '/\\').'/';
        $this->container = $container;
        $this->cache = $cache;
    }

    /**
     * Returns all blocks with a given blockId (several blocks can share the same ID if they have different languages)
     *
     * @param string $blockId
     * @return Block[]
     */
    public function getBlocks(string $blockId): array
    {
        $key = 'block__'.$blockId;
        $block = $this->cache->get($key);
        if ($block === null) {
            $block = $this->getBlocksNoCache($blockId);
            $this->cache->set($key, $block);
        }
        return $block;
    }

    /**
     * @param string $blockName
     * @return Block[]
     * @throws BlockNotFoundException
     */
    private function getBlocksNoCache(string $blockName): array
    {
        if ($this->container->has($blockName)) {
            $entry = $this->container->get($blockName);
            if ($entry instanceof Block) {
                return [$entry];
            }
        }

        $blocks = $this->loadBlocks();
        if (isset($blocks[$blockName])) {
            return $blocks[$blockName];
        }

        throw BlockNotFoundException::couldNotLoadBlock($blockName);
    }

    /**
     * @return Block[][]
     */
    private function loadBlocks(): array
    {
        if ($this->blocks === null)
        {
            $this->blocks = [];
            $fileList = new Finder();

            $fileList->files()->in($this->blockDirectory)->name('/\.html|\.md/')->sortByName();

            foreach ($fileList as $splFileInfo) {
                $block = Block::fromFile($splFileInfo);
                $this->blocks[$block->getId()][] = $block;
            }
        }
        return $this->blocks;
    }

    /**
     * @param string $tag
     * @return Block[]
     */
    public function findBlocksByTag(string $tag): array
    {
        $blocks = $this->loadBlocks();

        $filteredBlocks = [];
        foreach ($blocks as $blockArray) {
            $filteredBlocks = array_merge($filteredBlocks, array_filter($blockArray, function(Block $block) use ($tag) {
                return in_array($tag, $block->getTags(), true);
            }));
        }
        return $filteredBlocks;
    }
}
