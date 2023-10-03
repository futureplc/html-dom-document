<?php

namespace Future\HTMLDocument\Tests\HTMLDocument;

use DOMDocument;
use Future\HTMLDocument\HTMLDocument;
use PHPUnit\Framework\TestCase;
use Future\HTMLDocument\HTMLElement;

class HTMLDocumentTest extends TestCase
{
    /** @test */
    public function elements_can_be_created()
    {
        $dom = HTMLDocument::loadFromFile(__DIR__ . '/fixtures/example.html');
        $element = $dom->createElement('h1', 'New h1 title');

        $this->assertInstanceOf(HTMLElement::class, $element);
        $this->assertSame('New h1 title', $element->textContent);
        $this->assertSame('<h1>New h1 title</h1>', $element->saveHTML());
    }

    /** @test */
    public function elements_can_be_created_from_dom_nodes()
    {
        $domDoc = new DOMDocument();
        $domElement = $domDoc->createElement('h1', 'New h1 title');
        $domElement->setAttribute('id', 'new-h1');

        $htmlDoc = new HTMLDocument();
        $htmlElement = $htmlDoc->createElementFromNode($domElement);

        $this->assertInstanceOf(HTMLElement::class, $htmlElement);
        $this->assertSame('New h1 title', $htmlElement->textContent);
        $this->assertSame('new-h1', $htmlElement->getAttribute('id'));
        $this->assertSame('<h1 id="new-h1">New h1 title</h1>', $htmlElement->saveHTML());
    }

    /** @test */
    public function elements_can_be_created_from_strings()
    {
        $htmlDoc = new HTMLDocument();
        $htmlElement = $htmlDoc->createElementFromHTML('<h1 id="new-h1">New h1 title</h1>');

        $this->assertInstanceOf(HTMLElement::class, $htmlElement);
        $this->assertSame('New h1 title', $htmlElement->textContent);
        $this->assertSame('new-h1', $htmlElement->getAttribute('id'));
        $this->assertSame('<h1 id="new-h1">New h1 title</h1>', $htmlElement->saveHTML());
    }

    /** @test */
    public function unloaded_documents_are_not_html5()
    {
        $this->assertFalse((new HTMLDocument())->isHtml5());
    }

    /** @test */
    public function can_tell_if_html5_document()
    {
        $this->assertTrue(HTMLDocument::fromHTML('')->isHtml5());
        $this->assertTrue(HTMLDocument::fromHTML('<!doctype html><html></html>')->isHtml5());
        $this->assertFalse(HTMLDocument::fromHTML('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html></html>')->isHtml5());
        $this->assertFalse(HTMLDocument::fromHTML('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"><html></html>')->isHtml5());
        $this->assertFalse(HTMLDocument::fromHTML('<!doctype test><html></html>')->isHtml5());
    }

    /** @test */
    public function can_load_html_with_multiple_root_nodes()
    {
        $dom = HTMLDocument::fromHTML('<h1>One</h1><h2>Two</h2>Three');

        $this->assertSame('<h1>One</h1><h2>Two</h2>Three', $dom->saveHTML());
    }
}
