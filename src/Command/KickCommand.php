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
 * The 'kick' command.
 *
 * Kicks buried or delayed jobs into a 'ready' state.
 * If there are buried jobs, it will kick up to $max of them.
 * Otherwise, it will kick up to $max delayed jobs.
 */
class KickCommand extends AbstractCommand
{
    public function __construct(private int $max)
    {
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new EmptySuccessParser(ResponseType::KICKED);
    }


    public function getCommandLine(): string
    {
        return "kick {$this->max}";
    }

    public function getType(): CommandType
    {
        return CommandType::KICK;
    }
}
