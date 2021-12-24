<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\JobNotFoundExceptionParser;
use Pheanstalk\Parser\JobParser;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\ResponseType;

/**
 * The 'reserve-job' command.
 */
final class ReserveJobCommand extends JobCommand
{
    public function getResponseParser(): ResponseParserInterface
    {
        return new ChainedParser(
            new JobNotFoundExceptionParser(),
            new JobParser($this->getSuccessResponse()),
        );
    }

    public function getType(): CommandType
    {
        return CommandType::RESERVE_JOB;
    }

    public function getSuccessResponse(): ResponseType
    {
        return ResponseType::RESERVED;
    }
}
