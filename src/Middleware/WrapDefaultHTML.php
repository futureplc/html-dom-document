<?php

namespace Future\HTMLDocument\Middleware;

use Future\HTMLDocument\Utility;

/**
 * Wraps the default HTML internally to prevent DOMDocument applying
 * an implicit <p> tag to documents with no root element.
 */
class WrapDefaultHTML extends AbstractMiddleware
{
    protected bool $hasMultipleRootNodes = false;

    public function beforeLoadHTML(string $source): string
    {
        if (Utility::countRootNodes($source) > 1) {
            $this->hasMultipleRootNodes = true;

            return Utility::wrap($source);
        }

        return $source;
    }

    public function afterSaveHTML(string $source): string
    {
        if ($this->hasMultipleRootNodes) {
            return Utility::unwrap($source);
        }

        return $source;
    }
}
