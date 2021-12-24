<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Parser\YamlListParser;
use Pheanstalk\YamlResponseParser;

/**
 * The 'list-tubes' command.
 *
 * List all existing tubes.
 */
class ListTubesCommand extends AbstractCommand
{
    public function getCommandLine(): string
    {
        return $this->getType()->value;
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new YamlListParser();
    }

    public function getType(): CommandType
    {
        return CommandType::LIST_TUBES;
    }
}
