<?php

namespace TheCodingMachine\CMS\StaticRegistry\Loaders;

use PHPUnit\Framework\TestCase;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Block;
use TheCodingMachine\CMS\StaticRegistry\Loaders\InvalidExtensionException;
use TheCodingMachine\CMS\StaticRegistry\Loaders\UnableToLoadFileException;

class BlockTest extends TestCase
{
    public function testLoadBlock()
    {
        $block = Block::fromFile(new \SplFileInfo(__DIR__ . '/../fixtures/Loaders/blocks/test_block_import.html'));

        $this->assertSame('my_block', $block->getId());
        $this->assertSame('fr', $block->getLang());
        $this->assertSame('Foobar', $block->getContent());
        $this->assertSame(['foo', 'bar'], $block->getTags());
        $this->assertSame('block.twig', $block->getTemplate());
        $this->assertSame(['date' => strtotime('2017-12-12')], $block->getContext());
    }

    public function testLoadMarkdownBlock()
    {
        $block = Block::fromFile(new \SplFileInfo(__DIR__ . '/../fixtures/Loaders/blocks/test_block_import.md'));

        $this->assertSame('<p><em>Foobar</em></p>', $block->getContent());
    }

    public function testMissingField()
    {
        $this->expectException(UnableToLoadFileException::class);
        Block::fromFile(new \SplFileInfo(__DIR__.'/../fixtures/Loaders/test_import_bad.html'));
    }

    public function testMissingFile()
    {
        $this->expectException(UnableToLoadFileException::class);
        Block::fromFile(new \SplFileInfo(__DIR__.'/../fixtures/Loaders/not_exists.html'));
    }

    public function testInvalidExtension()
    {
        $this->expectException(InvalidExtensionException::class);
        Block::fromFile(new \SplFileInfo(__FILE__));
    }
}
