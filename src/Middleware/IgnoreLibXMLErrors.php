<?php

namespace Future\HTMLDocument\Middleware;

/**
 * Ignore and clear any internal LibXML errors, such as it not recognising
 * valid HTML-5 elements when parsing the document.
 */
class IgnoreLibXMLErrors extends AbstractMiddleware
{
    public function beforeLoadHTML(string $source): string
    {
        libxml_use_internal_errors(false);

        return $source;
    }

    public function afterLoadHTML(): void
    {
        libxml_clear_errors();
    }
}
