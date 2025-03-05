<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// Extract snippets from readme.
$readme = file(__DIR__ . '/../README.md', FILE_SKIP_EMPTY_LINES + FILE_IGNORE_NEW_LINES);
if ($readme === false) {
    throw new RuntimeException("Failed to open readme file");
}

passthru('rm -r ' . __DIR__ . '/snippets');
mkdir(__DIR__ . '/snippets');
/**
 * @param string[] $lines
 * @return string[]
 */
function readSnippet(&$lines): array
{
    $snippet = [];
    while ($lines[0] !== '```') {
        $snippet[] = array_shift($lines);
    }
    array_shift($lines);
    return $snippet;
}

/**
 * @param string[] $snippet
 * @param string $name
 * @return void
 */
function storeSnippet(array $snippet, string $name)
{
    // We replace occurrences of __DIR__

    $fileName = __DIR__ . '/snippets/' . strtr($name, [' ' => '-', '/' => '_or_']) . '.php';

    file_put_contents($fileName, strtr(implode("\n", ["<?php", ...$snippet]), [
        '__DIR__' => '__DIR__ . "/.."'
    ]));
}

$title = 'Root';
while ($readme !== []) {
    $line = array_shift($readme);
    if (preg_match('~^\#+\s+(.*)$~', $line, $matches) === 1) {
        $title = $matches[1];
    }

    if ($line === '```php') {
        $snippet = readSnippet($readme);
        storeSnippet($snippet, $title);
    }
}
