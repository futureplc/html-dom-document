<?php

namespace Future\HTMLDocument\Tests\HTMLDocument;

use DOMDocument;
use DOMElement;
use DOMNode;
use Future\HTMLDocument\Utility;
use PHPUnit\Framework\TestCase;

class UtilityTest extends TestCase
{
    /** @test */
    public function attributes_are_formatted_properly()
    {
        $this->assertSame('foo', Utility::attribute('foo'));
        $this->assertSame('foo="bar baz"', Utility::attribute('foo', ['bar baz']));
        $this->assertSame('foo=""', Utility::attribute('foo', []));
        $this->assertSame('foo', Utility::attribute('foo', true));
        $this->assertSame('', Utility::attribute('foo', false));
        $this->assertSame('foo="bar"', Utility::attribute('foo', 'bar'));
    }

    /** @test */
    public function multiple_attributes_are_formatted_properly()
    {
        $this->assertSame('foo class="bar baz" hello="world"', Utility::attributes([
            'foo' => true,
            'class' => ['bar', 'baz'],
            'hello' => 'world',
        ]));
    }

    /** @test */
    public function can_map_recursively()
    {
        $dom = new DOMDocument();
        $dom->loadHTML('<div><em>Item 1</em></div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $this->assertEquals("<div><em>Item 1</em></div>\n", $dom->saveHTML());

        Utility::nodeMapRecursive($dom, function (DOMNode $node): DOMNode {
            if ($node->nodeName === 'em') {
                return new DOMElement('strong', $node->nodeValue);
            }

            return $node;
        });

        $this->assertEquals("<div><strong>Item 1</strong></div>\n", $dom->saveHTML());
    }

    /** @test */
    public function can_count_root_nodes_from_html_string()
    {
        $this->assertEquals(1, Utility::countRootNodes('<div></div>'));
        $this->assertEquals(2, Utility::countRootNodes('<div></div><div></div>'));
        $this->assertEquals(3, Utility::countRootNodes('<div></div><div></div><div></div>'));
        $this->assertEquals(3, Utility::countRootNodes('Foo<div>Bar</div>Baz'));
    }

    /** @test */
    public function can_count_root_nodes_from_html5_void_elements()
    {
        $this->assertEquals(2, Utility::countRootNodes('<input><button></button>'));
    }
}
