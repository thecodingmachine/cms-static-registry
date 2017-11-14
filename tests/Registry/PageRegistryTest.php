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

}
