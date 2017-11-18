<?php

namespace TheCodingMachine\CMS\StaticRegistry\Menu;


use PHPUnit\Framework\TestCase;

class MenuRegistryTest extends TestCase
{
    public function testMenuRegistry()
    {
        $menuRegistry = new MenuRegistry();

        // FIXME: l'ordre devrait être gardé si même priorité
        // Utiliser une SplPriorityQueue instead!!!
        $menuRegistry->registerMenuItem(['foo', 'bar', 'baz'], '/foo/bar/baz', 1.0);
        $menuRegistry->registerMenuItem(['foo', 'bar', 'baz2'], '/foo/bar/baz2', 0.0);
        $menuRegistry->registerMenuItem(['foo', 'bar', 'baz3'], '/foo/bar/baz3', 0.0, 'cssClass');

        $rootMenu = $menuRegistry->getRootMenu();

        $this->assertSame('root', $rootMenu->getLabel());
        $this->assertCount(1, $rootMenu->getChildren());
        $foo = $rootMenu->getChildren()[0];
        $this->assertSame('foo', $foo->getLabel());
        $this->assertCount(1, $foo->getChildren());
        $bar = $foo->getChildren()[0];
        $this->assertSame('bar', $bar->getLabel());
        $this->assertCount(3, $bar->getChildren());
        $barChildren = $bar->getChildren();
        $this->assertCount(3, $barChildren);
        $baz2 = $barChildren[0];
        $this->assertSame('baz2', $baz2->getLabel());
        $this->assertCount(0, $baz2->getChildren());
        $baz3 = $barChildren[1];
        $this->assertSame('baz3', $baz3->getLabel());
        $this->assertCount(0, $baz3->getChildren());
        $this->assertSame('cssClass', $baz3->getCssClass());
        $baz = $barChildren[2];
        $this->assertSame('baz', $baz->getLabel());
        $this->assertCount(0, $baz->getChildren());
        $this->assertSame('/foo/bar/baz', $baz->getUrl());
        $this->assertTrue($baz->isActive('/foo/bar/baz'));
        $this->assertFalse($baz->isExtended('/foo/bar/baz'));
        $this->assertTrue($foo->isExtended('/foo/bar/baz'));
    }
}
