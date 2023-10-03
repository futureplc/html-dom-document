<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\Middleware\InjectDefaultDoctype;
use PHPUnit\Framework\TestCase;

class InjectDefaultDoctypeTest extends TestCase
{
    /** @test */
    public function implied_html5_doctype_is_not_in_output()
    {
        $html = '<html><head><title>Test</title></head><body></body></html>';
        $dom = HTMLDocument::fromHTML($html, middleware: false);
        $dom = $dom->withMiddleware(new InjectDefaultDoctype($dom));

        $this->assertSame($html, $dom->saveHTML());
    }

    /** @test */
    public function default_doctype_is_injected()
    {
        $originalHtml = '<html><head><title>Test</title></head><body></body></html>';
        $expectedHtml = '<!DOCTYPE html><html><head><title>Test</title></head><body></body></html>';

        $dom = HTMLDocument::fromHTML($originalHtml);
        $middleware = new InjectDefaultDoctype($dom);

        $this->assertSame($expectedHtml, $middleware->beforeLoadHTML($originalHtml));
    }
}
