<?php

namespace Future\HTMLDocument;

use DOMAttr;
use DOMElement;
use DOMNode;
use Future\HTMLDocument\Concerns\CanCompareDocument;
use Future\HTMLDocument\Concerns\CanManipulateDocument;
use Future\HTMLDocument\Concerns\HasCssQuerySelectors;
use Future\HTMLDocument\Concerns\HasXPathQuerySelectors;
use Future\HTMLDocument\Exceptions\HTMLException;

/**
 * @property-read ?HTMLElement $parentNode
 * @property-read HTMLNodeList $childNodes
 * @property-read ?HTMLElement $firstChild
 * @property-read ?HTMLElement $lastChild
 * @property-read ?HTMLElement $previousSibling
 * @property-read ?HTMLElement $nextSibling
 * @property-read ?HTMLDocument $ownerDocument
 */
class HTMLElement extends DOMElement
{
    use HasCssQuerySelectors;
    use HasXPathQuerySelectors;
    use CanManipulateDocument;
    use CanCompareDocument;

    public static function fromNode(DOMNode $node): HTMLElement
    {
        return HTMLDocument::fromHTML('')->createElementFromNode($node);
    }

    public static function fromHTML(string $html): HTMLElement
    {
        return HTMLDocument::fromHTML('')->createElementFromHTML($html);
    }

    /** @return string[] */
    public function getClassList(): array
    {
        if (! $this->hasAttribute('class')) {
            return [];
        }

        $classes = $this->getAttribute('class');

        return explode(' ', $classes);
    }

    public function setClassList(array $classes): void
    {
        $this->setAttribute('class', implode(' ', $classes));
    }

    /** @psalm-suppress MethodSignatureMismatch */
    public function setAttribute(string $qualifiedName, string|array $value): DOMAttr|bool
    {
        $value = is_array($value) ? implode(' ', $value) : $value;

        return parent::setAttribute($qualifiedName, $value);
    }

    public function hasClass(string $class): bool
    {
        return in_array($class, $this->getClassList());
    }

    public function addClass(string $class): void
    {
        $classes = $this->getClassList();

        if (in_array($class, $classes)) {
            return;
        }

        $classes[] = $class;

        $this->setAttribute('class', implode(' ', $classes));
    }

    public function removeClass(string $class): void
    {
        $classes = $this->getClassList();

        if (! in_array($class, $classes, true)) {
            return;
        }

        $classes = array_filter($classes, function ($item) use ($class) {
            return $item !== $class;
        });

        if (empty($classes)) {
            $this->removeAttribute('class');
        } else {
            $this->setAttribute('class', implode(' ', $classes));
        }
    }

    public function toggleAttribute(string $name): void
    {
        if ($this->hasAttribute($name)) {
            $this->removeAttribute($name);
        } else {
            $this->setAttribute($name, '');
        }
    }

    public function isVoidElement(): bool
    {
        return in_array($this->nodeName, ['area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr']);
    }

    public function getOuterHTML(): string
    {
        $attributes = Utility::attributes($this->getAttributes());

        if (! empty($attributes)) {
            $attributes = ' ' . $attributes;
        }

        if ($this->isVoidElement()) {
            if ($this->getDocument()->isHtml5()) {
                return "<{$this->nodeName}{$attributes}>";
            }

            return "<{$this->nodeName}{$attributes} />";
        }

        return "<{$this->nodeName}{$attributes}>{$this->getInnerHTML()}</{$this->nodeName}>";
    }

    public function getInnerHTML(): string
    {
        if ($this->firstChild === null) {
            return '';
        }

        $result = '';

        if ($this->hasChildNodes()) {
            /** @psalm-suppress InvalidIterator */
            foreach ($this->childNodes as $child) {
                /** @var DOMNode $child */
                if ($child->nodeType === XML_TEXT_NODE) {
                    $result .= $child->nodeValue;
                } else {
                    $result .= $this->getDocument()->saveHTML($child);
                }
            }
        }

        return $result;
    }

    public function replace(DOMNode $node): static
    {
        HTMLException::assertHasParentNode($this, 'Cannot replace a node in its parent if there is no parent node');

        $this->parentNode->replaceChild($node, $this);

        return $this;
    }

    public function saveHTML(): string
    {
        return $this->getDocument()->saveHTML($this);
    }

    public function __toString(): string
    {
        return $this->saveHTML();
    }

    protected function getDocument(): HTMLDocument
    {
        return $this->ownerDocument ?? new HTMLDocument();
    }

    protected function getCurrentNode(): DOMNode
    {
        return $this;
    }

    /**
     * @return string[]
     */
    public function getAttributes(): array
    {
        $attributes = [];

        if (empty($this->attributes)) {
            return [];
        }

        /** @psalm-suppress InvalidIterator */
        foreach ($this->attributes as $attributeName => $attribute) {
            $attributes[$attributeName] = $attribute->value ?? null;
        }

        return $attributes;
    }

    /**
     * Recursively map over an HTMLElement and its children, allowing you to
     * inspect and modify any of the children nodes in the callback
     * along the way. Return `null` in a callback to skip a node.
     *
     * @psalm-param callable(DOMNode): ?DOMNode $callback
     * @return HTMLElement
     */
    public function mapRecursive(callable $callback): HTMLElement
    {
        Utility::nodeMapRecursive($this, $callback);

        return $this;
    }
}
