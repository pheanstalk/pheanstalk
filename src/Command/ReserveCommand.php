<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception\DeadlineSoonException;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\DeadlineSoonExceptionParser;
use Pheanstalk\Parser\JobParser;
use Pheanstalk\Parser\TimedOutExceptionParser;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

/**
 * The 'reserve' command.
 *
 * Reserves/locks a ready job in a watched tube.
 */
class ReserveCommand extends AbstractCommand
{
    public function getResponseParser(): ResponseParserInterface
    {
        return new ChainedParser(
            new DeadlineSoonExceptionParser(),
            new TimedOutExceptionParser(),
            new JobParser(ResponseType::RESERVED)
        );
    }


    public function getType(): CommandType
    {
        return CommandType::RESERVE;
    }
}
