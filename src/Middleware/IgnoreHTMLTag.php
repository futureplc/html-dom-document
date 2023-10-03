<?php

namespace Future\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;

class IgnoreHTMLTag extends AbstractMiddleware
{
    protected array $replacements = [];

    public function __construct(protected HTMLDocument $dom, protected string $tag) {}

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
