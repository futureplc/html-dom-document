<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use PHPUnit\Framework\TestCase;
use Future\HTMLDocument\HTMLElement;
use Future\HTMLDocument\HTMLDocument;

class HasCssQuerySelectorsTest extends TestCase
{
    /** @test */
    public function documents_can_extract_with_css_query_selectors()
    {
        $dom = HTMLDocument::loadFromFile(__DIR__ . '/../fixtures/example.html');

        $this->assertSame('Meta title', $dom->querySelector('head > title')->textContent);
        $this->assertSame('Logo alt text', $dom->querySelector('#logo')->getAttribute('alt'));
        $this->assertSame('H1 title', $dom->querySelector('h1#title')->textContent);

        $this->assertSame(
            ['Foo', 'Bar', 'Baz'],
            array_map(
                fn (HTMLElement $element) => $element->textContent,
                $dom->querySelectorAll('li')->toArray(),
            )
        );

        $this->assertEmpty($dom->querySelectorAll('.does-not-exist')->toArray());
    }
}
