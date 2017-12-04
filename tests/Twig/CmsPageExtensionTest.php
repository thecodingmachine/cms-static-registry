<?php

namespace TheCodingMachine\CMS\StaticRegistry\Twig;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Simplex\Container;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\CMS\Block\BlockRendererInterface;
use TheCodingMachine\CMS\CMSException;
use TheCodingMachine\CMS\DI\CMSUtilsServiceProvider;
use TheCodingMachine\CMS\StaticRegistry\DI\StaticRegistryServiceProvider;
use TheCodingMachine\CMS\StaticRegistry\Registry\StaticRegistry;
use TheCodingMachine\TwigServiceProvider;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class CmsPageExtensionTest extends TestCase
{
    public function testServiceProvider()
    {
        $simplex = new Container([
            new TwigServiceProvider(),
            new CMSUtilsServiceProvider(),
            new StaticRegistryServiceProvider()
        ]);

        $simplex->set('CMS_ROOT', __DIR__.'/../fixtures/Loaders');
        $simplex->set('THEMES_URL', '/themes/');
        $simplex->set(CacheInterface::class, function() { return new ArrayCache(); });

        $staticRegistry = $simplex->get(StaticRegistry::class);
        /* @var $staticRegistry StaticRegistry */
        $request = new ServerRequest([], [], new Uri('http://example.com/foo/twig'));

        $block = $staticRegistry->getPage($request);
        $blockRenderer = $simplex->get(BlockRendererInterface::class);
        /* @var $blockRenderer \TheCodingMachine\CMS\Block\BlockRendererInterface */
        $result = $blockRenderer->renderBlock($block);

        $this->assertSame(
<<<EOF
fr
en

/foo/bar
/foo/bar/baz
EOF
            , (string)$result->getContents());
    }

    public function testPageSort()
    {
        $simplex = new Container([
            new TwigServiceProvider(),
            new CMSUtilsServiceProvider(),
            new StaticRegistryServiceProvider()
        ]);

        $simplex->set('CMS_ROOT', __DIR__.'/../fixtures/Loaders');
        $simplex->set('THEMES_URL', '/themes/');
        $simplex->set(CacheInterface::class, function() { return new ArrayCache(); });

        $cmsExtension = $simplex->get(CmsPageExtension::class);
        /* @var $cmsExtension CmsPageExtension */
        $pages = $cmsExtension->getCmsPagesByTag('foo', null, 'date', 'asc', 1, 1);

        $this->assertCount(1, $pages);
        $this->assertSame('/foo/bar/baz', $pages[0]->getUrl());

        $pages = $cmsExtension->getCmsPagesByTag('foo', null, 'date', 'desc', 1, 1);

        $this->assertCount(1, $pages);
        $this->assertSame('/foo/bar', $pages[0]->getUrl());

        $this->expectException(CMSException::class);
        $pages = $cmsExtension->getCmsPagesByTag('foo', null, 'date', 'foo');
    }

    public function testBlockSort()
    {
        $simplex = new Container([
            new TwigServiceProvider(),
            new CMSUtilsServiceProvider(),
            new StaticRegistryServiceProvider()
        ]);

        $simplex->set('CMS_ROOT', __DIR__.'/../fixtures/Loaders');
        $simplex->set('THEMES_URL', '/themes/');
        $simplex->set(CacheInterface::class, function() { return new ArrayCache(); });

        $cmsExtension = $simplex->get(CmsPageExtension::class);
        /* @var $cmsExtension CmsPageExtension */
        $blocks = $cmsExtension->getCmsBlocksByTag('bar', 'date', 'asc', 1, 1);

        $this->assertCount(1, $blocks);
        $this->assertSame('my_block', $blocks[0]->getId());

        $blocks = $cmsExtension->getCmsBlocksByTag('bar', 'date', 'desc', 1, 1);

        $this->assertCount(1, $blocks);
        $this->assertSame('logo', $blocks[0]->getId());

        $this->expectException(CMSException::class);
        $cmsExtension->getCmsBlocksByTag('bar', 'date', 'foo');
    }

}
