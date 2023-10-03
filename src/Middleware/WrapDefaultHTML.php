<?php

namespace Future\HTMLDocument\Middleware;

use Future\HTMLDocument\Utility;

/**
 * Wraps the default HTML internally to prevent DOMDocument applying
 * an implicit <p> tag to documents with no root element.
 */
class WrapDefaultHTML extends AbstractMiddleware
{
    protected string $wrappingTag = 'wrap-for-multiple-root-nodes';
    protected bool $hasMultipleRootNodes = false;

    public function beforeLoadHTML(string $source): string
    {
        if (Utility::countRootNodes($source) > 1) {
            $this->hasMultipleRootNodes = true;
            return "<{$this->wrappingTag}>" . $source . "</{$this->wrappingTag}>";
        }

        return $source;
    }

    public function afterSaveHTML(string $source): string
    {
        if ($this->hasMultipleRootNodes) {
            return str_replace("<{$this->wrappingTag}>", '', str_replace("</{$this->wrappingTag}>", '', $source));
        }

        return $source;
    }
}
