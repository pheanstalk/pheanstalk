<?php

declare(strict_types=1);

namespace Pheanstalk\Parser;

use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\ClientException;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

/**
 * A parser that parses Yaml lists of strings
 * This is a very basic implementation that only supports single line strings and not any of the advanced YAML features
 * like multiline strings.
 */
class YamlListParser implements ResponseParserInterface
{
    public function parseResponse(CommandInterface $command, ResponseType $type, array $arguments = [], null|string $data = null): null|ResponseInterface
    {
        if ($type !== ResponseType::OK || !isset($data)) {
            return null;
        }

        $lines = [];
        foreach (explode("\n", $data) as $line) {
            if (str_starts_with($line, '- ')) {
                $lines[] = substr($line, 2);
            }
        }

        return new ArrayResponse($type, $lines);
    }
}
