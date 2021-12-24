<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Parser\YamlListParser;

/**
 * The 'list-tubes-watched' command.
 *
 * Lists the tubes on the watchlist.
 */
class ListTubesWatchedCommand extends AbstractCommand
{
    public function getResponseParser(): ResponseParserInterface
    {
        return new YamlListParser();
    }

    public function getType(): CommandType
    {
        return CommandType::LIST_TUBES_WATCHED;
    }
}
