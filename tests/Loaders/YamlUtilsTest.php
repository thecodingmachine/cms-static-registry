<?php

namespace TheCodingMachine\CMS\StaticRegistry\Loaders;

use PHPUnit\Framework\TestCase;

class YamlUtilsTest extends TestCase
{
    public function testMergeArrays()
    {
        $arrayMerger = new YamlUtils();
        $result = $arrayMerger->mergeArrays([
            'title' => 'foo',
            'website' => 'example.com',
            'context' => [
                'foo' => 'bar',
                'baz' => [ 'yop' ],
                'foobar' => [
                    'one' => 'two'
                ]
            ]
        ], [
            'title' => 'bar',
            'lang' => 'fr',
            'context' => [
                'foo' => 'baz',
                'baz' => [ 'yop2' ],
                'foobar' => [
                    'three' => 'four'
                ],
            ],
        ], [
            'title' => YamlUtils::OVERRIDE,
            'lang' => YamlUtils::OVERRIDE,
            'website' => YamlUtils::OVERRIDE,
            'menu_css_class' => YamlUtils::OVERRIDE,
            'meta_title' => YamlUtils::OVERRIDE,
            'meta_description' => YamlUtils::OVERRIDE,
            'theme' => YamlUtils::OVERRIDE,
            'template' => YamlUtils::OVERRIDE,
            'context' => YamlUtils::MERGE_ARRAY,
        ]);

        $this->assertSame([
            'title' => 'bar',
            'lang' => 'fr',
            'context' => [
                'foo' => 'baz',
                'baz' => [ 'yop', 'yop2' ],
                'foobar' => [
                    'three' => 'four',
                    'one' => 'two',
                ]
            ],
            'website' => 'example.com',
        ], $result);
    }
}
