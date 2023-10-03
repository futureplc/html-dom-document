<?php

namespace Future\HTMLDocument\Middleware;

/**
 * Trim additional whitespaces added before/after the output of the HTML,
 * which is added by DOMDocument by default.
 */
class TrimHTML extends AbstractMiddleware
{
    public function beforeLoadHTML(string $source): string
    {
        return trim($source);
    }
}
