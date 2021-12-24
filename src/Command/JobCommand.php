<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Parser\ChainedParser;
use Pheanstalk\Parser\EmptySuccessParser;
use Pheanstalk\Parser\JobNotFoundExceptionParser;
use Pheanstalk\ResponseType;

/**
 * A command that is executed against a single job
 */
abstract class JobCommand extends AbstractCommand
{
    abstract public function getSuccessResponse(): ResponseType;

    public function getCommandLine(): string
    {
        return "{$this->getType()->value} {$this->jobId->getId()}";
    }

    public function getResponseParser(): ResponseParserInterface
    {
        return new ChainedParser(
            // Every job command can result in NOT_FOUND
            new JobNotFoundExceptionParser(),
            // Every job command has 1 result indicating success
            new EmptySuccessParser($this->getSuccessResponse())
        );
    }

    public function __construct(protected readonly JobIdInterface $jobId)
    {
    }
}
