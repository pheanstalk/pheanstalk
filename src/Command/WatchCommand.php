<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Parser\EmptySuccessParser;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

/**
 * The 'watch' command.
 * Adds a tube to the watchlist to reserve jobs from.
 */
class WatchCommand extends TubeCommand
{
    public function getType(): CommandType
    {
        return CommandType::WATCH;
    }


    public function getResponseParser(): ResponseParserInterface
    {
        return new EmptySuccessParser(ResponseType::WATCHING);
    }
}
