<?php

namespace TheCodingMachine\CMS\StaticRegistry\Registry;


use PHPUnit\Framework\TestCase;
use Simplex\Container;
use Symfony\Component\Cache\Simple\ArrayCache;
use TheCodingMachine\CMS\StaticRegistry\Loaders\SubTheme;
use TheCodingMachine\CMS\StaticRegistry\Loaders\Theme;
use TheCodingMachine\CMS\Theme\SubThemeDescriptor;
use TheCodingMachine\CMS\Theme\TwigThemeDescriptor;

class ThemeRegistryTest extends TestCase
{

    public function testGetTheme()
    {
        $simplex = new Container();
        $simplex->set('bar_theme', function () {
            return new Theme(__DIR__ . '/bar_theme', []);
        });
        $blockRegistry = new BlockRegistry(__DIR__ . '/../fixtures/Loaders/blocks', $simplex, new ArrayCache());
        $themeRegistry = new ThemeRegistry(__DIR__ . '/../fixtures/Loaders/public/themes', __DIR__ . '/../fixtures/Loaders/sub_themes', $simplex, new ArrayCache(), $blockRegistry);
        $theme = $themeRegistry->getThemeDescriptor('foo_theme');
        /* @var $theme TwigThemeDescriptor */
        $this->assertInstanceOf(TwigThemeDescriptor::class, $theme);
        $this->assertSame('index.twig', $theme->getTemplate());
        $this->assertSame('foo_theme', $theme->getConfig()['theme']);

        $theme = $themeRegistry->getThemeDescriptor('foo_theme');
        $this->assertSame('index.twig', $theme->getTemplate());
        $this->assertSame('foo_theme', $theme->getConfig()['theme']);

        $theme = $themeRegistry->getThemeDescriptor('bar_theme');
        $this->assertSame('index.twig', $theme->getTemplate());
        $this->assertSame('bar_theme', $theme->getConfig()['theme']);

        $this->expectException(ThemeNotFoundException::class);
        $themeRegistry->getThemeDescriptor('no_theme');
    }

    public function testGetSubTheme()
    {
        $simplex = new Container();
        $simplex->set('bar_subtheme', function () {
            return new SubTheme('bar_subtheme', 'foo_theme', []);
        });
        $simplex->set('baz_theme', function () {
            return new TwigThemeDescriptor('index.twig', []);
        });
        $blockRegistry = new BlockRegistry(__DIR__ . '/../fixtures/Loaders/blocks', $simplex, new ArrayCache());
        $themeRegistry = new ThemeRegistry(__DIR__ . '/../fixtures/Loaders/public/themes', __DIR__ . '/../fixtures/Loaders/sub_themes', $simplex, new ArrayCache(), $blockRegistry);
        $theme = $themeRegistry->getThemeDescriptor('left block');
        /* @var $theme SubThemeDescriptor */
        $this->assertInstanceOf(SubThemeDescriptor::class, $theme);
        $this->assertSame('index.twig', $theme->getThemeDescriptor()->getThemeDescriptor()->getTemplate());
        $this->assertSame('foo_theme', $theme->getThemeDescriptor()->getThemeDescriptor()->getConfig()['theme']);

        $theme = $themeRegistry->getThemeDescriptor('bar_subtheme');
        $this->assertSame('index.twig', $theme->getThemeDescriptor()->getTemplate());
        $this->assertSame('foo_theme', $theme->getThemeDescriptor()->getConfig()['theme']);

        $theme = $themeRegistry->getThemeDescriptor('baz_theme');
        $this->assertSame($simplex['baz_theme'], $theme);
    }

    public function testGetDuplicateSubTheme()
    {
        $simplex = new Container();
        $blockRegistry = new BlockRegistry(__DIR__ . '/../fixtures/Loaders/blocks', $simplex, new ArrayCache());
        $themeRegistry = new ThemeRegistry(__DIR__ . '/../fixtures/Loaders/public/themes', __DIR__ . '/../fixtures/Loaders/duplicate_sub_themes', $simplex, new ArrayCache(), $blockRegistry);
        $this->expectException(DuplicateSubThemeException::class);
        $themeRegistry->getThemeDescriptor('foo');
    }
}
