<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;
use PHPUnit\Framework\TestCase;

class CanManipulateDocumentTest extends TestCase
{
    /** @test */
    public function can_remove_selectors()
    {
        $dom = HTMLDocument::loadFromFile(__DIR__ . '/../fixtures/example.html');

        $this->assertCount(3, $dom->querySelectorAll('li')->toArray());
        $dom = $dom->withoutSelector('li');
        $this->assertCount(0, $dom->querySelectorAll('li')->toArray());
    }

    /** @test */
    public function can_remove_comments()
    {
        $dom = HTMLDocument::loadFromFile(__DIR__ . '/../fixtures/example.html');

        $this->assertStringContainsString('This is a comment', $dom->saveHTML());
        $dom->withoutComments();
        $this->assertStringNotContainsString('This is a comment', $dom->saveHTML());
    }
}
