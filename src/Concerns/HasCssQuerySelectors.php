<?php

namespace Future\HTMLDocument\Concerns;

use DOMXPath;
use DOMNodeList;
use Future\HTMLDocument\HTMLElement;
use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\HTMLNodeList;
use Symfony\Component\CssSelector\CssSelectorConverter;

trait HasCssQuerySelectors
{
    abstract protected function getDocument(): HTMLDocument;

    public function querySelector(string $cssSelector): ?HTMLElement
    {
        $converter = new CssSelectorConverter();
        $xpath = new DOMXPath($this->getDocument());

        /** @var DOMNodeList $nodes */
        $nodes = $xpath->query($converter->toXPath($cssSelector));

        if ($nodes === false || $nodes->count() === 0) {
            return null;
        }

        return $this->getDocument()->createElementFromNode($nodes->item(0));
    }

    public function querySelectorAll(string $cssSelector): HTMLNodeList
    {
        $converter = new CssSelectorConverter();
        $xpath = new DOMXPath($this->getDocument());

        /** @var DOMNodeList $nodes */
        $nodes = $xpath->query($converter->toXPath($cssSelector));

        if ($nodes === false) {
            return new HTMLNodeList();
        }

        return HTMLNodeList::fromDOMNodeList($nodes, $this->getDocument());
    }
}
