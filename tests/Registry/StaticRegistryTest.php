<?php

namespace TheCodingMachine\CMS\StaticRegistry\Registry;

use PHPUnit\Framework\TestCase;
use Simplex\Container;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\CMS\Theme\TwigThemeDescriptor;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

class StaticRegistryTest extends TestCase
{
    public function testStaticRegistry()
    {
        $simplex = new Container();
        $blockRegistry = new BlockRegistry(__DIR__ . '/../fixtures/Loaders/blocks', $simplex, new ArrayCache());
        $themeRegistry = new ThemeRegistry(__DIR__ . '/../fixtures/Loaders/themes', __DIR__ . '/../fixtures/Loaders/sub_themes', $simplex, new ArrayCache(), $blockRegistry);
        $pageRegistry = new PageRegistry(__DIR__ . '/../fixtures/Loaders/pages', new ArrayCache());

        $staticPageRegistry = new StaticRegistry($pageRegistry, $themeRegistry);
        $request = new ServerRequest([], [], new Uri('http://example.com/foo/bar'));
        $block = $staticPageRegistry->getPage($request);

        $theme = $block->getThemeDescriptor();
        $this->assertInstanceOf(TwigThemeDescriptor::class, $theme);
    }
}
