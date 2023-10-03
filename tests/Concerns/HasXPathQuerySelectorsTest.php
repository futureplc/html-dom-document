<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;
use PHPUnit\Framework\TestCase;

class HasXPathQuerySelectorsTest extends TestCase
{
    /** @test */
    public function documents_can_extract_with_xpath_selectors()
    {
        $dom = HTMLDocument::loadFromFile(__DIR__ . '/../fixtures/example.html');

        $this->assertSame('Meta title', $dom->query('//title')->item(0)->textContent);
    }
}
