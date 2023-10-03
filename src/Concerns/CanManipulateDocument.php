<?php

namespace Future\HTMLDocument\Concerns;

use Future\HTMLDocument\HTMLNodeList;

trait CanManipulateDocument
{
    abstract public function querySelectorAll(string $cssSelector): HTMLNodeList;

    abstract public function query(string $xpathSelector): HTMLNodeList;

    public function withoutSelector(string $cssSelector): static
    {
        $nodes = $this->querySelectorAll($cssSelector);

        foreach ($nodes as $node) {
            if (! empty($node->parentNode)) {
                $node->parentNode->removeChild($node);
            }
        }

        return $this;
    }

    public function withoutComments(): static
    {
        $comments = $this->query('//comment()');

        foreach ($comments as $comment) {
            if (! empty($comment->parentNode)) {
                $comment->parentNode->removeChild($comment);
            }
        }

        return $this;
    }
}
