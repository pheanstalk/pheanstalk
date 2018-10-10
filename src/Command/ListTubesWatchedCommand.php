<?php

namespace Pheanstalk\Command;

use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\YamlResponseParser;

/**
 * The 'list-tubes-watched' command.
 *
 * Lists the tubes on the watchlist.
 */
class ListTubesWatchedCommand extends AbstractCommand
{
    public function getCommandLine(): string
    {
        return 'list-tubes-watched';
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new YamlResponseParser(YamlResponseParser::MODE_LIST);
    }
}
