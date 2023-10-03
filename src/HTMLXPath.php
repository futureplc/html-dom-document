<?php

namespace Future\HTMLDocument;

use DOMNode;
use DOMXPath;
use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\HTMLNodeList;

/**
 * @property-read HTMLDocument $document
 */
class HTMLXPath extends DOMXPath
{
    public function evaluate(string $expression, ?DOMNode $contextNode = null, bool $registerNodeNS = true): HTMLNodeList
    {
        $nodes = parent::evaluate($expression, $contextNode, $registerNodeNS);

        if ($nodes === false) {
            return new HTMLNodeList();
        }

        return HTMLNodeList::fromDOMNodeList($nodes, $this->document);
    }

    public function query(string $expression, ?DOMNode $contextNode = null, bool $registerNodeNS = true): HTMLNodeList
    {
        $nodes = parent::query($expression, $contextNode, $registerNodeNS);

        if ($nodes === false) {
            return new HTMLNodeList();
        }

        return HTMLNodeList::fromDOMNodeList($nodes, $this->document);
    }
}
