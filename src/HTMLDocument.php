<?php

namespace Future\HTMLDocument;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Future\HTMLDocument\Concerns\CanManipulateDocument;
use Future\HTMLDocument\Concerns\HasCssQuerySelectors;
use Future\HTMLDocument\Concerns\HasXPathQuerySelectors;
use Future\HTMLDocument\Exceptions\HTMLException;
use Future\HTMLDocument\Middleware\AbstractMiddleware;

/**
 * @property-read ?HTMLElement $documentElement
 * @property-read ?HTMLElement $firstElementChild
 * @property-read ?HTMLElement $lastElementChild
 * @property-read ?HTMLElement $parentNode
 * @property-read HTMLNodeList $childNodes
 * @property-read ?HTMLElement $firstChild
 * @property-read ?HTMLElement $lastChild
 * @property-read ?HTMLElement $previousSibling
 * @property-read ?HTMLElement $nextSibling
 * @property-read ?HTMLDocument $ownerDocument
 *
 * @method ?HTMLElement getElementById(string $elementId)
 * @method HTMLNodeList getElementsByTagName(string $qualifiedName)
 * @method HTMLNodeList getElementsByTagNameNS(?string $namespace, string $localName)
 */
class HTMLDocument extends DOMDocument
{
    use HasCssQuerySelectors;
    use HasXPathQuerySelectors;
    use CanManipulateDocument;

    /** @var AbstractMiddleware[] */
    private array $middleware = [];

    public function __construct(string $version = '1.0', string $encoding = 'UTF-8')
    {
        parent::__construct($version, $encoding);
        $this->registerNodeClass(DOMDocument::class, HTMLDocument::class);
        $this->registerNodeClass(DOMElement::class, HTMLElement::class);
        $this->registerNodeClass(DOMNode::class, HTMLElement::class);
        $this->registerNodeClass(DOMText::class, HTMLText::class);

        $this->preserveWhiteSpace = true;
        $this->formatOutput = false;

        $this->middleware[] = new Middleware\IgnoreLibXMLErrors($this);
        $this->middleware[] = new Middleware\WrapDefaultHTML($this);
        $this->middleware[] = new Middleware\InjectDefaultDoctype($this);
        $this->middleware[] = new Middleware\TrimHTML($this);
        foreach (['template', 'script', 'style', 'textarea'] as $tag) {
            $this->middleware[] = new Middleware\IgnoreHTMLTag($this, $tag);
        }
    }

    public static function fromHTML(string $html, bool $middleware = true, $options = 0, $libNoImplied = true): HTMLDocument
    {
        $dom = new HTMLDocument();

        if (! $middleware) {
            $dom = $dom->withoutMiddleware();
        }

        $dom->loadHTML($html, $options, $libNoImplied);

        return $dom;
    }

    public function withMiddleware(AbstractMiddleware $middleware): HTMLDocument
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /** @param ?class-string $middleware */
    public function withoutMiddleware(?string $middlewareToRemove = null): HTMLDocument
    {
        if ($middlewareToRemove === null) {
            $this->middleware = [];

            return $this;
        }

        $this->middleware = array_filter(
            $this->middleware,
            fn (AbstractMiddleware $middleware) => ! $middleware instanceof $middlewareToRemove,
        );

        return $this;
    }

    public static function loadFromString(string $string): HTMLDocument
    {
        $dom = new HTMLDocument();
        $dom->loadHTML($string);

        return $dom;
    }

    public static function loadFromFile(string $filePath): HTMLDocument
    {
        $dom = new HTMLDocument();
        $dom->loadHTMLFile($filePath);

        return $dom;
    }

    public function loadHTMLFile(string $filename, int $options = 0)
    {
        return $this->loadHTML(file_get_contents($filename), $options);
    }

