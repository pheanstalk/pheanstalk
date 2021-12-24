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
 * The 'peek' command.
 *
 * The peek command let the client inspect a specific job in the system.
 */
final class PeekJobCommand extends JobCommand
{
    public function getResponseParser(): ResponseParserInterface
    {
        return new ChainedParser(
            new JobNotFoundExceptionParser(),
            new JobParser($this->getSuccessResponse())
        );
    }

    public function getType(): CommandType
    {
        return CommandType::PEEK;
    }

    public function getSuccessResponse(): ResponseType
    {
        return ResponseType::FOUND;
    }
}
