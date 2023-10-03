<?php

namespace Future\HTMLDocument;

use DOMNode;
use DOMText;
use Future\HTMLDocument\Exceptions\HTMLException;
use InvalidArgumentException;

class HTMLText extends DOMText
{
    /**
     * Replace part of a text node with another full node, replacing it into the same parent node.
     */
    public function replaceTextWithNode(string $search, DOMNode $replace): HTMLText
    {
        if ($this->nodeType !== XML_TEXT_NODE) {
            throw new InvalidArgumentException('Node must be a text node');
        }

        HTMLException::assertHasParentNode($this, 'Cannot replace text node without a parent node');
        HTMLException::assertHasOwnerDocument($this, 'Cannot replace text node without an owner document');

        $beforeSearch = $search === '' ? $this->textContent : explode($search, $this->textContent)[0];
        $afterSearch = $search === '' ? $this->textContent : array_reverse(explode($search, $this->textContent, 2))[0];

        $fragment = $this->ownerDocument->createDocumentFragment();
        $fragment->appendChild($this->ownerDocument->createTextNode($beforeSearch));
        $fragment->appendChild($this->ownerDocument->importNode($replace, true));
        $fragment->appendChild($this->ownerDocument->createTextNode($afterSearch));
        $this->parentNode->replaceChild($fragment, $this);

        return $this;
    }
}
