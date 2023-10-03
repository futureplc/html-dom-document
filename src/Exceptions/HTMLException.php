<?php

namespace Future\HTMLDocument\Exceptions;

use DOMNode;
use Exception;

class HTMLException extends Exception
{
    public static function assertHasParentNode(DOMNode $node, ?string $message = null): void
    {
        if (empty($node->parentNode)) {
            throw new HTMLException('Node "<' . $node->nodeName . '>" must have a parent node' . ($message ? ": {$message}" : ''));
        }
    }

    public static function assertHasOwnerDocument(DOMNode $node, ?string $message = null): void
    {
        if (empty($node->ownerDocument)) {
            throw new HTMLException('Node "<' . $node->nodeName . '>" must have an owner document' . ($message ? ": {$message}" : ''));
        }
    }
}
