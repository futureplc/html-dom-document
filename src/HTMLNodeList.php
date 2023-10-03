<?php

namespace Future\HTMLDocument;

use DOMNode;
use Countable;
use ArrayAccess;
use DOMNodeList;
use Traversable;
use ArrayIterator;
use DOMException;
use IteratorAggregate;
use Future\HTMLDocument\HTMLElement;

/** @template-implements ArrayAccess<int, HTMLElement> */
class HTMLNodeList extends DOMNodeList implements IteratorAggregate, ArrayAccess, Countable, Traversable
{
    /** @var HTMLElement[] */
    protected array $elements = [];

    /**
     * @psalm-suppress NonInvariantPropertyType
     *
     * @deprecated Cannot be overridden due to DOMNodeList's readonly quriks, so should be avoided. Use the count() method instead.
     *
     * @todo Find a way to set the length of the list in the constructor
     * (unfortunately it's readonly but can't be set due to DOMNodeList's instantiation quirks).
     */
    public int $length;

    /** @param HTMLElement[] $elements */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    public static function fromDOMNodeList(DOMNodeList $list, HTMLDocument $dom = null): HTMLNodeList
    {
        $dom ??= new HTMLDocument();
        $nodes = [];

        /** @var DOMNode[] $list */
        foreach ($list as $node) {
            try {
                $newNode = $dom->createElement($node->nodeName, $node->nodeValue);

                if (!empty($node->parentNode)) {
                    $node->parentNode->replaceChild($newNode, $node);
                }

                $nodes[] = $newNode;
            } catch (DOMException $e) {
                // Sometimes a query is made that returns something which is not
                // an element, such as a <!-- comment -->, and still needs to
                // be returned and usable without throwing an exception.
                $nodes[] = $node;
            }
        }

        return new HTMLNodeList($nodes);
    }

    public function count(): int
    {
        return count($this->elements);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    public function item(int $index): ?HTMLElement
    {
        return $this->offsetExists($index) ? $this->offsetGet($index) : null;
    }

    /** @return HTMLElement[] */
    public function toArray(): array
    {
        return $this->elements;
    }

    public function offsetSet($offset, $value): void
    {
        $this->elements[$offset] = $value;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->elements[$offset]);
    }

    public function offsetUnset($offset): void
    {
        unset($this->elements[$offset]);
    }

    public function offsetGet($offset): ?HTMLElement
    {
        if (!isset($this->elements[$offset])) {
            return null;
        }

        return $this->elements[$offset];
    }
}
