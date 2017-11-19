<?php

namespace TheCodingMachine\CMS\StaticRegistry\DI;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Simplex\Container;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\CMS\StaticRegistry\Registry\StaticRegistry;
use TheCodingMachine\CMS\Theme\TwigThemeDescriptor;
use TheCodingMachine\TwigServiceProvider;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class StaticRegistryServiceProviderTest extends TestCase
{
    public function testServiceProvider()
    {
        $simplex = new Container();
        $simplex->register(new TwigServiceProvider());
        $simplex->register(new StaticRegistryServiceProvider());

        $simplex->set('CMS_ROOT', __DIR__.'/../fixtures/Loaders');
        $simplex->set(CacheInterface::class, function() { return new ArrayCache(); });

        $staticRegistry = $simplex->get(StaticRegistry::class);
        /* @var $staticRegistry StaticRegistry */
        $request = new ServerRequest([], [], new Uri('http://example.com/foo/bar'));
        $block = $staticRegistry->getPage($request);

        $theme = $block->getThemeDescriptor();
        $this->assertInstanceOf(TwigThemeDescriptor::class, $theme);

        // Let's check Twig is properly configured and can load Twig files from the themes directory.
        $twig = $simplex->get(\Twig_Environment::class);
        /* @var $twig \Twig_Environment */
        $this->assertTrue($twig->getLoader()->exists('foo_theme/index.twig'));
    }
}
