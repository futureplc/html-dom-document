<?php

namespace Future\HTMLDocument;

use DOMDocument;
use DOMNode;

class Utility
{
    /** Turn an array into a string of HTML attributes. */
    public static function attributes(array $attributes): string
    {
        $attributeStrings = [];

        foreach ($attributes as $attributeName => $value) {
            $attributeStrings[] = Utility::attribute($attributeName, $value);
        }

        return join(' ', $attributeStrings);
    }

    public static function attribute(string $attributeName, mixed $value = null): string
    {
        if (is_array($value)) {
            return $attributeName . '="' . htmlspecialchars(join(' ', $value)) . '"';
        }

        if (is_bool($value)) {
            return $value ? htmlspecialchars($attributeName) : '';
        }

        if (empty($value)) {
            return htmlspecialchars($attributeName);
        }

        return $attributeName . '="' . htmlspecialchars($value) . '"';
    }

    /**
     * Recursively map over a DOMNode and its children, allowing you to
     * inspect and modify any of the children nodes in the callback
     * along the way. Return `null` in a callback to skip a node.
     *
     * @template T
     * @psalm-param T $node
     * @psalm-param callable(DOMNode): ?T $callback
     * @return T
     */
    public static function nodeMapRecursive(DOMNode $node, callable $callback): DOMNode
    {
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $newChild = $callback($child);

                if ($newChild === null) {
                    continue;
                }

                if ($newChild !== $child) {
                    $node->replaceChild($newChild, $child);
                }

                static::nodeMapRecursive($child, $callback);
            }
        }

        return $callback($node) ?? $node;
    }

    /**
     * Count the number of root nodes in an HTML string.
     */
    public static function countRootNodes(string $html): int
    {
        $dom = new DOMDocument();
        $fragment = $dom->createDocumentFragment();
        $fragment->appendXML($html);

        return $fragment->childNodes->length;
    }

    public static function nodeContainsNode(DOMNode $parentNode, DOMNode $childNode)
    {
        if ($parentNode === $childNode) {
            return true;
        }

        foreach ($parentNode->childNodes as $child) {
            if ($child === $childNode || Utility::nodeContainsNode($child, $childNode)) {
                return true;
            }
        }

        return false;
    }
    
}
