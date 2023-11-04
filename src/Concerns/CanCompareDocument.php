<?php

namespace Future\HTMLDocument\Concerns;

use DOMNode;
use Future\HTMLDocument\Utility;

trait CanCompareDocument
{
    public function contains(DOMNode $node): bool
    {
        /** @var DOMNode $this */
        return Utility::nodeContainsNode($this, $node);
    }
}
