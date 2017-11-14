<?php

namespace TheCodingMachine\CMS\StaticRegistry\Loaders;

use PHPUnit\Framework\TestCase;

class SubThemeTest extends TestCase
{
    public function testLoadSubTheme()
    {
        $subTheme = SubTheme::fromFile(__DIR__ . '/../fixtures/Loaders/sub_themes/header_footer.yml');

        $this->assertSame('theme with header and footer only', $subTheme->getName());
        $this->assertSame('foo_theme', $subTheme->getParent());
        $this->assertArrayHasKey('header_left', $subTheme->getAssignations());
        $this->assertContains('logo', $subTheme->getAssignations()['header_left']);
    }

    public function testMissingField()
    {
        $this->expectException(UnableToLoadFileException::class);
        SubTheme::fromFile(__DIR__.'/../fixtures/Loaders/test_import_bad_subtheme.yml');
    }

    public function testMissingFile()
    {
        $this->expectException(UnableToLoadFileException::class);
        SubTheme::fromFile(__DIR__.'/../fixtures/not_exists.html');
    }
}
