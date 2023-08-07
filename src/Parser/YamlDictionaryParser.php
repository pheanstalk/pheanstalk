<?php

declare(strict_types=1);

namespace Pheanstalk\Parser;

/**
 * A parser that parses Yaml a simple dictionary
 * This is a very basic implementation that only supports single line strings and not any of the advanced YAML features
 * like multiline strings.
 * @internal
 */
final class YamlDictionaryParser
{
    /**
     * @return array<string, bool|float|int|string>
     */
    public function parse(string $data): array
    {
        $dictionary = [];
        /** @var list<string> $matches */
        $matches = [];
        foreach (explode("\n", $data) as $line) {
            if (preg_match('#(\S+):\s*(.*)#', $line, $matches) === 1) {
                $dictionary[$matches[1]] = $this->cast($matches[2], $matches[1]);
            }
        }
        return $dictionary;
    }

    private function cast(string $value, string $key): bool|float|int|string
    {
        /**
         * Special case due to upstream issue: https://github.com/beanstalkd/beanstalkd/issues/610
         * Also using the workaround for the `os` key since while fixed for 16 months it's not yet been part of a release
         */
        if (in_array($key, ['hostname', 'os', 'tube', 'name', 'platform'], true)) {
            // We still check if it's quoted so that when the fixes are applied to beanstalkd our code still works.
            if (preg_match('/^".*"$/', $value) === 1) {
                return substr($value, 1, -1);
            } else {
                return $value;
            }
        }
        // Quoted strings
        if (preg_match('/^".*"$/', $value) === 1) {
            return substr($value, 1, -1);
        } elseif (preg_match('/^\d+$/', $value) === 1) {
            return (int)$value;
        } elseif (is_numeric($value)) {
            return (float) $value;
        } elseif (in_array($value, ["true", "false"], true)) {
            return $value === "true";
        }
        return trim($value);
    }
}
