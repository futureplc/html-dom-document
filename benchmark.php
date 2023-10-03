<?php

require __DIR__ . '/vendor/autoload.php';

use Future\HTMLDocument\HTMLDocument;

function benchmark(string $message, callable $callback): void
{
    $startTime = microtime(true);

    for ($i = 0; $i < 1_000_000; $i++) {
        $callback();
    }

    $endTime = microtime(true);
    $executionLength = ($endTime - $startTime);
    echo "Execution time for {$message}: {$executionLength}s" . PHP_EOL;
}

$html = '<html><body><div>test</div></body></html>';

benchmark('Plain DOMDocument', function () use ($html) {
    $dom = new DOMDocument();
    $dom->loadHTML($html);
});

benchmark('HTMLDocument with no middleware', function () use ($html) {
    HTMLDocument::fromHTML($html, false);
});

benchmark('HTMLDocument with all middleware', function () use ($html) {
    HTMLDocument::fromHTML($html);
});