    public function saveHTMLFile($filename): int|false
    {
        if (! is_writable($filename)) {
            return false;
        }
        $result = $this->saveHTML();
        file_put_contents($filename, $result);
        $bytesWritten = filesize($filename);

        if ($bytesWritten !== strlen($result)) {
            throw new HTMLException('File written was not the same size as the HTML.');
        }

        return $bytesWritten;
    }

    public function loadHTML(string $source, int $options = 0, $libNoImplied = true): bool
    {
        foreach ($this->middleware as $middleware) {
            if (method_exists($middleware, 'beforeLoadHTML')) {
                $source = $middleware->beforeLoadHTML($source);
            }
        }

        // DOMDocument doesn't like treating an empty string as valid HTML,
        // so we'll skip this step if it's empty.
        if (! empty(trim($source))) {
            // Uses the @ operator to suppress errors with DOMDocument, as it may
            // throw an error if invalid HTML is found, but continue to load
            // the resulting document as expected, working around them.
            if ($libNoImplied) {
                $source = @parent::loadHTML($source, $options | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            } else {
                $source = @parent::loadHTML($source, $options | LIBXML_HTML_NODEFDTD);
            }
        }

        foreach ($this->middleware as $middleware) {

            if (method_exists($middleware, 'afterLoadHTML')) {
                $middleware->afterLoadHTML();
            }
        }

        return is_string($source) ? true : $source;
    }

    public function saveHTML(?DOMNode $node = null): string
    {
        foreach (array_reverse($this->middleware) as $middleware) {
            if (method_exists($middleware, 'beforeSaveHTML')) {
                $middleware->beforeSaveHTML();
            }
        }

        $source = parent::saveHTML($node) ?: throw new HTMLException('Failed to save HTML');

        foreach (array_reverse($this->middleware) as $middleware) {
            if (method_exists($middleware, 'afterSaveHTML')) {
                $source = $middleware->afterSaveHTML($source);
            }
        }

        return trim($source);
    }

    public function __toString(): string
    {
        return $this->saveHTML();
    }

    protected function getDocument(): HTMLDocument
    {
        return $this;
    }

    protected function getCurrentNode(): DOMNode
    {
        return $this;
    }

    public function createElement(string $localName, string $value = ''): HTMLElement
    {
        $orphan = new HTMLElement($localName, $value);
        $fragment = $this->createDocumentFragment();
        $fragment->appendChild($orphan);

        /** @var HTMLElement $element */
        $element = $fragment->removeChild($orphan);

        return $element;
    }

    public function createElementFromNode(DOMNode $node): HTMLElement
    {
        $element = $this->createElement($node->nodeName);

        if ($node->hasAttributes()) {
            /** @psalm-suppress InvalidIterator */
            foreach ($node->attributes as $attribute) {
                /** @var DOMAttr $attribute */
                $element->setAttribute($attribute->nodeName, $attribute->nodeValue);
            }
        }

        if ($node->hasChildNodes()) {
            /** @psalm-suppress InvalidIterator */
            foreach ($node->childNodes as $child) {
                /** @var DOMNode $child */
                $element->appendChild($this->importNode($child, true));
            }
        }

        return $element;
    }

    public function createElementFromHTML(string $html): HTMLElement
    {
        $dom = new HTMLDocument();
        $dom->withoutMiddleware();
        $dom->loadHTML($html);

        return $this->createElementFromNode($dom->documentElement);
    }

    public function isHtml5(): bool
    {
        /** @psalm-suppress TypeDoesNotContainType */
        if (empty($this->doctype)) {
            return false;
        }

        return strtolower($this->doctype->name) === 'html'
            && empty($this->doctype->publicId)
            && empty($this->doctype->systemId);
    }

    /**
     * Recursively map over the document and its children, allowing you to
     * inspect and modify any of the children nodes in the callback
     * along the way. Return `null` in a callback to skip a node.
     *
     * @psalm-param callable(DOMNode): ?DOMNode $callback
     * @return HTMLDocument
     */
    public function mapRecursive(callable $callback): HTMLDocument
    {
        return Utility::nodeMapRecursive($this, $callback);
    }
}
