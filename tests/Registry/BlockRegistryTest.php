<?php

namespace TheCodingMachine\CMS\StaticRegistry\Registry;


use PHPUnit\Framework\TestCase;
use Simplex\Container;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Block;

class BlockRegistryTest extends TestCase
{

    public function testGetBlocks()
    {
        $simplex = new Container();
        $simplex->set('bar_block', function() {
            return new Block('bar_block', 'foo', 'fr', [], null, []);
        });
        $blockRegistry = new BlockRegistry(__DIR__.'/../fixtures/Loaders/blocks', $simplex, new ArrayCache());
        $blocks = $blockRegistry->getBlocks('my_block');
        $this->assertSame('my_block', $blocks[0]->getId());

        $blocks = $blockRegistry->getBlocks('my_block');
        $this->assertSame('my_block', $blocks[0]->getId());

        $blocks = $blockRegistry->getBlocks('bar_block');
        $this->assertSame('bar_block', $blocks[0]->getId());

        $this->expectException(BlockNotFoundException::class);
        $blockRegistry->getBlocks('no_block');
    }

    public function testFindBlocksByTag()
    {
        $simplex = new Container();
        $blockRegistry = new BlockRegistry(__DIR__.'/../fixtures/Loaders/blocks', $simplex, new ArrayCache());

        $blocks = $blockRegistry->findBlocksByTag('foo');
        $this->assertCount(3, $blocks);

        $blocks = $blockRegistry->findBlocksByTag('bar');
        $this->assertCount(2, $blocks);

        $blocks = $blockRegistry->findBlocksByTag('not_exist');
        $this->assertCount(0, $blocks);
    }
}
