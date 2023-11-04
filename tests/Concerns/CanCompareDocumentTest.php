<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\HTMLElement;
use PHPUnit\Framework\TestCase;

class CanCompareDocumentTest extends TestCase
{
    /** @test */
    public function can_tell_if_one_node_contains_another_node()
    {
        $dom = HTMLDocument::loadFromFile(__DIR__ . '/../fixtures/example.html');
        $titleFromDom = $dom->getElementById('title');
        $separateTitleNode = new HTMLElement('h1', 'Hello World');

        $this->assertTrue($dom->contains($titleFromDom));
        $this->assertFalse($dom->contains($separateTitleNode));
    }
}
