<?php

namespace Future\HTMLDocument\Middleware;

/**
 * Inject a default HTML-5 doctype for internal processing, if no doctype is present.
 * This allows us to use HTML-5 features without them being converted into HTML-4 compatible tags.
 */
class InjectDefaultDoctype extends AbstractMiddleware
{
    /**
     * Whether or not the current object had to have a doctype tag injected
     * into the start of it to preserve the document type as HTML.
     */
    private bool $hadDoctypeInjected = false;

    /**
     * The default doctype that will be added to the HTML during
     * processing, if none was supplied in the original source.
     */
    private string $defaultDocType = '<!DOCTYPE html>';

    public function beforeLoadHTML(string $source): string
    {
        if (!$this->hasDoctype($source)) {
            $this->hadDoctypeInjected = true;

            return $this->defaultDocType . $source;
        }

        return $source;
    }

    public function afterSaveHTML(string $source): string
    {
        if ($this->hadDoctypeInjected && is_string($source)) {
            return $this->stripDefaultDocType($source);
        }

        return $source;
    }

    /**
     * Checks whether or not the given HTML has a doctype.
     */
    private function hasDoctype(string $html): bool
    {
        return str_starts_with(strtoupper($html), '<!DOCTYPE');
    }

    /**
     * Strips the default docblock from the beginning of the string.
     */
    private function stripDefaultDocType(string $html): string
    {
        return array_reverse(explode($this->defaultDocType, $html, 2))[0];
    }
}
