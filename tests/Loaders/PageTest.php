<?php

namespace TheCodingMachine\CMS\StaticRegistry\Loaders;

use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    public function testLoadPage()
    {
        $page = Page::fromFile(__DIR__.'/../fixtures/Loaders/pages/test_import.html');

        $this->assertNull($page->getId());
        $this->assertSame('foo', $page->getTitle());
        $this->assertSame('bar', $page->getMetaTitle());
        $this->assertSame('baz', $page->getMetaDescription());
        $this->assertSame(['menu 1', 'menu 2', 'menu 3'], $page->getMenu());
        $this->assertSame(1, $page->getMenuOrder());
        $this->assertSame('fooClass', $page->getMenuCssClass());
        $this->assertSame('example.com', $page->getWebsite());
        $this->assertSame('fr', $page->getLang());
        $this->assertSame('foo_theme', $page->getTheme());
        $this->assertSame('Foobar', $page->getContent());
        $this->assertSame('/foo/bar', $page->getUrl());
        $this->assertSame('baz', $page->getContext()['bar']);
    }

    public function testLoadMarkdownBlock()
    {
        $block = Page::fromFile(__DIR__ . '/../fixtures/Loaders/pages/test_import.md');

        $this->assertSame('<p><em>Foobar</em></p>', $block->getContent());
    }

    public function testMissingUrlField()
    {
        $this->expectException(UnableToLoadFileException::class);
        Page::fromFile(__DIR__.'/../fixtures/Loaders/test_import_bad.html');
    }

    public function testMissingFile()
    {
        $this->expectException(UnableToLoadFileException::class);
        Page::fromFile(__DIR__.'/../fixtures/Loaders/not_exists.html');
    }

    public function testInvalidExtension()
    {
        $this->expectException(InvalidExtensionException::class);
        Page::fromFile(__FILE__);
    }

    public function testInherits()
    {
        $page = Page::fromFile(__DIR__.'/../fixtures/Loaders/inherited_pages/main.md');

        $this->assertSame('foo', $page->getTitle());
        $this->assertSame('fr', $page->getLang());
        $this->assertSame('foo_theme', $page->getTheme());
        $this->assertSame('/foo/bar/baz', $page->getUrl());
        $this->assertSame(['blog'], $page->getTags());
    }

    public function testInvalidInheritance()
    {
        $this->expectException(UnableToLoadFileException::class);
        Page::fromFile(__DIR__.'/../fixtures/Loaders/inherited_pages/failed_inheritance.md');
    }
}
