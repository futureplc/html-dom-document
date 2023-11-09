<?php

namespace Future\HTMLDocument\Tests\HTMLDocument;

use Future\HTMLDocument\HTMLElement;
use Future\HTMLDocument\HTMLNodeList;
use Future\HTMLDocument\HTMLText;
use PHPUnit\Framework\TestCase;

class HTMLNodeListTest extends TestCase
{
    /** @test */
    public function can_be_created_from_html()
    {
        $htmlNodeList = HTMLNodeList::fromString('<div></div><p></p>');

        $this->assertCount(2, $htmlNodeList);
    }
}
