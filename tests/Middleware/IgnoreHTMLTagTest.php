<?php

namespace Future\HTMLDocument\Tests\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\Middleware\IgnoreHTMLTag;
use PHPUnit\Framework\TestCase;

class IgnoreHTMLTagTest extends TestCase
{
    /** @test */
    public function template_replacement()
    {
        $html = '<div><p>First</p><template class="testing"><p>Inside</p></template><p>Second</p></div>';
        $dom = new HTMLDocument();
        $middleware = new IgnoreHTMLTag($dom, 'template');
        $dom->withoutMiddleware()
            ->withMiddleware($middleware)
            ->loadHTML($html);

        $this->assertNotSame($html, $dom->withoutMiddleware()->saveHTML());
        $this->assertSame($html, $dom->withMiddleware($middleware)->saveHTML());
    }

    /** @test */
    public function script_replacement()
    {
        $html = '<script src="foo"></script>';
        $dom = new HTMLDocument();
        $middleware = new IgnoreHTMLTag($dom, 'script');
        $dom->withoutMiddleware()
            ->withMiddleware($middleware)
            ->loadHTML($html);

        $this->assertNotSame($html, $dom->withoutMiddleware()->saveHTML());
        $this->assertSame($html, $dom->withMiddleware($middleware)->saveHTML());
    }
}
