<?php

namespace Future\HTMLDocument\Tests\HTMLDocument;

use Future\HTMLDocument\HTMLElement;
use Future\HTMLDocument\HTMLText;
use PHPUnit\Framework\TestCase;

class HTMLTextTest extends TestCase
{
    /** @test */
    public function can_replace_text_with_node()
    {
        $element = HTMLElement::fromHTML('<div></div>');

        /** @var HTMLText $textNode */
        $textNode = $element->ownerDocument->createTextNode('This is an example!');
        $element->appendChild($textNode);

        $this->assertEquals('<div>This is an example!</div>', $element->__toString());
        $textNode->replaceTextWithNode('example', HTMLElement::fromHTML('<strong>example</strong>'));
        $this->assertEquals('<div>This is an <strong>example</strong>!</div>', $element->__toString());
    }
}
