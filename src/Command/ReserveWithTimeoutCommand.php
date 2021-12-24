<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\EmptySuccessParser;
use Pheanstalk\Parser\JobParser;
use Pheanstalk\Parser\TimedOutExceptionParser;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\Response\EmptySuccessResponse;
use Pheanstalk\Response\RawDataResponse;
use Pheanstalk\ResponseType;

/**
 * The 'reserve' command.
 * Reserves/locks a ready job in a watched tube.
 */
class ReserveWithTimeoutCommand extends AbstractCommand
{
    /**
     * A timeout value of 0 will cause the server to immediately return either a
     * response or TIMED_OUT.  A positive value of timeout will limit the amount of
     * time the client will block on the reserve request until a job becomes
     * available.
     * @param positive-int $timeout
     */
    public function __construct(private readonly int $timeout)
    {
    }

    public function getCommandLine(): string
    {
        return "reserve-with-timeout {$this->timeout}";
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new ChainedParser(
            new EmptySuccessParser(ResponseType::TIMED_OUT),
            new JobParser(ResponseType::RESERVED)

        );
    }
    public function getType(): CommandType
    {
        return CommandType::RESERVE;
    }
}
