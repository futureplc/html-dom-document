<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use PHPUnit\Framework\TestCase;
use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\Middleware\TrimHTML;

class TrimHTMLTest extends TestCase
{
    /** @test */
    public function implied_html5_doctype_is_not_in_output()
    {
        $html = "<html><head><title>Test</title></head><body></body></html>";

        $dom = HTMLDocument::fromHTML($html, middleware: false);
        $dom = $dom->withMiddleware(new TrimHTML($dom));
        $this->assertSame($html, $dom->saveHTML());
    }
}
