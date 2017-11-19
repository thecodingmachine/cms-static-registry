<?php

namespace TheCodingMachine\CMS\StaticRegistry\Registry;

use PHPUnit\Framework\TestCase;
use Simplex\Container;
use Symfony\Component\Cache\Simple\ArrayCache;

class PageRegistryTest extends TestCase
{
    public function testGetPage()
    {
        $pageRegistry = new PageRegistry(__DIR__ . '/../fixtures/Loaders/pages', new ArrayCache());

        $page = $pageRegistry->getPage('/foo/bar', 'example.com');
        $this->assertSame('/foo/bar', $page->getUrl());

        $page = $pageRegistry->getPage('/foo/bar/baz', 'whatever.com');
        $this->assertSame('/foo/bar/baz', $page->getUrl());

        $this->expectException(PageNotFoundException::class);
        $pageRegistry->getPage('/not/exists', 'unknown.com');
    }

    public function testDuplicatePages()
    {
        $pageRegistry = new PageRegistry(__DIR__ . '/../fixtures/Loaders/duplicate_pages', new ArrayCache());

        $this->expectException(DuplicatePageException::class);
        $pageRegistry->getPage('/foo/bar', 'example.com');
    }

    public function testGetRootMenuItem()
    {
        $pageRegistry = new PageRegistry(__DIR__ . '/../fixtures/Loaders/pages', new ArrayCache());

        $menuItem = $pageRegistry->getRootMenuItem();
        $this->assertCount(2, $menuItem->getChildren()[0]->getChildren()[0]->getChildren());
    }

}
