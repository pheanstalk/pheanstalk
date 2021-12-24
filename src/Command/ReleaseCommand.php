<?php

declare(strict_types=1);

namespace Pheanstalk\Command;

use Pheanstalk\CommandType;
use Pheanstalk\Contract\CommandInterface;
use Pheanstalk\Contract\JobIdInterface;
use Pheanstalk\Contract\ResponseInterface;
use Pheanstalk\Contract\ResponseParserInterface;
use Pheanstalk\Exception;
use Pheanstalk\Response\ArrayResponse;
use Pheanstalk\Response\EmptySuccessResponse;
use Pheanstalk\ResponseType;

/**
 * The 'release' command.
 *
 * Releases a reserved job back onto the ready queue.
 */
class ReleaseCommand extends JobCommand
{
    public function __construct(JobIdInterface $job, private readonly int $priority, private readonly int $delay)
    {
        parent::__construct($job);
    }

    public function getCommandLine(): string
    {
        return "release {$this->jobId->getId()} {$this->priority} {$this->delay}";
    }

    public function getType(): CommandType
    {
        return CommandType::RELEASE;
    }

    public function getSuccessResponse(): ResponseType
    {
        return ResponseType::RELEASED;
    }
}
