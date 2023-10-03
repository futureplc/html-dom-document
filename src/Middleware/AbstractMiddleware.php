<?php

namespace Future\HTMLDocument\Middleware;

use Future\HTMLDocument\HTMLDocument;

abstract class AbstractMiddleware
{
    public function __construct(protected HTMLDocument $dom) {}

    public function beforeLoadHTML(string $source): string
    {
        return $source;
    }

    public function afterLoadHTML(): void {}

    public function beforeSaveHTML(): void {}

    public function afterSaveHTML(string $source): string
    {
        return $source;
    }
}
