<?php

namespace Future\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;

/**
 * Replace the given tag with a <template> tag temporarily,
 * as DOMDocument will urlencode them by default which is
 * not behaviour we want as <template> tags can be very
 * important in some applications, such as using a
 * mustache-like syntax for Google AMP.
 */
class IgnoreHTMLTag extends AbstractMiddleware
{
    protected array $replacements = [];

    public function __construct(protected HTMLDocument $dom, protected string $tag)
    {
    }

    /** @link https://regex101.com/r/Sg2P90/1 */
    public function beforeLoadHTML(string $source): string
    {
        $tagRegex = preg_quote($this->tag, '/');

        preg_match_all("/<{$tagRegex}(.*?)>(.*?)<\/{$tagRegex}>/", $source, $matches);

        $this->replacements = [
            ...$this->replacements,
            ...$matches[0],
        ];

        foreach ($this->replacements as $index => $match) {
            $source = str_replace(
                $match,
                $this->getReplacementString($index),
                $source
            );
        }

        return $source;
    }

    public function afterSaveHTML(string $source): string
    {
        foreach ($this->replacements as $index => $match) {
            $source = str_replace(
                $this->getReplacementString($index),
                $match,
                $source
            );
        }

        return $source;
    }

    protected function getReplacementString(int $index): string
    {
        return '<!-- <template replacement-index="' . $index . '"> -->';
    }
}
