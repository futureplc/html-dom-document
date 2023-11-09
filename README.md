# A drop-in replacement for DOMDocument that handles HTML5 documents gracefully

[![Latest Version on Packagist](https://img.shields.io/packagist/v/futureplc/html-dom-document.svg?style=flat-square)](https://packagist.org/packages/futureplc/html-dom-document)
[![Tests](https://img.shields.io/github/actions/workflow/status/futureplc/html-dom-document/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/futureplc/html-dom-document/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/futureplc/html-dom-document.svg?style=flat-square)](https://packagist.org/packages/futureplc/html-dom-document)

The HTMLDocument package has one primary purpose: to act as a stand-in replacement for the `DOMDocument` and related DOM classes that come with PHP's core libxml extension.

> [!] There are other, recommended ways if you're writing new code to parse HTML content and don't need a drop-in replacement for `DOMDocument`, such as for a legacy application.
> Instead, onsider using a package like the [Symfony DOM Crawler component].

While the DOM-related classes with PHP are a great way to parse XML, they quickly fall apart when trying to parse HTML. The main problems are:

- libxml, and `DOMDocument` as a result, are not well-suited to parsing HTML5, as HTML5 is not necessarily valid XML. It can work with HTML5 just fine, but it needs a bit of finessing to get it working correctly
- The interfaces are not very intuitive, often requiring 5+ lines of code to perform simple operations like making an instance of a `DOMElement`
- The interface is inundated with legacy techniques, such as falsey return values on failures instead of sensible defaults, which makes it challenging to use alongside static analysis tools
- There are a lot of features missing that people working with HTML often use nowadays, such as querying with CSS selectors, manipulating attributes and equivalents of some JavaScript functions to get commonly needed things like `innerHTML` and `outerHTML`

This package provides a series of classes to replace the DOM ones in a backward-compatible fashion but with a tighter interface and additional utilities bundled in to make working with HTML a breeze. These classes will return instances of the equivalent `HTML*` class instead of the `DOM*` one:

- `DOMDocument` -> `HTMLDocument`
- `DOMElement` -> `HTMLElement`
- `DOMNode` -> `HTMLElement`
- `DOMText` -> `HTMLText`
- `DOMNodeList` -> `HTMLNodeList`
- `DOMXPath` -> `HTMLXPath`

## Installation

You can install the package via Composer:

```bash
composer require futureplc/html-dom-document
```

## Features

### Sensible return values

There's nothing more annoying than having to check union types on every operation because of PHP's legacy of using falsey return types. We've sorted this by making sure there are sensible defaults:

- If a return value expects `DOMNodeList` or `false`, we'll return an empty `DOMNodeList` if there are no values to return
-  If a return value could be a `string` or `false`, we'll either throw an exception on failure or return an empty string
- No more differentiating between `DOMNode` and `DOMElement`; we have a single `HTMLElement` class that handles all scenarios of the two combined

You'll notice this philosophy throughout the interface - if there's a sensible type to return, we'll ensure you get that instead of dealing with unions.

### Easily create HTML documents and elements

`DOMDocument` typically has a terse, antiquated interface that requires a lot of setup and repetition to do even basic and commonly needed tasks like creating a `DOMElement` class from a plain HTML string.

All the old `DOMDocument` style methods still work, so you can drop this package in as a replacement for existing `DOMDocument` implementations. However, we have added new ways to create HTML documents and elements without the verbosity usually required for some operations.

```php
$dom = new HTMLDocument(); $dom->loadHTML($html);
$dom = HTMLDocument::fromHTML($html);
$dom = HTMLDocument::loadFromFile($filePath);

$element = HTMLElement::fromNode($domNode);
$element = HTMLElement::fromHTML($html);

$element = $dom->createElement('p', 'This is a paragraph.');
$element = $dom->createElementFromNode($domNode);
$element = $dom->createElementFromHTML('<p>This is a paragraph.</p>');
```

### Additional behaviour to support HTML5

The majority of the custom behaviour to allow DOMDocument to parse any HTML string comes from a series of "middleware" classes that manipulate the HTML before it's loaded and before it's emitted as a plain HTML string again.

These middleware do various things, such as:
- Assuming HTML5 behaviour if no `<!doctype>` is present, by adding one
- Ignoring LibXML errors (as LibXML complains about certain HTML5 tags even though it can parse them properly)
- Treating `<template>` and `<script>` tags as verbatim so their contents aren't changed by the rest of the document

These will be enabled by default if you use the `HTMLDocument` class, but you can disable them if you like by calling the `withoutMiddleware()` method before loading the HTML.
Easily get the HTML string back

Getting a plain HTML string back out of `DOMDocument` can be a bit tricky if you need something specific like a specific element, so we have added some options to make it easier.

```php
$html = (string) $dom; // Cast the HTMLDocument to a string
$html = $dom->saveHTML();

$html = (string) $element; // Cast the HTMLElement to a string
$html = $element->saveHTML();
$html = $element->getInnerHTML(); // Gets the HTML of the element without the wrapping node
$html = $element->getOuterHTML(); // Gets the HTML of the element with the wrapping node
```

### Check if HTML5

If you need to know whether you're working with an HTML5 document or not, the `isHTML5()` method will tell you.

```php
$dom->isHtml5(); // true
```

If working with HTML5, you may want to know if a given node is a "void element," meaning it needs no closing tag. This can be checked with the `isVoidElement()` method.

```php
$element->isVoidElement(); // true
```

### Utility methods

There are a couple of additional utility methods to help build attribute strings from PHP arrays.

`Utility::attribute()` will take a single key/value pair and turn it into an HTML attribute, regardless of whether the value is a string, array, or boolean.

```php
Utility::attribute('class', ['foo', 'bar']); // class="foo bar"
Utility::attribute('id', 'baz'); // id="baz"
Utility::attribute('required', true); // disabled
```

`Utility::attributes()` will take this further by doing the same with an array of key/value pairs, turning them into an HTML attribute string altogether.

```php
Utility::attributes([
    'class' => ['foo', 'bar'],
    'id' => 'baz',
    'required' => true,
]);

// class="foo bar" id="baz" required
```

`Utility::nodeMapRecursive()` gives the ability to run a callback on every node in a document, including all child nodes. You can use this callback to inspect the nodes, modify them, replace one node with another entirely, or remove them from the document.

This is also available on `HTMLElement` and `HTMLDocument` objects through the `mapRecursive` method.

```php
$dom = HTMLDocument::fromHTML('<p><span>foo</span></p>');
$dom->mapRecursive(function ($node) {
    if ($node instanceof HTMLElement) {
        $node->setAttribute('class', 'bar');
    }
});

// <p class="bar"><span class="bar">foo</span></p>
```

### Working with CSS classes

The `HTMLElement` class has several methods to help you work with CSS classes.

```php
$element->setClassList(['foo', 'bar']);
$element->getClassList(); // ['foo', 'bar']
$element->hasClass('foo'); // true
$element->addClass('foo'); // ['foo', 'bar', 'baz']
$element->removeClass('baz'); // ['foo', 'bar']
```

### Toggling boolean attributes

In the case where you need to toggle some boolean attributes on or off, the `toggleAttribute()` method is available.

```php
$element = HTMLElement::fromString('<input type="checkbox">');
$element->toggleAttribute('checked'); // <input type="checkbox" checked>
$element->toggleAttribute('checked'); // <input type="checkbox">
```

### Querying on CSS selectors and XPath
Most people working with HTML know how to use most CSS selectors, but many have never touched XPath. We've added handy `querySelector()`  and `querySelectorAll()` methods to the `HTMLDocument` and `HTMLElement` classes, allowing you to use CSS selectors directly to get the needed elements, courtesy of the [Symfony CSS Selector](https://github.com/symfony/css-selector) package.

```php
$dom->querySelector('head > title'); // Returns the first `<title>` element
$dom->querySelectorAll('.foo'); // Returns all elements with the class `foo`
```

If you still need to work with XPath, there's now a convenient `query()` method available on both `HTMLDocument` and `HTMLElement` classes.

```php
$dom->query('//a'); // Returns all `<a>` elements
```

### Working with text nodes

Working with text nodes can be tricky if you ever want to change something in the text to another node entirely. The `replaceTextWithNode()` method on `HTMLText` lets you do just that.

This is particularly useful if you use the `Utility::nodeMapRecursive()` function, which will traverse through text nodes.

```php
$textNode->replaceTextWithNode('example', HTMLElement::fromHTML('<strong>example</strong>'));
```

## Drawbacks

Because of all the extra checks and type conversions, this package is a bit slower than the native `DOMDocument` classes. However, the difference is negligible in most cases, and the benefits of the additional features and ease of use far outweigh the performance hit unless you are processing millions of large HTML documents at once.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Future PLC](https://github.com/futureplc)
- [Liam Hammett](https://github.com/imliam)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
