<?php

namespace Future\HTMLDocument\Concerns;

use Future\HTMLDocument\HTMLDocument;
use Future\HTMLDocument\HTMLNodeList;
use Future\HTMLDocument\HTMLXPath;

trait HasXPathQuerySelectors
{
    abstract protected function getDocument(): HTMLDocument;

	public function query($xpathSelector): HTMLNodeList
	{
        $xpath = new HTMLXPath($this->getDocument());

		return $xpath->query($xpathSelector, $this->getDocument());
	}

	public function evaluate($xpathSelector): HTMLNodeList
	{
        $xpath = new HTMLXPath($this->getDocument());

		return $xpath->query($xpathSelector, $this->getDocument());
	}
}
