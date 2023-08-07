<?php

declare(strict_types=1);

namespace Pheanstalk\Parser;

/**
 * A parser that parses Yaml lists of strings
 * This is a very basic implementation that only supports single line strings and not any of the advanced YAML features
 * like multiline strings.
 * @internal
 */
final class YamlListParser
{
    /**
     * @param string $data
     * @return list<string>
     */
    public function parse(string $data): array
    {
        $lines = [];
        foreach (explode("\n", $data) as $line) {
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '- ')) {
                $lines[] = substr($trimmed, 2);
            }
        }

        return $lines;
    }
}
