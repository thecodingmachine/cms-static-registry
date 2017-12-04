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
}
