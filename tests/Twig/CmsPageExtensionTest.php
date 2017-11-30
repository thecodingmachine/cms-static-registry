<?php

namespace TheCodingMachine\CMS\StaticRegistry\Twig;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Simplex\Container;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\CMS\Block\BlockRendererInterface;
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

}
