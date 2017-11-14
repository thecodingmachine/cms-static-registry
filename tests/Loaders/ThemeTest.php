<?php

namespace TheCodingMachine\CMS\StaticRegistry\Loaders;

use Barclays\Model\BaseBlock;
use PHPUnit\Framework\TestCase;

class ThemeTest extends TestCase
{
    public function testLoadTheme()
    {
        $theme = Theme::fromDirectory(__DIR__ . '/../fixtures/Loaders/themes/foo_theme');

        $this->assertSame('foo_theme', $theme->getName());
        $this->assertSame(__DIR__ . '/../fixtures/Loaders/themes/foo_theme', $theme->getPath());
        $this->assertSame(['foo', 'bar'], $theme->getZones());
    }

    public function testMissingField()
    {
        $this->expectException(UnableToLoadFileException::class);
        Theme::fromDirectory(__DIR__.'/../fixtures/Loaders/test_import_bad.yml');
    }

    public function testMissingDir()
    {
        $this->expectException(UnableToLoadFileException::class);
        Theme::fromDirectory(__DIR__.'/../fixtures/Loaders/themes/not_exists.html');
    }

    public function testMissingFile()
    {
        $this->expectException(UnableToLoadFileException::class);
        Theme::fromDirectory(__DIR__.'/../fixtures/Loaders/themes');
    }
}
